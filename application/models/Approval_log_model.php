<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approval_log_model extends CI_Model {

    protected $table = 'approval_logs';

    public function log_outbound($module_type, $source_ref, $approval_id, $endpoint, $payload, $result)
    {
        return $this->db->insert($this->table, [
            'source_ref'       => $source_ref,
            'approval_id'      => $approval_id,
            'module_type'      => $module_type,
            'direction'        => 'outbound',
            'endpoint'         => $endpoint,
            'http_method'      => 'POST',
            'request_payload'  => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'response_payload' => $result['raw'],
            'http_status'      => $result['http_code'],
            'status'           => $result['ok'] ? 'sukses' : 'gagal',
        ]);
    }

    public function log_inbound($module_type, $source_ref, $approval_id, $body_raw, $http_status, $status)
    {
        return $this->db->insert($this->table, [
            'source_ref'      => $source_ref,
            'approval_id'     => $approval_id,
            'module_type'     => $module_type,
            'direction'       => 'inbound',
            'endpoint'        => uri_string(),
            'http_method'     => 'PATCH',
            'request_payload' => $body_raw,
            'http_status'     => $http_status,
            'status'          => $status,
        ]);
    }

    public function get_all(array $filters = [])
    {
        if (!empty($filters['module_type'])) {
            $this->db->where('module_type', $filters['module_type']);
        }
        if (!empty($filters['direction'])) {
            $this->db->where('direction', $filters['direction']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(created_at) <=', $filters['date_to']);
        }
        return $this->db->order_by('id', 'DESC')->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function get_recent($limit = 10)
    {
        return $this->db->order_by('id', 'DESC')->limit($limit)->get($this->table)->result();
    }
}
