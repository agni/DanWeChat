<?php

namespace Dandelion\WeChat;

include_once "WXBizDataCrypt/WXBizDataCrypt.php";

class Crypt
{
    private $appid;
    private $sessionKey;
    private $encryptedData;
    private $iv;

    private $decryptedData;

    public function __construct($appid, $sessionKey)
    {
        $this->appid = $appid;
        $this->sessionKey = $sessionKey;
    }

    public function decryptData($encryptedData, $iv)
    {
        $this->encryptedData = $encryptedData;
        $this->iv = $iv;
        $decryptor = new \WXBizDataCrypt($this->appid, $this->sessionKey);
        $this->decryptedData = $decryptor->decryptData($this->encryptedData, $this->iv);
    }

    public function getDecryptedData()
    {
        if (is_int($this->decryptedData)) {
            return null;
        }
        return $this->decryptedData;
    }
}
