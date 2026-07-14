<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resep_detail_model extends CI_Model {

    protected $table = 'resep_detail';

    public function get_by_resep($resep_id)
    {
        return $this->db
            ->select('resep_detail.*, obat.nama_obat, obat.satuan')
            ->join('obat', 'obat.id = resep_detail.obat_id')
            ->get_where($this->table, ['resep_id' => $resep_id])
            ->result();
    }

    public function save_items($resep_id, array $obat_ids, array $jumlahs, array $aturan_pakais)
    {
        $this->db->delete($this->table, ['resep_id' => $resep_id]);
        foreach ($obat_ids as $i => $obat_id) {
            if (empty($obat_id)) continue;
            $this->db->insert($this->table, [
                'resep_id'    => $resep_id,
                'obat_id'     => (int) $obat_id,
                'jumlah'      => (int) ($jumlahs[$i] ?? 1),
                'aturan_pakai' => $aturan_pakais[$i] ?? '',
            ]);
        }
    }
}
