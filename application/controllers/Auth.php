<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
    }

    public function login()
    {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        if ($this->input->method() !== 'post') {
            $this->load->view('auth/login');
            return;
        }

        $this->form_validation->set_rules('username', 'Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/login');
            return;
        }

        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password');
        $user     = $this->User_model->get_by_username($username);

        if ($user && $user->is_active && password_verify($password, $user->password)) {
            $this->session->set_userdata([
                'logged_in' => TRUE,
                'user_id'   => $user->id,
                'username'  => $user->username,
                'nama'      => $user->nama,
                'role'      => $user->role,
            ]);
            redirect('dashboard');
        }

        $this->session->set_flashdata('error', 'Username atau password salah, atau akun tidak aktif.');
        redirect('auth/login');
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}
