<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_tables extends CI_Migration {

    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS users (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              username VARCHAR(50) NOT NULL UNIQUE,
              password VARCHAR(255) NOT NULL,
              nama VARCHAR(100) NOT NULL,
              role ENUM('admin','dokter','staff') NOT NULL DEFAULT 'staff',
              is_active TINYINT(1) NOT NULL DEFAULT 1,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS dokter (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              user_id INT UNSIGNED NULL,
              nama VARCHAR(100) NOT NULL,
              no_sip VARCHAR(50) NULL,
              spesialisasi VARCHAR(100) NULL,
              no_telp VARCHAR(30) NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (user_id) REFERENCES users(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS pasien (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              no_rm VARCHAR(30) NOT NULL UNIQUE,
              nama VARCHAR(100) NOT NULL,
              tanggal_lahir DATE NULL,
              jenis_kelamin ENUM('L','P') NULL,
              alamat TEXT NULL,
              no_telp VARCHAR(30) NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS obat (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              nama_obat VARCHAR(150) NOT NULL,
              satuan VARCHAR(30) NULL,
              stok INT NOT NULL DEFAULT 0,
              harga DECIMAL(12,2) NOT NULL DEFAULT 0,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS surat (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              nomor_surat VARCHAR(50) NOT NULL UNIQUE,
              jenis_surat VARCHAR(100) NULL,
              dokter_id INT UNSIGNED NOT NULL,
              pasien_id INT UNSIGNED NOT NULL,
              isi TEXT NULL,
              file_pdf VARCHAR(255) NULL,
              file_pdf_public_url VARCHAR(500) NULL,
              status ENUM('draft','menunggu_approval','terverifikasi','ditolak','kedaluwarsa','gagal_kirim') NOT NULL DEFAULT 'draft',
              source_ref VARCHAR(64) NULL UNIQUE,
              approval_id CHAR(36) NULL UNIQUE,
              approver_user_id VARCHAR(50) NULL,
              expires_in_hours SMALLINT UNSIGNED NULL,
              decided_by VARCHAR(50) NULL,
              decided_at DATETIME NULL,
              channel VARCHAR(20) NULL,
              decision_note TEXT NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (dokter_id) REFERENCES dokter(id),
              FOREIGN KEY (pasien_id) REFERENCES pasien(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS resep (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              nomor_resep VARCHAR(50) NOT NULL UNIQUE,
              dokter_id INT UNSIGNED NOT NULL,
              pasien_id INT UNSIGNED NOT NULL,
              tanggal DATE NOT NULL,
              catatan TEXT NULL,
              file_pdf VARCHAR(255) NULL,
              file_pdf_public_url VARCHAR(500) NULL,
              status ENUM('draft','menunggu_approval','terverifikasi','ditolak','kedaluwarsa','gagal_kirim') NOT NULL DEFAULT 'draft',
              source_ref VARCHAR(64) NULL UNIQUE,
              approval_id CHAR(36) NULL UNIQUE,
              approver_user_id VARCHAR(50) NULL,
              expires_in_hours SMALLINT UNSIGNED NULL,
              decided_by VARCHAR(50) NULL,
              decided_at DATETIME NULL,
              channel VARCHAR(20) NULL,
              decision_note TEXT NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (dokter_id) REFERENCES dokter(id),
              FOREIGN KEY (pasien_id) REFERENCES pasien(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS resep_detail (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              resep_id INT UNSIGNED NOT NULL,
              obat_id INT UNSIGNED NOT NULL,
              jumlah INT NOT NULL DEFAULT 1,
              aturan_pakai VARCHAR(255) NULL,
              FOREIGN KEY (resep_id) REFERENCES resep(id) ON DELETE CASCADE,
              FOREIGN KEY (obat_id) REFERENCES obat(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS approval_logs (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              source_ref VARCHAR(64) NOT NULL,
              approval_id CHAR(36) NULL,
              module_type ENUM('surat','resep') NOT NULL,
              direction ENUM('outbound','inbound') NOT NULL,
              endpoint VARCHAR(255) NULL,
              http_method VARCHAR(10) NULL,
              request_payload TEXT NULL,
              response_payload TEXT NULL,
              http_status INT NULL,
              status ENUM('sukses','gagal') NOT NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS settings (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `key` VARCHAR(100) NOT NULL UNIQUE,
              `value` TEXT NULL,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Seed default admin user (password: admin123)
        $this->db->query("
            INSERT IGNORE INTO users (username, password, nama, role)
            VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'Administrator', 'admin')
        ");

        // Seed default settings untuk ApprovalSmart
        $settings = [
            ['key' => 'approvalsmart_base_url',     'value' => ''],
            ['key' => 'approvalsmart_hmac_key_id',  'value' => ''],
            ['key' => 'approvalsmart_hmac_secret',  'value' => ''],
            ['key' => 'approvalsmart_legacy_token', 'value' => ''],
            ['key' => 'approvalsmart_approver_id',  'value' => ''],
        ];
        foreach ($settings as $s) {
            $this->db->query(
                "INSERT IGNORE INTO settings (`key`, `value`) VALUES ('{$s['key']}', '{$s['value']}')"
            );
        }
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['resep_detail','approval_logs','surat','resep','obat','pasien','dokter','users','settings'] as $t) {
            $this->db->query("DROP TABLE IF EXISTS `{$t}`");
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }
}
