<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pasien extends Auth_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pasien_model');
    }

    public function index()
    {
        $data = [
            'title'       => 'Data Pasien',
            'active_menu' => 'pasien',
            'pasiens'     => $this->Pasien_model->get_all(),
        ];
        $this->_view('pasien/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post' && $this->_validate()) {
            $no_rm = $this->input->post('no_rm', TRUE);
            if ($this->Pasien_model->is_no_rm_taken($no_rm)) {
                $this->session->set_flashdata('error', 'No. Rekam Medis sudah terdaftar.');
                redirect('pasien/create');
            }
            $this->Pasien_model->insert($this->_post_data());
            $this->session->set_flashdata('success', 'Pasien berhasil ditambahkan.');
            redirect('pasien');
        }

        $data = ['title' => 'Tambah Pasien', 'active_menu' => 'pasien', 'item' => NULL];
        $this->_view('pasien/form', $data);
    }

    public function edit($id)
    {
        $item = $this->Pasien_model->get_by_id($id);
        if (!$item) show_404();

        if ($this->input->method() === 'post' && $this->_validate()) {
            $no_rm = $this->input->post('no_rm', TRUE);
            if ($this->Pasien_model->is_no_rm_taken($no_rm, $id)) {
                $this->session->set_flashdata('error', 'No. Rekam Medis sudah terdaftar.');
                redirect("pasien/edit/{$id}");
            }
            $this->Pasien_model->update($id, $this->_post_data());
            $this->session->set_flashdata('success', 'Pasien berhasil diupdate.');
            redirect('pasien');
        }

        $data = ['title' => 'Edit Pasien', 'active_menu' => 'pasien', 'item' => $item];
        $this->_view('pasien/form', $data);
    }

    public function delete($id)
    {
        $this->Pasien_model->delete($id);
        $this->session->set_flashdata('success', 'Pasien berhasil dihapus.');
        redirect('pasien');
    }

    private function _validate()
    {
        $this->form_validation->set_rules('no_rm', 'No. Rekam Medis', 'required|trim|max_length[30]');
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('jenis_kelamin', 'Jenis Kelamin', 'in_list[L,P,]');
        return $this->form_validation->run();
    }

    private function _post_data()
    {
        return [
            'no_rm'          => $this->input->post('no_rm', TRUE),
            'nama'           => $this->input->post('nama', TRUE),
            'tanggal_lahir'  => $this->input->post('tanggal_lahir') ?: NULL,
            'jenis_kelamin'  => $this->input->post('jenis_kelamin') ?: NULL,
            'alamat'         => $this->input->post('alamat', TRUE),
            'no_telp'        => $this->input->post('no_telp', TRUE),
        ];
    }
}
