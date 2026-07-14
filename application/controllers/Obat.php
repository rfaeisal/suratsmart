<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Obat extends Auth_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Obat_model');
    }

    public function index()
    {
        $data = [
            'title'       => 'Data Obat',
            'active_menu' => 'obat',
            'obats'       => $this->Obat_model->get_all(),
        ];
        $this->_view('obat/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post' && $this->_validate()) {
            $this->Obat_model->insert($this->_post_data());
            $this->session->set_flashdata('success', 'Obat berhasil ditambahkan.');
            redirect('obat');
        }

        $data = ['title' => 'Tambah Obat', 'active_menu' => 'obat', 'item' => NULL];
        $this->_view('obat/form', $data);
    }

    public function edit($id)
    {
        $item = $this->Obat_model->get_by_id($id);
        if (!$item) show_404();

        if ($this->input->method() === 'post' && $this->_validate()) {
            $this->Obat_model->update($id, $this->_post_data());
            $this->session->set_flashdata('success', 'Obat berhasil diupdate.');
            redirect('obat');
        }

        $data = ['title' => 'Edit Obat', 'active_menu' => 'obat', 'item' => $item];
        $this->_view('obat/form', $data);
    }

    public function delete($id)
    {
        $this->Obat_model->delete($id);
        $this->session->set_flashdata('success', 'Obat berhasil dihapus.');
        redirect('obat');
    }

    private function _validate()
    {
        $this->form_validation->set_rules('nama_obat', 'Nama Obat', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('satuan', 'Satuan', 'trim|max_length[30]');
        $this->form_validation->set_rules('stok', 'Stok', 'required|integer|greater_than_equal_to[0]');
        $this->form_validation->set_rules('harga', 'Harga', 'required|numeric|greater_than_equal_to[0]');
        return $this->form_validation->run();
    }

    private function _post_data()
    {
        return [
            'nama_obat' => $this->input->post('nama_obat', TRUE),
            'satuan'    => $this->input->post('satuan', TRUE),
            'stok'      => (int) $this->input->post('stok'),
            'harga'     => (float) $this->input->post('harga'),
        ];
    }
}
