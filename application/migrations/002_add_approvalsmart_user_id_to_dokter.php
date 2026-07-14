<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_approvalsmart_user_id_to_dokter extends CI_Migration {

    public function up()
    {
        // approver_user_id yang dikirim ke ApprovalSmart = users.id dari aplikasi ini.
        // Tim ApprovalSmart yang mapping user ID kita ke Telegram.
        // Tidak perlu kolom tambahan di tabel dokter — cukup dokter.user_id.
        $this->db->delete('settings', ['key' => 'approvalsmart_approver_id']);
    }

    public function down()
    {
        $this->db->query("INSERT IGNORE INTO settings (`key`, `value`) VALUES ('approvalsmart_approver_id', '')");
    }
}
