<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Endpoint JSON internal — bukan bagian spek ApprovalSmart.
 * Tidak butuh session login; akses bebas dari dalam network dev.
 */
class Api extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Approval_log_model');
    }

    /**
     * GET /api/approval/log
     * Query params: module_type, direction, status, date_from, date_to
     */
    public function approval_log()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->_json(['status' => 'error', 'message' => 'Method not allowed'], 405);
            return;
        }

        $filters = array_filter([
            'module_type' => $this->input->get('module_type'),
            'direction'   => $this->input->get('direction'),
            'status'      => $this->input->get('status'),
            'date_from'   => $this->input->get('date_from'),
            'date_to'     => $this->input->get('date_to'),
        ]);

        $logs = $this->Approval_log_model->get_all($filters);

        $this->_json([
            'status' => 'success',
            'data'   => $logs,
            'total'  => count($logs),
        ]);
    }

    private function _json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
