<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends Auth_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('dashboard');
        }
    }

    public function index()
    {
        $data = [
            'title'       => 'Manajemen User',
            'active_menu' => 'user',
            'users'       => $this->User_model->get_all(),
        ];
        $this->_view('user/index', $data);
    }

    public function create()
    {
        $this->_set_rules();

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');

            if ($this->form_validation->run()) {
                $username = $this->input->post('username', TRUE);
                if ($this->User_model->is_username_taken($username)) {
                    $this->session->set_flashdata('error', 'Username sudah digunakan.');
                    redirect('user/create');
                }
                $this->User_model->insert([
                    'username'  => $username,
                    'password'  => $this->input->post('password'),
                    'nama'      => $this->input->post('nama', TRUE),
                    'role'      => $this->input->post('role'),
                    'is_active' => $this->input->post('is_active') ? 1 : 0,
                ]);
                $this->session->set_flashdata('success', 'User berhasil ditambahkan.');
                redirect('user');
            }
        }

        $data = ['title' => 'Tambah User', 'active_menu' => 'user', 'item' => NULL];
        $this->_view('user/form', $data);
    }

    public function edit($id)
    {
        $item = $this->User_model->get_by_id($id);
        if (!$item) show_404();

        $this->_set_rules();

        if ($this->input->method() === 'post') {
            if ($this->form_validation->run()) {
                $username = $this->input->post('username', TRUE);
                if ($this->User_model->is_username_taken($username, $id)) {
                    $this->session->set_flashdata('error', 'Username sudah digunakan.');
                    redirect("user/edit/{$id}");
                }
                $this->User_model->update($id, [
                    'username'  => $username,
                    'password'  => $this->input->post('password'),
                    'nama'      => $this->input->post('nama', TRUE),
                    'role'      => $this->input->post('role'),
                    'is_active' => $this->input->post('is_active') ? 1 : 0,
                ]);
                $this->session->set_flashdata('success', 'User berhasil diupdate.');
                redirect('user');
            }
        }

        $data = ['title' => 'Edit User', 'active_menu' => 'user', 'item' => $item];
        $this->_view('user/form', $data);
    }

    public function delete($id)
    {
        if ((int)$id === (int)$this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Tidak bisa menghapus akun sendiri.');
            redirect('user');
        }
        $this->User_model->delete($id);
        $this->session->set_flashdata('success', 'User berhasil dihapus.');
        redirect('user');
    }

    private function _set_rules()
    {
        $this->form_validation->set_rules('username', 'Username', 'required|trim|min_length[3]|max_length[50]');
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[admin,dokter,staff]');
    }
}
