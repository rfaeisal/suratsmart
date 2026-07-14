<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dokter_model extends CI_Model {

    protected $table = 'dokter';

    public function get_all()
    {
        return $this->db->select('dokter.*, users.username')
            ->join('users', 'users.id = dokter.user_id', 'left')
            ->order_by('dokter.nama')
            ->get($this->table)
            ->result();
    }

    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    public function get_for_dropdown()
    {
        return $this->db->select('id, nama')
            ->order_by('nama')
            ->get($this->table)
            ->result();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }
}
