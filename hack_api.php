<?php
    // *******************************************************************
    // *--------------- START, SINGLETON CLASS STARTS HERE ---------------

    class Singleton_Database_Connection {
        // Member variables for Singleton_Database_Connection class
        private $Host = 'localhost';
        private $DatabaseName = 'api_db';
        private $Username = 'root';
        private $Password = '';
        private $initConnection = null;

        // Creating an instance of the Singleton_Database_Connection class
        public static function instance() {
            static $instance = null;
            if ($instance === null) {
                $instance = new Singleton_Database_Connection();
            }
            return $instance;
        }

        // Method for Closing the database connection
        private function close($connection) {
            $connection->close();
        }

        // The Constructor for the Singleton_Database_Connection class
        private function __construct() {
            if ($this->initConnection !== null) {
                if (mysqli_ping($this->initConnection)) {
                    $this->initConnection->close();
                }
            }
            // Initializing the Connection object
            $this->initConnection = new mysqli($this->Host, $this->Username, $this->Password);
            // Checking if Connection was successful
            if ($this->initConnection->connect_error) {
                die("Connection to the Database failed: " . $this->initConnection->connect_error);
            } else {
                $this->initConnection->select_db($this->DatabaseName);
            }
        }

        // The Destructor for the Singleton_Database_Connection class
        public function __destruct() { // destructor closes connection
            // Check if the connection is still open and close it
            if (mysqli_ping($this->initConnection)) {
                $this->initConnection->close();
            }
        }

        // Encrypt Password and return a HASH of length 128, VARCHAR(128).
        function encrypt_password($Password, $salt) {
            // Using 1000 iterations for the hash_pbkdf2 method, and a HASH length of 32 BYTES
            $hash = hash_pbkdf2("sha256", $Password, $salt, 1000, 32);
            // Finally i Concatenate and encode the SALT and HASH
            return base64_encode($salt . $hash);
        }

        // Verify Password, Encrypt the Password using the encrypt_password($Password, $salt) method and compare it with the stored one.
        function verify_password($Password, $stored_hash, $salt) {
            // $decoded = base64_decode($stored_hash);
            $hash = $this->encrypt_password($Password, $salt);
            return $hash == $stored_hash;
        }

        // Generating random API Keys, default length is 20
        function generateRandomAPIKey($length = 32) {
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

        // Method to check whether a User already Exists by their email; returns true if successful else return false
        public function userExists($Email) {
            $UserQueryExecution = $this->initConnection->prepare("SELECT * FROM users WHERE email = ?");
            $UserQueryExecution->bind_param("s", $Email);
            $UserQueryExecution->execute();
            $result = $UserQueryExecution->get_result();
            if ($result->num_rows == 0) {
                return false;
            }
            return true;
        }

        // Method for Checking if a certain API key is in the database
        public function keyExists($key) {
            $UserQueryExecution = $this->initConnection->prepare("SELECT * FROM users WHERE API_key = ?");
            $UserQueryExecution->bind_param("s", $key);
            $UserQueryExecution->execute();
            $result = $UserQueryExecution->get_result();
            if ($result->num_rows == 0) {
                return false;
            }
            return true;
        }

        // Getting the User's Name and Surname
        public function getUserName($Email) {
            $UserQueryExecution = $this->initConnection->prepare("SELECT name, surname FROM users WHERE email = ?");
            $UserQueryExecution->bind_param("s", $Email);
            $UserQueryExecution->execute();
            $result = $UserQueryExecution->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row["name"] . " " . $row["surname"];
            } else {
                return "No Name";
            }
        }

        // Getting the User's API Key
        public function getAPI_Key($Email) {
            $UserQueryExecution = $this->initConnection->prepare("SELECT API_key FROM users WHERE email = ?");
            $UserQueryExecution->bind_param("s", $Email);
            $UserQueryExecution->execute();
            $result = $UserQueryExecution->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row["API_key"];
            } else {
                return "No Key";
            }
        }

        // Getting the User's Email
        public function getEmail($apikey) {
            $UserQueryExecution = $this->initConnection->prepare("SELECT API_key FROM users WHERE email = ?");
            $UserQueryExecution->bind_param("s", $Email);
            $UserQueryExecution->execute();
            $result = $UserQueryExecution->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row["API_key"];
            } else {
                return "No Key";
            }
        }

        // * Change Password
        public function Change_Password($API_key, $new_password) {
            // Check if the User already exists in the database
            if ($this->keyExists($API_key)) {
                // Connecting to the database to store User information
                $UserQueryExecution2 = $this->initConnection->prepare("UPDATE users SET password=? WHERE API_key=?");
                $UserQueryExecution2->bind_param("ss", $new_password, $API_key);
                $UserQueryExecution2->execute();
                // If no row was added
                if ($UserQueryExecution2->affected_rows > 0) {
                    return true;
                }
            }
            return false;
        }

        // * Delete Account
        public function Delete_Account($Email, $Password) {
            if ($this->validateLogin($Email, $Password) === true) {
                $UserQueryExecution = $this->initConnection->prepare("DELETE FROM users WHERE email = ?");
                $UserQueryExecution->bind_param("s", $Email);
                $UserQueryExecution->execute();
                $result = $UserQueryExecution->get_result();
                // If there are some rows returned
                if ($result->num_rows > 0) {
                    return true;
                }
            }
            return "Internal Server Error/Incorrect password";
        }

        //  * Log a User Out
        public function Logout_Request($apikey) {
            if ($this->keyExists($apikey)) {
                // Connecting to the database to store User information
                $logged_in = false;
                $UserQueryExecution2 = $this->initConnection->prepare("UPDATE users SET logged_in=? WHERE API_key=?");
                $UserQueryExecution2->bind_param("is", $logged_in, $API_key);
                $UserQueryExecution2->execute();
                // If no row was added
                if ($UserQueryExecution2->affected_rows > 0) {
                    return true;
                }
            }
            return "Internal Server Error/Incorrect password";
        }

        //  * Log a User Out
        public function Login_Request($apikey) {
            if ($this->keyExists($apikey)) {
                // Connecting to the database to store User information
                $logged_in = true;
                $UserQueryExecution2 = $this->initConnection->prepare("UPDATE users SET logged_in=? WHERE API_key=?");
                $UserQueryExecution2->bind_param("is", $logged_in, $API_key);
                $UserQueryExecution2->execute();
                // If no row was added
                if ($UserQueryExecution2->affected_rows > 0) {
                    return true;
                }
            }
            return "Internal Server Error/Incorrect password";
        }

        // * PREFERENCES TABLE
        public function Add_Update_Preference($API_key, $theme, $pref) {
            // Check if the User already exists in the database
            if ($this->keyExists($API_key)) {
                // Check if there's an existing preference with the same API key
                $UserQueryExecution = $this->initConnection->prepare("SELECT * FROM  preferences WHERE API_key=?");
                $UserQueryExecution->bind_param("s", $API_key);
                $UserQueryExecution->execute();
                $result = $UserQueryExecution->get_result();
                if ($result->num_rows > 0) {
                    // Connecting to the database to store User information
                    $UserQueryExecution2 = $this->initConnection->prepare("UPDATE preferences SET theme=?, pref=? WHERE API_key=?");
                    $UserQueryExecution2->bind_param("sss", $theme, $pref, $API_key);
                    $UserQueryExecution2->execute();
                    // If no row was added
                    if ($UserQueryExecution2->affected_rows <= 0) {
                        return false;
                    }
                    return true;
                } else {
                    // Connecting to the database to store User information
                    $UserQueryExecution2 = $this->initConnection->prepare("INSERT INTO preferences (API_key, theme, pref) VALUES (?,?,?)");
                    $UserQueryExecution2->bind_param("sss",  $API_key, $theme, $pref);
                    $UserQueryExecution2->execute();
                    // If no row was added
                    if ($UserQueryExecution2->affected_rows <= 0) {
                        return false;
                    }
                    return true;
                }
            }
            return false;
        }

        // * Generate New ApiKey
        public function Generate_ApiKey($apikey) {
            if ($this->keyExists($apikey)) {
                // Connecting to the database to store User information
                $new_apiKey = $this->generateRandomAPIKey();
                $UserQueryExecution2 = $this->initConnection->prepare("UPDATE users SET API_key=? WHERE API_key=?");
                $UserQueryExecution2->bind_param("ss", $new_apiKey, $API_key);
                $UserQueryExecution2->execute();
                // If no row was added
                if ($UserQueryExecution2->affected_rows > 0) {
                    return $new_apiKey;
                }
            }
            return false;
        }

        // * Get Data from the Database
        public function Get_Data($API_key, $limit, $sort, $order) {
            if ($this->keyExists($API_key)) {
                $body_type = "Coupe";
                $UserQueryExecution = $this->initConnection->prepare("SELECT * FROM cars WHERE body_type = ? SORT BY ? ? LIMIT ?");
                $UserQueryExecution->bind_param("ssss", $body_type, $sort, $order, $limit);
                $UserQueryExecution->execute();
                $result = $UserQueryExecution->get_result();
                // If there are some rows returned
                if ($result->num_rows > 0) {
                    return $result;
                }
            }
            return null;
        }

        // * Method to check whether the provided Login details are Correct; returns true if successful else return string error message
        public function validateLogin($Email, $Password) {
            if (!$this->userExists($Email)) {
                return "User does not exist";
            } else {
                $UserQueryExecution = $this->initConnection->prepare("SELECT password, salt FROM users WHERE email = ?");
                $UserQueryExecution->bind_param("s", $Email);
                $UserQueryExecution->execute();
                $result = $UserQueryExecution->get_result();
                // If there are some rows returned
                if ($result->num_rows > 0) {
                    foreach ($result as $row) {
                        $pass = $row['password'];
                        $salt = $row['salt'];
                        break;
                    }
                    // Now verifying the password against the $pass from the database
                    if ($this->verify_password($Password, $pass, $salt)) {
                        return true;
                    }
                    return "Incorrect password";
                } else {
                    return "Internal Server Error/Incorrect password";
                }
            }
        }

        public function validateSignupInputs($Name, $Surname, $Email, $Password, $PassConfirmation) {
            // All fields are not empty
            if (empty($Name) || empty($Surname) || empty($Email) || empty($Password) || empty($PassConfirmation)) {
                return "All Fields SHOULD Not Be Empty";
            }
            // The NAME and SURNAME fields contain only Characters
            if (!preg_match('/^[a-zA-Z ]+$/', $Name) || !preg_match('/^[a-zA-Z ]+$/', $Surname)) {
                return "The NAME and SURNAME fields SHOULD contain only Characters";
            }
            // The EMAIL contains '@gmail.com' or '@tuks.co.za', and also that it has a letter on the LEFT.
            if (!preg_match('/^[a-zA-Z].*@gmail\.com$|^[a-zA-Z].*@tuks\.co\.za$/', $Email)) {
                return "The EMAIL SHOULD contain '@gmail.com' or '@tuks.co.za', and AT LEAST a letter on the LEFT.";
            }
            // Making sure the EMAIL doesn't contain Illegal Characters
            if (preg_match('/[\/\\\|<>\'\"]/', $Email)) {
                return "Make sure the EMAIL doesn't contain Illegal Characters";
            }
            // The PASSWORD is at least 8 Characters long and contains a Number, Contains a special Character, Uppercase and Lowercase letters.
            if (!preg_match('/^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).{8,}$/', $Password)) {
                return "Make sure the PASSWORD is at least 8 Characters long and contains a Number, Contains a special Character, Uppercase and Lowercase letters.";
            }
            // Making sure the PASSWORD doesn't contain Illegal Characters
            if (preg_match('/[\/\\\|<>\'\"]/', $Password)) {
                return "Make sure the PASSWORD doesn't contain Illegal Characters";
            }
            // The PASSWORD and CONFIRM PASSWORD match
            if ($Password !== $PassConfirmation) {
                return "The PASSWORD and CONFIRM PASSWORD SHOULD match";
            }
            // All Checks are SUCCESSFUL
            return "SUCCESSFUL";
        }

        // The Add User method for the Singleton_Database_Connection class
        public function addUser($Name, $Surname, $Email, $Password, $account) {
            $APIkey = null;
            // Check if the User already exists in the database with same email
            if ($this->userExists($Email)) {
                return "User already exists";
            } else {
                // Generate a RANDOM SALT value between [2000000000, 2147483646].
                $min = 2000000000;
                $max = 2147483646;
                $salt = rand($min, $max);
                $logged_in = true;
                // Hash PASSWORD using the random number as the salt with "sha256" and hash_pbkdf2 method
                $hashedPassword = $this->encrypt_password($Password, $salt);
                // Generating an API key for the User
                $APIkey = $this->generateRandomAPIKey();
                // Connecting to the database to store User information
                $UserQueryExecution = $this->initConnection->prepare("INSERT INTO users (name, surname, email, password, API_key, salt, account, logged_in) VALUES (?,?,?,?,?,?,?)");
                $UserQueryExecution->bind_param("sssssisi", $Name, $Surname, $Email, $hashedPassword, $APIkey, $salt, $account, $logged_in);
                $UserQueryExecution->execute();
                // If no row was added
                if ($UserQueryExecution->affected_rows <= 0) {
                    return "Internal server error";
                }
                return $APIkey;
            }
        }
    } // * DONE
    
    // *******************************************************************
    // *------------ DONE, POST_REQUEST_API CLASS STARTS HERE ------------

    // ******************************************************************
    // * NOW THE IMPLEMENTATION IS COMPLETE => Handling Incoming Requests

?>