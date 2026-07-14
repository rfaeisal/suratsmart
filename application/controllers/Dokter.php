<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dokter extends Auth_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Dokter_model', 'User_model']);
    }

    public function index()
    {
        $data = [
            'title'       => 'Data Dokter',
            'active_menu' => 'dokter',
            'dokters'     => $this->Dokter_model->get_all(),
        ];
        $this->_view('dokter/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post' && $this->_validate()) {
            $this->Dokter_model->insert($this->_post_data());
            $this->session->set_flashdata('success', 'Dokter berhasil ditambahkan.');
            redirect('dokter');
        }

        $data = [
            'title'       => 'Tambah Dokter',
            'active_menu' => 'dokter',
            'item'        => NULL,
            'users'       => $this->User_model->get_for_dropdown(),
        ];
        $this->_view('dokter/form', $data);
    }

    public function edit($id)
    {
        $item = $this->Dokter_model->get_by_id($id);
        if (!$item) show_404();

        if ($this->input->method() === 'post' && $this->_validate()) {
            $this->Dokter_model->update($id, $this->_post_data());
            $this->session->set_flashdata('success', 'Dokter berhasil diupdate.');
            redirect('dokter');
        }

        $data = [
            'title'       => 'Edit Dokter',
            'active_menu' => 'dokter',
            'item'        => $item,
            'users'       => $this->User_model->get_for_dropdown(),
        ];
        $this->_view('dokter/form', $data);
    }

    public function delete($id)
    {
        $this->Dokter_model->delete($id);
        $this->session->set_flashdata('success', 'Dokter berhasil dihapus.');
        redirect('dokter');
    }

    private function _validate()
    {
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('no_sip', 'No. SIP', 'trim|max_length[50]');
        $this->form_validation->set_rules('spesialisasi', 'Spesialisasi', 'trim|max_length[100]');
        $this->form_validation->set_rules('no_telp', 'No. Telp', 'trim|max_length[30]');
        return $this->form_validation->run();
    }

    private function _post_data()
    {
        return [
            'nama'                   => $this->input->post('nama', TRUE),
            'no_sip'                 => $this->input->post('no_sip', TRUE),
            'spesialisasi'           => $this->input->post('spesialisasi', TRUE),
            'no_telp'                => $this->input->post('no_telp', TRUE),
            'user_id'                => $this->input->post('user_id') ?: NULL,
            'approvalsmart_user_id'  => trim($this->input->post('approvalsmart_user_id', TRUE)) ?: NULL,
        ];
    }
}
