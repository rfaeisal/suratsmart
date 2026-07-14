<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resep extends Auth_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Resep_model', 'Resep_detail_model', 'Dokter_model', 'Pasien_model', 'Obat_model', 'Approval_log_model']);
        $this->load->library(['Pdf_generator', 'Approvalbridge']);
    }

    public function index()
    {
        $data = [
            'title'       => 'Resep',
            'active_menu' => 'resep',
            'reseps'      => $this->Resep_model->get_all(),
        ];
        $this->_view('resep/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post' && $this->_validate()) {
            $id = $this->Resep_model->insert([
                'nomor_resep' => $this->Resep_model->generate_nomor(),
                'dokter_id'   => $this->input->post('dokter_id'),
                'pasien_id'   => $this->input->post('pasien_id'),
                'tanggal'     => $this->input->post('tanggal'),
                'catatan'     => $this->input->post('catatan', TRUE),
                'status'      => 'draft',
            ]);
            $this->Resep_detail_model->save_items(
                $id,
                $this->input->post('obat_id'),
                $this->input->post('jumlah'),
                $this->input->post('aturan_pakai')
            );
            $this->session->set_flashdata('success', 'Resep berhasil dibuat.');
            redirect('resep/detail/' . $id);
        }

        $data = [
            'title'       => 'Buat Resep',
            'active_menu' => 'resep',
            'item'        => NULL,
            'detail'      => [],
            'dokters'     => $this->Dokter_model->get_for_dropdown(),
            'pasiens'     => $this->Pasien_model->get_for_dropdown(),
            'obats'       => $this->Obat_model->get_for_dropdown(),
        ];
        $this->_view('resep/form', $data);
    }

    public function edit($id)
    {
        $item = $this->Resep_model->get_by_id($id);
        if (!$item) show_404();
        if ($item->status !== 'draft') {
            $this->session->set_flashdata('error', 'Hanya resep berstatus draft yang bisa diedit.');
            redirect('resep/detail/' . $id);
        }

        if ($this->input->method() === 'post' && $this->_validate()) {
            $this->Resep_model->update($id, [
                'dokter_id' => $this->input->post('dokter_id'),
                'pasien_id' => $this->input->post('pasien_id'),
                'tanggal'   => $this->input->post('tanggal'),
                'catatan'   => $this->input->post('catatan', TRUE),
                'file_pdf'  => NULL,
            ]);
            $this->Resep_detail_model->save_items(
                $id,
                $this->input->post('obat_id'),
                $this->input->post('jumlah'),
                $this->input->post('aturan_pakai')
            );
            $this->session->set_flashdata('success', 'Resep berhasil diupdate.');
            redirect('resep/detail/' . $id);
        }

        $data = [
            'title'       => 'Edit Resep',
            'active_menu' => 'resep',
            'item'        => $item,
            'detail'      => $this->Resep_detail_model->get_by_resep($id),
            'dokters'     => $this->Dokter_model->get_for_dropdown(),
            'pasiens'     => $this->Pasien_model->get_for_dropdown(),
            'obats'       => $this->Obat_model->get_for_dropdown(),
        ];
        $this->_view('resep/form', $data);
    }

    public function detail($id)
    {
        $item = $this->Resep_model->get_by_id($id);
        if (!$item) show_404();

        $this->load->model('Settings_model');
        $pub_base = rtrim($this->Settings_model->get('public_base_url'), '/');

        $data = [
            'title'          => 'Detail Resep',
            'active_menu'    => 'resep',
            'item'           => $item,
            'detail'         => $this->Resep_detail_model->get_by_resep($id),
            'pdf_public_url' => $pub_base && $item->file_pdf ? $pub_base . '/' . $item->file_pdf : NULL,
        ];
        $this->_view('resep/detail', $data);
    }

    public function generate_pdf($id)
    {
        $item = $this->Resep_model->get_by_id($id);
        if (!$item) show_404();

        $file_pdf = $this->pdf_generator->generate_resep($id);
        if ($file_pdf) {
            $this->Resep_model->update($id, ['file_pdf' => $file_pdf]);
            $this->session->set_flashdata('success', 'PDF berhasil digenerate.');
        } else {
            $this->session->set_flashdata('error', 'Gagal generate PDF.');
        }
        redirect('resep/detail/' . $id);
    }

    public function download_pdf($id)
    {
        $item = $this->Resep_model->get_by_id($id);
        if (!$item || !$item->file_pdf) show_404();
        $this->pdf_generator->download($item->file_pdf);
    }

    public function delete($id)
    {
        $item = $this->Resep_model->get_by_id($id);
        if (!$item) show_404();
        if ($item->status !== 'draft') {
            $this->session->set_flashdata('error', 'Hanya resep draft yang bisa dihapus.');
            redirect('resep');
        }
        if ($item->file_pdf && file_exists(FCPATH . $item->file_pdf)) {
            unlink(FCPATH . $item->file_pdf);
        }
        $this->Resep_model->delete($id);
        $this->session->set_flashdata('success', 'Resep berhasil dihapus.');
        redirect('resep');
    }

    public function set_public_url($id)
    {
        $item = $this->Resep_model->get_by_id($id);
        if (!$item) show_404();
        $url = trim($this->input->post('file_pdf_public_url', TRUE));
        $this->Resep_model->update($id, ['file_pdf_public_url' => $url ?: NULL]);
        $this->session->set_flashdata('success', 'Public URL berhasil disimpan.');
        redirect('resep/detail/' . $id);
    }

    public function kirim($id)
    {
        $item = $this->Resep_model->get_by_id($id);
        if (!$item) show_404();

        if (!in_array($item->status, ['draft', 'gagal_kirim'])) {
            $this->session->set_flashdata('error', 'Status resep saat ini tidak bisa dikirim: ' . $item->status);
            redirect('resep/detail/' . $id);
        }

        $dokter = $this->db->get_where('dokter', ['id' => $item->dokter_id])->row();
        if (!$dokter || !$dokter->approvalsmart_user_id) {
            $this->session->set_flashdata('error', 'ApprovalSmart User ID dokter belum diisi. Edit data dokter terlebih dahulu.');
            redirect('resep/detail/' . $id);
        }

        if (!$item->file_pdf) {
            $this->session->set_flashdata('error', 'Generate PDF terlebih dahulu sebelum mengirim.');
            redirect('resep/detail/' . $id);
        }

        $expires     = max(1, min(720, (int)$this->input->post('expires_in_hours') ?: 48));
        $approval_id = $this->_generate_uuid();

        $this->load->model('Settings_model');
        $public_base = rtrim($this->Settings_model->get('public_base_url'), '/');
        $pdf_public_url = $public_base && $item->file_pdf
            ? $public_base . '/' . $item->file_pdf
            : NULL;

        if ($pdf_public_url) {
            $this->Resep_model->update($id, ['file_pdf_public_url' => $pdf_public_url]);
        }

        $payload = [
            'approval_id'      => $approval_id,
            'source_ref'       => $item->source_ref,
            'request_type'     => 'resep',
            'title'            => $item->nomor_resep . ' — Resep ' . $item->nama_pasien,
            'approver_user_id' => $dokter->approvalsmart_user_id,
            'expires_in_hours' => $expires,
            'summary'          => "Dokter: {$item->nama_dokter}\nPasien: {$item->nama_pasien} [{$item->no_rm}]\nTanggal: " . tgl_indonesia($item->tanggal),
            'detail_url'       => site_url('resep/detail/' . $id),
        ];

        if ($pdf_public_url) {
            $payload['attachment'] = [
                'filename' => basename($item->file_pdf),
                'url'      => $pdf_public_url,
            ];
        }

        $result = $this->approvalbridge->send($payload);

        $this->Approval_log_model->log_outbound(
            'resep', $item->source_ref, $approval_id,
            $result['endpoint'], $payload, $result
        );

        if ($result['http_code'] === 202 || $result['http_code'] === 409) {
            $this->Resep_model->update($id, [
                'status'           => 'menunggu_approval',
                'approval_id'      => $approval_id,
                'approver_user_id' => $dokter->approvalsmart_user_id,
                'expires_in_hours' => $expires,
            ]);
            $msg = $result['http_code'] === 409 ? 'Resep sudah terkirim sebelumnya (idempoten).' : 'Resep berhasil dikirim ke ApprovalSmart.';
            $this->session->set_flashdata('success', $msg);
        } else {
            $this->Resep_model->update($id, ['status' => 'gagal_kirim']);
            $err = $result['body']['message'] ?? $result['raw'];
            $this->session->set_flashdata('error', 'Gagal kirim (HTTP ' . $result['http_code'] . '): ' . $err);
        }

        redirect('resep/detail/' . $id);
    }

    private function _validate()
    {
        $this->form_validation->set_rules('dokter_id', 'Dokter', 'required|integer');
        $this->form_validation->set_rules('pasien_id', 'Pasien', 'required|integer');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        return $this->form_validation->run();
    }
}
