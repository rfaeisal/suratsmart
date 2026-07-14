<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approval_log extends Auth_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Approval_log_model');
    }

    public function index()
    {
        $filters = [
            'module_type' => $this->input->get('module_type'),
            'direction'   => $this->input->get('direction'),
            'status'      => $this->input->get('status'),
            'date_from'   => $this->input->get('date_from'),
            'date_to'     => $this->input->get('date_to'),
        ];

        $data = [
            'title'       => 'Approval Log',
            'active_menu' => 'approval_log',
            'logs'        => $this->Approval_log_model->get_all(array_filter($filters)),
            'filters'     => $filters,
        ];
        $this->_view('approval_log/index', $data);
    }

    public function detail($id)
    {
        $log = $this->Approval_log_model->get_by_id($id);
        if (!$log) show_404();

        $data = [
            'title'       => 'Detail Log #' . $id,
            'active_menu' => 'approval_log',
            'log'         => $log,
        ];
        $this->_view('approval_log/detail', $data);
    }
}
