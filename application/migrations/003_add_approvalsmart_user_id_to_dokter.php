<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_approvalsmart_user_id_to_dokter extends CI_Migration {

    public function up()
    {
        $this->db->query("ALTER TABLE dokter ADD COLUMN approvalsmart_user_id VARCHAR(50) NULL DEFAULT NULL COMMENT 'User ID yang sudah di-mapping di ApprovalSmart' AFTER user_id");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE dokter DROP COLUMN approvalsmart_user_id");
    }
}
