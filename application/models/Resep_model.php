<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resep_model extends CI_Model {

    protected $table = 'resep';

    public function get_all()
    {
        return $this->db
            ->select('resep.*, dokter.nama AS nama_dokter, pasien.nama AS nama_pasien')
            ->join('dokter', 'dokter.id = resep.dokter_id')
            ->join('pasien', 'pasien.id = resep.pasien_id')
            ->order_by('resep.id', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_by_id($id)
    {
        return $this->db
            ->select('resep.*, dokter.nama AS nama_dokter, dokter.no_sip, dokter.spesialisasi, pasien.nama AS nama_pasien, pasien.no_rm, pasien.tanggal_lahir, pasien.jenis_kelamin')
            ->join('dokter', 'dokter.id = resep.dokter_id')
            ->join('pasien', 'pasien.id = resep.pasien_id')
            ->get_where($this->table, ['resep.id' => $id])
            ->row();
    }

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();
        $this->db->update($this->table, ['source_ref' => 'resep-' . $id], ['id' => $id]);
        return $id;
    }

    public function update($id, $data)
    {
        return $this->db->update($this->table, $data, ['id' => $id]);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, ['id' => $id]);
    }

    public function get_by_source_ref($source_ref)
    {
        return $this->db->get_where($this->table, ['source_ref' => $source_ref])->row();
    }

    public function update_approval_result($id, $status, $decided_by, $decided_at, $note)
    {
        $map = ['approved' => 'terverifikasi', 'rejected' => 'ditolak', 'expired' => 'kedaluwarsa'];
        return $this->db->update($this->table, [
            'status'        => $map[$status] ?? $status,
            'decided_by'    => $decided_by,
            'decided_at'    => $decided_at,
            'decision_note' => $note,
        ], ['id' => $id]);
    }

    public function generate_nomor()
    {
        $prefix = 'RESEP/' . date('Y/m') . '/';
        $last   = $this->db->like('nomor_resep', $prefix, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();
        $seq = $last ? ((int) substr($last->nomor_resep, -3)) + 1 : 1;
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
