<?php
class TokenStorage {
    private $storagePath = __DIR__ . '/../storage/tokens/';
    
    public function __construct() {
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0700, true);
        }
    }
    
    public function storeToken($token) {
        file_put_contents(
            $this->storagePath . md5($token),
            json_encode(['expires' => time() + 3600])
        );
    }
    
    public function invalidateToken($token) {
        $file = $this->storagePath . md5($token);
        if (file_exists($file)) {
            unlink($file);
            return true;
        }
        return false;
    }
    
    public function isValidToken($token) {
        $file = $this->storagePath . md5($token);
        return file_exists($file) && 
               json_decode(file_get_contents($file))->expires > time();
    }
}