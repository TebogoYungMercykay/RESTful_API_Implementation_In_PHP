<?php
    require_once('singleton.php');
    require_once('encryption.php');

    // *--------------- START: Query Database Class Starts Here ---------------
    class Query_Database {
        // * Member variables for Query_Database class
        private $encryption = null;
        private $connectionObject = null;
        // * The Constructor for the Query_Database class
        public function __construct() {
            $this->encryption = new Encryption();
            $this->connectionObject = Singleton_Connection::instance();
        }

        // * Method to check whether a User already Exists by their email; returns true if successful else return false
        public function userExists($Email) {
            $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("SELECT * FROM users WHERE email = ?");
            $UserQueryExecution->bind_param("s", $Email);
            $UserQueryExecution->execute();
            $result = $UserQueryExecution->get_result();
            if ($result->num_rows == 0) {
                return false;
            }
            return true;
        }

        // * Method for Checking if a certain API key is in the database
        public function keyExists($key) {
            $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("SELECT * FROM users WHERE API_key = ?");
            $UserQueryExecution->bind_param("s", $key);
            $UserQueryExecution->execute();
            $result = $UserQueryExecution->get_result();
            if ($result->num_rows == 0) {
                return false;
            }
            return true;
        }

        // * Getting the User's Name and Surname
        public function getUserName($Email) {
            $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("SELECT name, surname FROM users WHERE email = ?");
            $UserQueryExecution->bind_param("s", $Email);
            $UserQueryExecution->execute();
            $result = $UserQueryExecution->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row["name"] . " " . $row["surname"];
            } else {
                return "The Email entered is Incorrect!";
            }
        }

        // * Getting the User's API Key
        public function getAPI_Key($Email) {
            $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("SELECT API_key FROM users WHERE email = ?");
            $UserQueryExecution->bind_param("s", $Email);
            $UserQueryExecution->execute();
            $result = $UserQueryExecution->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row["API_key"];
            } else {
                return "The Email entered is Incorrect!";
            }
        }

        // * Getting the User's Email
        public function getEmail($api_key) {
            $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("SELECT email FROM users WHERE API_key = ?");
            $UserQueryExecution->bind_param("s", $api_key);
            $UserQueryExecution->execute();
            $result = $UserQueryExecution->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row["email"];
            } else {
                return "The API_Key entered is Incorrect!";
            }
        }

        //  * Log a User Out
        public function Logout_Request($api_key) {
            if ($this->keyExists($api_key)) {
                // Connecting to the database to store User information
                $logged_in = false;
                $UserQueryExecution2 = $this->connectionObject->getInitConnection()->prepare("UPDATE users SET logged_in=? WHERE API_key=?");
                $UserQueryExecution2->bind_param("is", $logged_in, $api_key);
                $UserQueryExecution2->execute();
                // If no row was added
                if ($UserQueryExecution2->affected_rows > 0) {
                    return true;
                }
            }
            return "The API_Key entered is Incorrect!";
        }

        //  * Log a User Out
        public function Login_Request($api_key) {
            if ($this->keyExists($api_key)) {
                // Connecting to the database to store User information
                $logged_in = true;
                $UserQueryExecution2 = $this->connectionObject->getInitConnection()->prepare("UPDATE users SET logged_in=? WHERE API_key=?");
                $UserQueryExecution2->bind_param("is", $logged_in, $api_key);
                $UserQueryExecution2->execute();
                // If no row was added
                if ($UserQueryExecution2->affected_rows > 0) {
                    return true;
                }
            }
            return "The API_Key entered is Incorrect!";
        }

        // * PREFERENCES TABLE
        public function Add_Update_Preference($api_key, $theme, $pref) {
            // Check if the User already exists in the database
            if ($this->keyExists($api_key)) {
                // Check if there's an existing preference with the same API key
                $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("SELECT * FROM  preferences WHERE API_key=?");
                $UserQueryExecution->bind_param("s", $api_key);
                $UserQueryExecution->execute();
                $result = $UserQueryExecution->get_result();
                if ($result->num_rows > 0) {
                    // Connecting to the database to store User information
                    $UserQueryExecution2 = $this->connectionObject->getInitConnection()->prepare("UPDATE preferences SET theme=?, pref=? WHERE API_key=?");
                    $UserQueryExecution2->bind_param("sss", $theme, $pref, $api_key);
                    $UserQueryExecution2->execute();
                    // If no row was added
                    if ($UserQueryExecution2->affected_rows <= 0) {
                        return false;
                    }
                    return true;
                } else {
                    // Connecting to the database to store User information
                    $UserQueryExecution2 = $this->connectionObject->getInitConnection()->prepare("INSERT INTO preferences (API_key, theme, pref) VALUES (?,?,?)");
                    $UserQueryExecution2->bind_param("sss",  $api_key, $theme, $pref);
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

        // * Generating a new API Key for a User
        public function Generate_ApiKey($api_key) {
            if ($this->keyExists($api_key)) {
                // Generating a new API key
                $new_apiKey = $this->encryption->generateRandomAPIKey();
                // Start a database transaction
                $this->connectionObject->getInitConnection()->begin_transaction();
                try {
                    // Temporarily disable the foreign key constraint in preferences
                    $this->connectionObject->getInitConnection()->query("SET FOREIGN_KEY_CHECKS=0");
                    // Update the API key in the preferences table
                    $PreferenceQueryExecution = $this->connectionObject->getInitConnection()->prepare("UPDATE preferences SET API_key=? WHERE API_key=?");
                    $PreferenceQueryExecution->bind_param("ss", $new_apiKey, $api_key);
                    $PreferenceQueryExecution->execute();
                    // Check if the update was successful in the preferences table
                    if ($PreferenceQueryExecution->affected_rows >= 0) {
                        // Now, update the API key in the users table
                        $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("UPDATE users SET API_key=? WHERE API_key=?");
                        $UserQueryExecution->bind_param("ss", $new_apiKey, $api_key);
                        $UserQueryExecution->execute();
                        // Check if the update was successful in the users table
                        if ($UserQueryExecution->affected_rows > 0) {
                            // Commit the transaction if both updates were successful
                            $this->connectionObject->getInitConnection()->commit();
                            return $new_apiKey;
                        }
                    }
                } catch (Exception $exception) {
                    // Handle any exceptions and rollback the transaction
                    $this->connectionObject->getInitConnection()->rollback();
                } finally {
                    // Re-enable the foreign key constraint
                    $this->connectionObject->getInitConnection()->query("SET FOREIGN_KEY_CHECKS=1");
                }
            }
            return false;
        }

        // * Delete Account
        public function Delete_Account($api_key, $Email, $Password) {
            if ($this->validateLogin($Email, $Password) == true && $this->getEmail($api_key) == $Email) {
                $this->connectionObject->getInitConnection()->begin_transaction();
                try {
                    // Now, update the API key in the preferences table
                    $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("DELETE FROM preferences WHERE API_key=?");
                    $UserQueryExecution->bind_param("s", $api_key);
                    $UserQueryExecution->execute();
                    // Check if the delete was successful in the preferences table
                    if ($UserQueryExecution->affected_rows >= 0) {
                        // Delete the associated preferences
                        $PreferenceQueryExecution = $this->connectionObject->getInitConnection()->prepare("DELETE FROM users WHERE API_key=?");
                        $PreferenceQueryExecution->bind_param("s", $api_key);
                        $PreferenceQueryExecution->execute();
                        // Commit the transaction if both deletes were successful
                        if ($PreferenceQueryExecution->affected_rows > 0) {
                            $this->connectionObject->getInitConnection()->commit();
                            return "Account Deletion Successful!";
                        }
                    }
                } catch (Exception $exception) {
                    // Handle database-related exceptions
                    $this->connectionObject->getInitConnection()->rollback();
                    return "Database error: " . $exception->getMessage();
                } finally {
                    // Re-enable the foreign key constraint
                    $this->connectionObject->getInitConnection()->query("SET FOREIGN_KEY_CHECKS=1");
                }
            } else {
                return "Incorrect Details";
            }
        }

        // * Change Password
        public function Change_Password($api_key, $new_password) {
            // Check if the User already exists in the database
            if ($this->keyExists($api_key)) {
                // Disable foreign key checks
                try {
                    $salt = $this->encryption->generateSalt();
                    $new_password = $this->encryption->encrypt_password($new_password, $salt);
                    $this->connectionObject->getInitConnection()->begin_transaction();
                    // Disable foreign key checks
                    $disableForeignKeySQL = "SET FOREIGN_KEY_CHECKS=0";
                    $this->connectionObject->getInitConnection()->query($disableForeignKeySQL);
                    // Connecting to the database to store User information
                    $UserQueryExecution2 = $this->connectionObject->getInitConnection()->prepare("UPDATE users SET password=?, salt=? WHERE API_key=?");
                    $UserQueryExecution2->bind_param("sis", $new_password, $salt, $api_key);
                    $UserQueryExecution2->execute();
                    // Re-enable foreign key checks
                    $enableForeignKeySQL = "SET FOREIGN_KEY_CHECKS=1";
                    $this->connectionObject->getInitConnection()->query($enableForeignKeySQL);
                    // Commit the transaction
                    $this->connectionObject->getInitConnection()->commit();
                    // If no row was added
                    if ($UserQueryExecution2->affected_rows > 0) {
                        return true;
                    } else {
                        return "Internal Server Error/Incorrect key";
                    }
                } catch (Exception $exception) {
                    // Rollback the transaction if something goes wrong
                    $this->connectionObject->getInitConnection()->rollback();
                }
            } else {
                return "Incorrect API Key";
            }
        }

        // * Change Password
        public function Change_Password_2($username, $password, $new_password) {
            // Check if the User already exists in the database
            $validate = $this->validateLogin($username, $password);
            if ($this->validateLogin($username, $password)) {
                $validate = $this->validateSignupInputs("Logic", "Legends", "myemail@gmail.com", $new_password, $new_password);
                // Disable foreign key checks
                if ($validate == "SUCCESSFUL") {
                    try {
                        $api_key = $this->getAPI_Key($username);
                        $salt = $this->encryption->generateSalt();
                        $new_password = $this->encryption->encrypt_password($new_password, $salt);
                        $this->connectionObject->getInitConnection()->begin_transaction();
                        // Disable foreign key checks
                        $disableForeignKeySQL = "SET FOREIGN_KEY_CHECKS=0";
                        $this->connectionObject->getInitConnection()->query($disableForeignKeySQL);
                        // Connecting to the database to store User information
                        $UserQueryExecution2 = $this->connectionObject->getInitConnection()->prepare("UPDATE users SET password=?, salt=? WHERE API_key=?");
                        $UserQueryExecution2->bind_param("sis", $new_password, $salt, $api_key);
                        $UserQueryExecution2->execute();
                        // Re-enable foreign key checks
                        $enableForeignKeySQL = "SET FOREIGN_KEY_CHECKS=1";
                        $this->connectionObject->getInitConnection()->query($enableForeignKeySQL);
                        // Commit the transaction
                        $this->connectionObject->getInitConnection()->commit();
                        // If no row was added
                        if ($UserQueryExecution2->affected_rows > 0) {
                            return true;
                        } else {
                            return "Internal Server Error/Incorrect key";
                        }
                    } catch (Exception $exception) {
                        // Rollback the transaction if something goes wrong
                        $this->connectionObject->getInitConnection()->rollback();
                    }
                } else {
                    return $validate;
                }
            } else {
                return $validate;
            }
        }

        // * Get Data from the Database
        public function Get_Data($api_key, $limit, $sort, $order) {
            if ($this->keyExists($api_key)) {
                $body_type = "Coupe";
                // Corrected the SQL query, removed "SORT BY" and added placeholders for sorting and ordering.
                $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("SELECT * FROM cars WHERE body_type = ? ORDER BY $sort $order LIMIT ?");
                // $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("SELECT * FROM cars WHERE body_type = ? LIMIT ?");
                // Check if the prepare statement succeeded
                if (!$UserQueryExecution) {
                    return null; // Return null on error
                }
                // Bind the parameters
                $UserQueryExecution->bind_param("si", $body_type, $limit);
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
                $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("SELECT password, salt FROM users WHERE email = ?");
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
                    if ($this->encryption->verify_password($Password, $pass, $salt)) {
                        return true;
                    }
                    return "Incorrect password Entered";
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
            // The EMAIL contains '@gmail.com' or '@icloud.co.za', and also that it has a letter on the LEFT.
            if (!preg_match('/^[a-zA-Z].*@gmail\.com$|^[a-zA-Z].*@icloud\.co\.za$/', $Email)) {
                return "The EMAIL SHOULD contain '@gmail.com' or '@icloud.co.za', and AT LEAST a letter on the LEFT.";
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

        // * The Add User method for the Query_Database class
        public function addUser($Name, $Surname, $Email, $Password, $account) {
            $API_key = null;
            // Check if the User already exists in the database with same email
            if ($this->userExists($Email)) {
                return "User already exists";
            } else {
                $salt = $this->encryption->generateSalt();
                $logged = true;
                // Hash PASSWORD using the random number as the salt with "sha256" and hash_pbkdf2 method
                $hashedPassword = $this->encryption->encrypt_password($Password, $salt);
                // Generating an API key for the User
                $API_key = $this->encryption->generateRandomAPIKey();
                // Connecting to the database to store User information
                $UserQueryExecution = $this->connectionObject->getInitConnection()->prepare("INSERT INTO users (name, surname, email, password, API_key, salt, account, logged_in) VALUES (?,?,?,?,?,?,?,?)");
                $UserQueryExecution->bind_param("sssssisi", $Name, $Surname, $Email, $hashedPassword, $API_key, $salt, $account, $logged);
                $UserQueryExecution->execute();
                // If no row was added
                if ($UserQueryExecution->affected_rows <= 0) {
                    return "Internal Server Error, Please Sign In Again";
                }
                return $API_key;
            }
        }
    } // * DONE
?>