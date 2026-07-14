<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Endpoint inbound callback dari ApprovalSmart.
 * Tidak extend Auth_Controller — ini API endpoint, bukan halaman web.
 * Auth via Bearer token dari header Authorization.
 */
class Approvals extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Surat_model', 'Resep_model', 'Approval_log_model']);
        $this->load->library(['Approvalbridge', 'Pdf_generator']);
        $this->load->config('approvalsmart');
    }

    /**
     * PATCH approvals/{source_ref}
     * Dipanggil ApprovalSmart setelah approver menekan tombol di Telegram.
     */
    public function callback($source_ref = NULL)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            $this->_json(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }

        // 1. Verifikasi Bearer token
        $auth_header = $this->input->get_request_header('Authorization', TRUE);
        $legacy_token = $this->approvalbridge->get_legacy_token();
        if (empty($legacy_token) || $auth_header !== 'Bearer ' . $legacy_token) {
            $this->_json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            return;
        }

        // 2. Parse body
        $raw_body = file_get_contents('php://input');
        $body     = json_decode($raw_body, TRUE);
        $status   = isset($body['status']) ? trim($body['status']) : '';

        if (!in_array($status, ['approved', 'rejected', 'expired'])) {
            $this->_json(['status' => 'error', 'message' => 'Invalid or missing status'], 400);
            return;
        }

        // 3. Resolve modul dari source_ref
        [$module_type, $doc] = $this->_resolve_document($source_ref);

        // 4. Dokumen tidak ditemukan → 200 supaya ApprovalSmart tidak retry
        if (!$doc) {
            $this->_json(['status' => 'ok', 'message' => 'source_ref not found, ignored'], 200);
            return;
        }

        // 5. Idempotency check
        $idempotency_key  = $this->input->get_request_header('Idempotency-Key', TRUE);
        $terminal_statuses = ['terverifikasi', 'ditolak', 'kedaluwarsa'];
        $already_done      = in_array($doc->status, $terminal_statuses);

        if ($already_done && $idempotency_key && $doc->approval_id === $idempotency_key) {
            $this->_json(['status' => 'error', 'message' => 'Already applied', 'approval_id' => $idempotency_key], 409);
            return;
        }

        // 6. Update status dokumen
        $decided_by = isset($body['decided_by']) ? $body['decided_by'] : NULL;
        $decided_at = isset($body['decided_at']) ? date('Y-m-d H:i:s', strtotime($body['decided_at'])) : date('Y-m-d H:i:s');
        $note       = isset($body['note']) ? $body['note'] : NULL;

        if ($module_type === 'surat') {
            $this->Surat_model->update_approval_result($doc->id, $status, $decided_by, $decided_at, $note);
        } else {
            $this->Resep_model->update_approval_result($doc->id, $status, $decided_by, $decided_at, $note);
        }

        // 7. Jika approved → generate PDF final (idempoten: generate ulang tidak merusak)
        if ($status === 'approved') {
            try {
                if ($module_type === 'surat') {
                    $this->pdf_generator->generate_surat($doc->id);
                } else {
                    $this->pdf_generator->generate_resep($doc->id);
                }
            } catch (Exception $e) {
                log_message('error', 'PDF generate after approval failed: ' . $e->getMessage());
            }
        }

        // 8. Log inbound
        $http_status = 200;
        $log_status  = 'sukses';
        $this->Approval_log_model->log_inbound(
            $module_type,
            $source_ref,
            $idempotency_key,
            $raw_body,
            $http_status,
            $log_status
        );

        $this->_json(['status' => 'ok', 'message' => 'Callback processed'], 200);
    }

    private function _resolve_document($source_ref)
    {
        if (strpos($source_ref, 'surat-') === 0) {
            return ['surat', $this->Surat_model->get_by_source_ref($source_ref)];
        }
        if (strpos($source_ref, 'resep-') === 0) {
            return ['resep', $this->Resep_model->get_by_source_ref($source_ref)];
        }
        return [NULL, NULL];
    }

    private function _json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
