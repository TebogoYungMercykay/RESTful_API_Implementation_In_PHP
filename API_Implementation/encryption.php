<?php
    // *--------------- START: Encryption  Class Starts Here ---------------
    class Encryption {
        // * The Constructor for the Encryption class
        public function __construct() {
            // Default Constructor
        }

        // * Generate a RANDOM SALT value between [2000000000, 2147483646].
        public function generateSalt() {
            $min = 2000000000;
            $max = 2147483646;
            return rand($min, $max);
        }

        // * Generating random API Keys, default length is 20
        public function generateRandomAPIKey($length = 32) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            if($length > 32) {
                $length = 32;
            }
            // This generates a random string of length $length, which is 20 for now.
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        // * Encrypt Password and return a HASH of length 128, VARCHAR(128).
        public function encrypt_password($Password, $salt) {
            // Using 1000 iterations for the hash_pbkdf2 method, and a HASH length of 32 BYTES
            $hash = hash_pbkdf2("sha256", $Password, $salt, 1000, 32);
            // Finally i Concatenate and encode the SALT and HASH
            return base64_encode($salt . $hash);
        }

        // * Verify Password, Encrypt the Password using the encrypt_password($Password, $salt) method and compare it with the stored one.
        public function verify_password($Password, $stored_hash, $salt) {
            // $decoded = base64_decode($stored_hash);
            $hash = $this->encrypt_password($Password, $salt);
            return $hash == $stored_hash;
        }
    } // * DONE
?>