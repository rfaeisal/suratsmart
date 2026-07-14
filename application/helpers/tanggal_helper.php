<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function tgl_indonesia($date)
{
    static $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',    4 => 'April',
        5 => 'Mei',     6 => 'Juni',     7 => 'Juli',      8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $ts = is_numeric($date) ? (int)$date : strtotime($date);
    return (int)date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}
