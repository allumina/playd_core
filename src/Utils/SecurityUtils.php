<?php

namespace Allumina\PlaydCore\Utils;

class SecurityUtils
{
    public static function generateKey(int $length = 64)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Encrypt the passed file and saves the result in a new file with ".enc" as suffix.
     *
     * @param string $source Path to file that should be encrypted
     * @param string $key    The key used for the encryption
     * @param string $dest   File name where the encryped file should be written to.
     * @return string|false  Returns the file name that has been created or FALSE if an error occured
     */
    public static function encryptFile($source, $key, $dest)
    {
        $key = substr(sha1($key, true), 0, 16);
        $iv = openssl_random_pseudo_bytes(16);

        $error = false;
        if ($fpOut = fopen($dest, 'w')) {
            // Put the initialzation vector to the beginning of the file
            fwrite($fpOut, $iv);
            if ($fpIn = fopen($source, 'rb')) {
                while (!feof($fpIn)) {
                    $plaintext = fread($fpIn, 16 * config('security.file_encryption_blocks'));
                    $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($fpOut, $ciphertext);
                }
                fclose($fpIn);
            } else {
                $error = true;
            }
            fclose($fpOut);
        } else {
            $error = true;
        }
        return $error ? false : $dest;
    }

    /**
     * Dencrypt the passed file and saves the result in a new file, removing the
     * last 4 characters from file name.
     *
     * @param string $source Path to file that should be decrypted
     * @param string $key    The key used for the decryption (must be the same as for encryption)
     * @param string $dest   File name where the decryped file should be written to.
     * @return string|false  Returns the file name that has been created or FALSE if an error occured
     */
    public static function decryptFile($source, $key, $dest)
    {
        $key = substr(sha1($key, true), 0, 16);
        $error = false;
        if ($fpOut = fopen($dest, 'w')) {
            if ($fpIn = fopen($source, 'rb')) {
                // Get the initialzation vector from the beginning of the file
                $iv = fread($fpIn, 16);
                while (!feof($fpIn)) {
                    $ciphertext = fread($fpIn, 16 * (config('security.file_encryption_blocks') + 1)); // we have to read one block more for decrypting than for encrypting
                    $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($fpOut, $plaintext);
                }
                fclose($fpIn);
            } else {
                $error = true;
            }
            fclose($fpOut);
        } else {
            $error = true;
        }

        return $error ? false : $dest;
    }

    public static function encrypt($data, $password, $algorithm = "AES256", $cost = 12)
    {
        $params = self::getEncryptParams($password, $algorithm, $cost);
        if ($params)  // av3DYGLkwBsErphcyYp+imUW4QKs19hUnFyyYcXwURU=
            return base64_encode(openssl_encrypt($data, $params["method"], $params["key"], OPENSSL_RAW_DATA, $params["iv"]));
    }

    public static function decrypt($encrypted, $password, $algorithm = "AES256", $cost = 12)
    {
        $params = self::getEncryptParams($password, $algorithm, $cost);
        if ($params) // My secret message 1234
            return openssl_decrypt(base64_decode($encrypted), $params["method"], $params["key"], OPENSSL_RAW_DATA, $params["key"]);
    }

    private static function getEncryptParams($password, $algorithm, $cost = 12)
    {
        if ($password && $algorithm) {
            switch ($algorithm) {
                case "AES128":
                    $method = "aes-128-cbc";
                    break;
                case "AES192":
                    $method = "aes-192-cbc";
                    break;
                case "AES256":
                    $method = "aes-256-cbc";
                    break;
                case "BF":
                    $method = "bf-cbc";
                    break;
                case "CAST":
                    $method = "cast5-cbc";
                    break;
                case "IDEA":
                    $method = "idea-cbc";
                    break;
                default:
            }

            // IV must be exact 16 chars (128 bit)
            if ($method) {
                return array(
                    "key" => password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost])
                , "method" => $method
                , "iv" => chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0)
                );
            }
        }
    }
}
