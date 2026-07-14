<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pdf_generator {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function generate_surat($surat_id)
    {
        $surat = $this->CI->db
            ->select('surat.*, dokter.nama AS nama_dokter, dokter.no_sip, dokter.spesialisasi, pasien.nama AS nama_pasien, pasien.no_rm, pasien.tanggal_lahir, pasien.jenis_kelamin, pasien.alamat AS alamat_pasien')
            ->join('dokter', 'dokter.id = surat.dokter_id')
            ->join('pasien', 'pasien.id = surat.pasien_id')
            ->get_where('surat', ['surat.id' => $surat_id])
            ->row();

        if (!$surat) return FALSE;

        $html  = $this->CI->load->view('pdf/surat', ['surat' => $surat], TRUE);
        $path  = FCPATH . 'uploads/pdf/surat/surat_' . $surat_id . '.pdf';
        $this->_render($html, $path);
        return 'uploads/pdf/surat/surat_' . $surat_id . '.pdf';
    }

    public function generate_resep($resep_id)
    {
        $resep = $this->CI->db
            ->select('resep.*, dokter.nama AS nama_dokter, dokter.no_sip, dokter.spesialisasi, pasien.nama AS nama_pasien, pasien.no_rm, pasien.tanggal_lahir, pasien.jenis_kelamin')
            ->join('dokter', 'dokter.id = resep.dokter_id')
            ->join('pasien', 'pasien.id = resep.pasien_id')
            ->get_where('resep', ['resep.id' => $resep_id])
            ->row();

        if (!$resep) return FALSE;

        $detail = $this->CI->db
            ->select('resep_detail.*, obat.nama_obat, obat.satuan')
            ->join('obat', 'obat.id = resep_detail.obat_id')
            ->get_where('resep_detail', ['resep_id' => $resep_id])
            ->result();

        $html = $this->CI->load->view('pdf/resep', ['resep' => $resep, 'detail' => $detail], TRUE);
        $path = FCPATH . 'uploads/pdf/resep/resep_' . $resep_id . '.pdf';
        $this->_render($html, $path);
        return 'uploads/pdf/resep/resep_' . $resep_id . '.pdf';
    }

    public function download($file_path)
    {
        $full_path = FCPATH . $file_path;
        if (!file_exists($full_path)) return FALSE;

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($full_path) . '"');
        header('Content-Length: ' . filesize($full_path));
        readfile($full_path);
        exit;
    }

    private function _render($html, $save_path)
    {
        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 20,
            'margin_bottom' => 20,
            'margin_left'   => 25,
            'margin_right'  => 20,
            'tempDir'       => FCPATH . 'uploads/pdf/tmp',
        ]);
        $mpdf->SetTitle('SuratSmart');
        $mpdf->WriteHTML($html);
        $mpdf->Output($save_path, 'F');
    }
}
