<?php
class HybridCrypto {
    private $aesMethod = 'AES-256-CBC';
    private $aesKey;
    private $iv;
    private $privateKey;
    private $publicKey;

    public function __construct() {
        // 1. Kunci simetris & IV
        $this->aesKey = openssl_random_pseudo_bytes(32);
        $this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->aesMethod));

        // 2. Deteksi otomatis path openssl.cnf berdasarkan direktori PHP yang aktif
        $opensslConfigPath = '';
        if (file_exists('C:/laragon/bin/php/')) {
            $phpDirs = glob('C:/laragon/bin/php/php-*', GLOB_ONLYDIR);
            if (!empty($phpDirs)) {
                $latestPhp = end($phpDirs);
                if (file_exists($latestPhp . '/extras/ssl/openssl.cnf')) {
                    $opensslConfigPath = $latestPhp . '/extras/ssl/openssl.cnf';
                }
            }
        } elseif (file_exists('C:/xampp/php/extras/ssl/openssl.cnf')) {
            $opensslConfigPath = 'C:/xampp/php/extras/ssl/openssl.cnf';
        }

        // 3. Konfigurasi RSA
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        if (!empty($opensslConfigPath)) {
            $config["config"] = $opensslConfigPath;
        }

        // Generate RSA Key Pair
        $res = openssl_pkey_new($config);

        if ($res === false) {
            $errorMsg = "Gagal membangkitkan kunci RSA. Detail Error: ";
            while ($msg = openssl_error_string()) {
                $errorMsg .= $msg . " ";
            }
            die($errorMsg);
        }

        openssl_pkey_export($res, $this->privateKey, null, !empty($opensslConfigPath) ? ["config" => $opensslConfigPath] : null);
        $keyDetails = openssl_pkey_get_details($res);
        $this->publicKey = $keyDetails['key'];
    }

    public function encryptContent($data) {
        $encrypted = openssl_encrypt($data, $this->aesMethod, $this->aesKey, 0, $this->iv);
        return base64_encode($encrypted);
    }

    public function decryptContent($encryptedData) {
        $decoded = base64_decode($encryptedData);
        return openssl_decrypt($decoded, $this->aesMethod, $this->aesKey, 0, $this->iv);
    }

    public function signData($data) {
        openssl_sign($data, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    public function verifySignature($data, $encodedSignature) {
        $signature = base64_decode($encodedSignature);
        return openssl_verify($data, $signature, $this->publicKey, OPENSSL_ALGO_SHA256) === 1;
    }
}
?>