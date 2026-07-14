<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Surat extends Auth_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Surat_model', 'Dokter_model', 'Pasien_model', 'Approval_log_model']);
        $this->load->library(['Pdf_generator', 'Approvalbridge']);
    }

    public function index()
    {
        $data = [
            'title'       => 'Surat',
            'active_menu' => 'surat',
            'surats'      => $this->Surat_model->get_all(),
        ];
        $this->_view('surat/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post' && $this->_validate()) {
            $id = $this->Surat_model->insert([
                'nomor_surat' => $this->Surat_model->generate_nomor(),
                'jenis_surat' => $this->input->post('jenis_surat', TRUE),
                'dokter_id'   => $this->input->post('dokter_id'),
                'pasien_id'   => $this->input->post('pasien_id'),
                'isi'         => $this->input->post('isi', TRUE),
                'status'      => 'draft',
            ]);
            $this->session->set_flashdata('success', 'Surat berhasil dibuat.');
            redirect('surat/detail/' . $id);
        }

        $data = [
            'title'       => 'Buat Surat',
            'active_menu' => 'surat',
            'item'        => NULL,
            'dokters'     => $this->Dokter_model->get_for_dropdown(),
            'pasiens'     => $this->Pasien_model->get_for_dropdown(),
        ];
        $this->_view('surat/form', $data);
    }

    public function edit($id)
    {
        $item = $this->Surat_model->get_by_id($id);
        if (!$item) show_404();
        if ($item->status !== 'draft') {
            $this->session->set_flashdata('error', 'Hanya surat berstatus draft yang bisa diedit.');
            redirect('surat/detail/' . $id);
        }

        if ($this->input->method() === 'post' && $this->_validate()) {
            $this->Surat_model->update($id, [
                'jenis_surat' => $this->input->post('jenis_surat', TRUE),
                'dokter_id'   => $this->input->post('dokter_id'),
                'pasien_id'   => $this->input->post('pasien_id'),
                'isi'         => $this->input->post('isi', TRUE),
                'file_pdf'    => NULL, // reset PDF saat isi diubah
            ]);
            $this->session->set_flashdata('success', 'Surat berhasil diupdate.');
            redirect('surat/detail/' . $id);
        }

        $data = [
            'title'       => 'Edit Surat',
            'active_menu' => 'surat',
            'item'        => $item,
            'dokters'     => $this->Dokter_model->get_for_dropdown(),
            'pasiens'     => $this->Pasien_model->get_for_dropdown(),
        ];
        $this->_view('surat/form', $data);
    }

    public function detail($id)
    {
        $item = $this->Surat_model->get_by_id($id);
        if (!$item) show_404();

        $this->load->model('Settings_model');
        $pub_base = rtrim($this->Settings_model->get('public_base_url'), '/');

        $data = [
            'title'           => 'Detail Surat',
            'active_menu'     => 'surat',
            'item'            => $item,
            'pdf_public_url'  => $pub_base && $item->file_pdf ? $pub_base . '/' . $item->file_pdf : NULL,
        ];
        $this->_view('surat/detail', $data);
    }

    public function generate_pdf($id)
    {
        $item = $this->Surat_model->get_by_id($id);
        if (!$item) show_404();

        $file_pdf = $this->pdf_generator->generate_surat($id);
        if ($file_pdf) {
            $this->Surat_model->update($id, ['file_pdf' => $file_pdf]);
            $this->session->set_flashdata('success', 'PDF berhasil digenerate.');
        } else {
            $this->session->set_flashdata('error', 'Gagal generate PDF.');
        }
        redirect('surat/detail/' . $id);
    }

    public function download_pdf($id)
    {
        $item = $this->Surat_model->get_by_id($id);
        if (!$item || !$item->file_pdf) show_404();
        $this->pdf_generator->download($item->file_pdf);
    }

    public function delete($id)
    {
        $item = $this->Surat_model->get_by_id($id);
        if (!$item) show_404();
        if ($item->status !== 'draft') {
            $this->session->set_flashdata('error', 'Hanya surat draft yang bisa dihapus.');
            redirect('surat');
        }
        if ($item->file_pdf && file_exists(FCPATH . $item->file_pdf)) {
            unlink(FCPATH . $item->file_pdf);
        }
        $this->Surat_model->delete($id);
        $this->session->set_flashdata('success', 'Surat berhasil dihapus.');
        redirect('surat');
    }

    public function set_public_url($id)
    {
        $item = $this->Surat_model->get_by_id($id);
        if (!$item) show_404();
        $url = trim($this->input->post('file_pdf_public_url', TRUE));
        $this->Surat_model->update($id, ['file_pdf_public_url' => $url ?: NULL]);
        $this->session->set_flashdata('success', 'Public URL berhasil disimpan.');
        redirect('surat/detail/' . $id);
    }

    public function kirim($id)
    {
        $item = $this->Surat_model->get_by_id($id);
        if (!$item) show_404();

        if (!in_array($item->status, ['draft', 'gagal_kirim'])) {
            $this->session->set_flashdata('error', 'Status surat saat ini tidak bisa dikirim: ' . $item->status);
            redirect('surat/detail/' . $id);
        }

        $dokter = $this->db->get_where('dokter', ['id' => $item->dokter_id])->row();
        if (!$dokter || !$dokter->approvalsmart_user_id) {
            $this->session->set_flashdata('error', 'ApprovalSmart User ID dokter belum diisi. Edit data dokter terlebih dahulu.');
            redirect('surat/detail/' . $id);
        }

        if (!$item->file_pdf) {
            $this->session->set_flashdata('error', 'Generate PDF terlebih dahulu sebelum mengirim.');
            redirect('surat/detail/' . $id);
        }

        $expires     = max(1, min(720, (int)$this->input->post('expires_in_hours') ?: 48));
        $approval_id = $this->_generate_uuid();
        $source_ref  = 'surat-' . $approval_id;

        // Bangun public URL PDF dari setting public_base_url
        $this->load->model('Settings_model');
        $public_base = rtrim($this->Settings_model->get('public_base_url'), '/');
        $pdf_public_url = $public_base && $item->file_pdf
            ? $public_base . '/' . $item->file_pdf
            : NULL;

        if ($pdf_public_url) {
            $this->Surat_model->update($id, ['file_pdf_public_url' => $pdf_public_url]);
        }

        $payload = [
            'approval_id'      => $approval_id,
            'source_ref'       => $source_ref,
            'request_type'     => 'surat',
            'title'            => $item->nomor_surat . ' — ' . $item->jenis_surat,
            'approver_user_id' => $dokter->approvalsmart_user_id,
            'expires_in_hours' => $expires,
            'summary'          => "Dokter: {$item->nama_dokter}\nPasien: {$item->nama_pasien} [{$item->no_rm}]",
            'detail_url'       => site_url('surat/detail/' . $id),
        ];

        if ($pdf_public_url) {
            $payload['attachment'] = [
                'filename' => basename($item->file_pdf),
                'url'      => $pdf_public_url,
            ];
        }

        $result = $this->approvalbridge->send($payload);

        // Log — jangan simpan HMAC secret (tidak ada di payload, hanya di header yang tidak kita log)
        $this->Approval_log_model->log_outbound(
            'surat', $source_ref, $approval_id,
            $result['endpoint'], $payload, $result
        );

        if ($result['http_code'] === 202 || $result['http_code'] === 409) {
            $this->Surat_model->update($id, [
                'status'           => 'menunggu_approval',
                'approval_id'      => $approval_id,
                'source_ref'       => $source_ref,
                'approver_user_id' => $dokter->approvalsmart_user_id,
                'expires_in_hours' => $expires,
            ]);
            $msg = $result['http_code'] === 409 ? 'Surat sudah terkirim sebelumnya (idempoten).' : 'Surat berhasil dikirim ke ApprovalSmart.';
            $this->session->set_flashdata('success', $msg);
        } else {
            $this->Surat_model->update($id, ['status' => 'gagal_kirim']);
            $err = $result['body']['message'] ?? $result['raw'];
            $this->session->set_flashdata('error', 'Gagal kirim (HTTP ' . $result['http_code'] . '): ' . $err);
        }

        redirect('surat/detail/' . $id);
    }

    private function _validate()
    {
        $this->form_validation->set_rules('jenis_surat', 'Jenis Surat', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('dokter_id', 'Dokter', 'required|integer');
        $this->form_validation->set_rules('pasien_id', 'Pasien', 'required|integer');
        $this->form_validation->set_rules('isi', 'Isi Surat', 'required|trim');
        return $this->form_validation->run();
    }
}
