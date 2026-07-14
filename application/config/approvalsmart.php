<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// File ini GITIGNORED. Isi dengan nilai asli dari tim ApprovalSmart.
// Lihat approvalsmart.php.example untuk template.

$config['approvalsmart_base_url']     = getenv('APPROVALSMART_BASE_URL')     ?: '';
$config['approvalsmart_hmac_key_id']  = getenv('APPROVALSMART_HMAC_KEY_ID')  ?: '';
$config['approvalsmart_hmac_secret']  = getenv('APPROVALSMART_HMAC_SECRET')  ?: '';
$config['approvalsmart_legacy_token'] = getenv('APPROVALSMART_LEGACY_TOKEN') ?: '';
$config['approvalsmart_timeout']      = 30;
