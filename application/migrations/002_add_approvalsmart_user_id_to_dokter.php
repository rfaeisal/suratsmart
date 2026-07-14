<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cleanup_approver_setting extends CI_Migration {

    public function up()
    {
        $this->db->delete('settings', ['key' => 'approvalsmart_approver_id']);
    }

    public function down()
    {
        $this->db->query("INSERT IGNORE INTO settings (`key`, `value`) VALUES ('approvalsmart_approver_id', '')");
    }
}
