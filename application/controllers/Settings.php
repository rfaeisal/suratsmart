<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends Auth_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Settings_model');
        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('dashboard');
        }
    }

    public function index()
    {
        if ($this->input->method() === 'post') {
            $this->Settings_model->save_all([
                'approvalsmart_base_url'     => $this->input->post('approvalsmart_base_url', TRUE),
                'approvalsmart_hmac_key_id'  => $this->input->post('approvalsmart_hmac_key_id', TRUE),
                'approvalsmart_hmac_secret'  => $this->input->post('approvalsmart_hmac_secret', TRUE),
                'approvalsmart_legacy_token' => $this->input->post('approvalsmart_legacy_token', TRUE),
                'public_base_url'            => rtrim($this->input->post('public_base_url', TRUE), '/'),
            ]);
            $this->session->set_flashdata('success', 'Settings berhasil disimpan.');
            redirect('settings');
        }

        $data = [
            'title'       => 'Settings',
            'active_menu' => 'settings',
            'settings'    => $this->Settings_model->get_all(),
        ];
        $this->_view('settings/index', $data);
    }
}
