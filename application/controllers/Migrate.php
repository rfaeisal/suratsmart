<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Jalankan sekali lewat browser: /migrate
// Hapus atau amankan file ini setelah schema berhasil dibuat.
class Migrate extends CI_Controller {

    public function index()
    {
        $this->load->library('migration');
        if ($this->migration->current() === FALSE) {
            show_error($this->migration->error_string());
        }
        echo '<pre>Migrasi berhasil.</pre>';
        echo '<p>Sekarang bisa <a href="' . site_url('auth/login') . '">login</a> dengan username <b>admin</b> password <b>admin123</b>.</p>';
    }
}
