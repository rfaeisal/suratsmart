<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model {

    protected $table = 'settings';

    public function get_all()
    {
        $rows    = $this->db->get($this->table)->result();
        $result  = [];
        foreach ($rows as $row) {
            $result[$row->key] = $row->value;
        }
        return $result;
    }

    public function get($key)
    {
        $row = $this->db->get_where($this->table, ['key' => $key])->row();
        return $row ? $row->value : NULL;
    }

    public function set($key, $value)
    {
        if ($this->db->get_where($this->table, ['key' => $key])->num_rows() > 0) {
            return $this->db->update($this->table, ['value' => $value], ['key' => $key]);
        }
        return $this->db->insert($this->table, ['key' => $key, 'value' => $value]);
    }

    public function save_all(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }
}
