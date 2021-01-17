<?php

namespace geekpop;

class Encryption extends GeekPop
{

    public function en($delta)
    {
        $ivlen = openssl_cipher_iv_length($encrypt_method = "AES-256-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $key = hash('sha256', ENCRYPT['secret_key']);
        $output = openssl_encrypt($delta, $encrypt_method, $key, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $output, $key, $as_binary = true);
        $ciphertext = base64_encode($iv . $hmac . $output);
        return rawurlencode($ciphertext);

    }

    public function de($delta, $json = null)
    {
        $x = rawurldecode($delta);
        $c = base64_decode($x);
        $ivlen = openssl_cipher_iv_length($encrypt_method = "AES-256-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $key = hash('sha256', ENCRYPT['secret_key']);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $encrypt_method, $key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        if (!hash_equals(is_bool($hmac) ? '' : $hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
        {
            if ($json) {
                $modal = [
                    "param" => 1,
                    "key" => $json,
                    //"html" => parent::RandErr(parent::jsonDBIterator("printer", true)->errors->oops),
                    "modal" => 'modal_warning'];
                try {
                    //parent::DisplayObject($modal);
                } catch (\Exception $e) {
                    print  $e->getMessage();
                    exit();
                }
            } else {
                die("bad request");
            }
        }
        return explode(DELIM, $original_plaintext);
    }

    function bp($delta)
    {
        return $delta;
    }
}