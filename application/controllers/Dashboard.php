<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Auth_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Approval_log_model');
    }

    public function index()
    {
        $data = [
            'title'        => 'Dashboard',
            'active_menu'  => 'dashboard',
            'stats'        => $this->_get_stats(),
            'recent_logs'  => $this->Approval_log_model->get_recent(10),
        ];
        $this->_view('dashboard/index', $data);
    }

    private function _get_stats()
    {
        return [
            'surat_draft'              => $this->db->where('status', 'draft')->count_all_results('surat'),
            'surat_menunggu'           => $this->db->where('status', 'menunggu_approval')->count_all_results('surat'),
            'surat_terverifikasi'      => $this->db->where('status', 'terverifikasi')->count_all_results('surat'),
            'resep_draft'              => $this->db->where('status', 'draft')->count_all_results('resep'),
            'resep_menunggu'           => $this->db->where('status', 'menunggu_approval')->count_all_results('resep'),
            'resep_terverifikasi'      => $this->db->where('status', 'terverifikasi')->count_all_results('resep'),
        ];
    }
}
