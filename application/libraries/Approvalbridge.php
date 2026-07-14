<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approvalbridge {

    private $CI;
    private $base_url;
    private $key_id;
    private $secret;
    private $timeout;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->_load_settings();
    }

    public function send(array $data)
    {
        $body      = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $signature = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $body, $this->secret);
        $endpoint  = rtrim($this->base_url, '/') . '/api/legacy/approvals';

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST           => TRUE,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-Timestamp: '  . $timestamp,
                'X-Key-Id: '     . $this->key_id,
                'X-Signature: '  . $signature,
            ],
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_FOLLOWLOCATION => FALSE,
        ]);

        $raw       = curl_exec($ch);
        $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err  = curl_error($ch);
        curl_close($ch);

        return [
            'ok'        => in_array($http_code, [202, 409]),
            'body'      => json_decode($raw ?: '{}', TRUE) ?? [],
            'raw'       => $raw ?: $curl_err,
            'http_code' => $http_code,
            'endpoint'  => $endpoint,
        ];
    }

    public function get_legacy_token()
    {
        return $this->_setting('approvalsmart_legacy_token');
    }

    private function _load_settings()
    {
        $this->base_url = $this->_setting('approvalsmart_base_url');
        $this->key_id   = $this->_setting('approvalsmart_hmac_key_id');
        $this->secret   = $this->_setting('approvalsmart_hmac_secret');
        $this->timeout  = (int) ($this->_setting('approvalsmart_timeout') ?: 15);
    }

    private function _setting($key)
    {
        // Prioritas: DB settings table → approvalsmart.php config
        $row = $this->CI->db->get_where('settings', ['key' => $key])->row();
        if ($row && $row->value !== '') {
            return $row->value;
        }
        $this->CI->config->load('approvalsmart', TRUE, TRUE);
        return $this->CI->config->item($key, 'approvalsmart') ?: '';
    }
}
