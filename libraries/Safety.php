<?php
namespace manguto\cms7\libraries;

class Safety
{

    const encrypt_method = "AES-256-CBC";

    /**
     * simple method to encrypt or decrypt a plain text string
     * initialization vector(IV) has to be the same when encrypting and decrypting *
     * 
     * @param string $action:can
     *            be 'encrypt' or 'decrypt'
     * @param string $string:string
     *            to encrypt or decrypt
     * @return string
     */
    static private function encrypt_decrypt($action, $string,$secret_key,$secret_iv)
    {
        $output = false;
        // hash
        $key = hash('sha256', $secret_key);
        
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == 'encrypt') {
            
            $output = openssl_encrypt($string, self::encrypt_method, $key, 0, $iv);            
            if ($output === false) {
                $error = '';
                while ($msg = openssl_error_string()) {
                    $error .= $msg . "<br />\n";
                }
                throw new Exception("Não foi possível realizar a criptografia. <hr/>$error");
            }
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            // debug($string,0); debug(base64_decode($string),0);
            $string = base64_decode($string);
            $output = openssl_decrypt($string, self::encrypt_method, $key, 0, $iv);
            if ($output === false) {
                $error = '';
                while ($msg = openssl_error_string()) {
                    $error .= $msg . "<br />\n";
                }
                throw new Exception("Não foi possível realizar a descriptografia. <hr/>$error");
            }
        } else {
            throw new Exception("Tipo de cifragem desconhecido ($action).");
        }
        return $output;
    }
    
    /**
     * encrypt text
     * @param string $string
     * @param string $secret_key
     * @param string $secret_iv
     * @return string
     */
    static function encrypt(string $string,string $secret_key='This is my secret key',string $secret_iv='This is my secret iv'):string
    {
        return self::encrypt_decrypt('encrypt', $string, $secret_key, $secret_iv);
    }
    
    /**
     * decrypt text
     * @param string $string
     * @param string $secret_key
     * @param string $secret_iv
     * @return string
     */
    static function decrypt(string $string,string $secret_key='This is my secret key',string $secret_iv='This is my secret iv'):string
    {
        return self::encrypt_decrypt('decrypt', $string, $secret_key, $secret_iv);
    }

    /**
     * encrypt / decrypt test
     */
    static function test()
    {
        $plain_txt = "This is my plain text";
        echo "<pre>Plain Text =" . $plain_txt . "\n";
        $encrypted_txt = self::encrypt($plain_txt);
        echo "Encrypted Text = " . $encrypted_txt . "\n";
        $decrypted_txt = self::decrypt($encrypted_txt);
        echo "Decrypted Text =" . $decrypted_txt . "\n";
        if ($plain_txt === $decrypted_txt)
            echo "<h1>SUCCESS! OK!!</h1>";
        else
            echo "FAILED";
        echo "\n";
        exit();
    }
}

?>