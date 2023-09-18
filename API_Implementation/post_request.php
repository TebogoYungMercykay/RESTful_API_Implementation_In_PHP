<?php
    require_once('query_database.php');

    // *--------------- START: POST_Requests Class Starts Here ---------------
    class POST_Requests {
        public $connectionObject = null;
        public $finalResponse = '';
        public $type = '';
        // * -------------- Methods, Destructor And Constructor --------------
        public function __construct() {
            if ($this->connectionObject == null) {
                $this->connectionObject = new Query_Database();
            }
        }
        public function __destruct() {
            $this->connectionObject = null;
        }

        // *-------------- Response Method With all Headers --------------
        public function response($data, $code = 400) {
            header("HTTP/1.1 $code");
            header("Content-Type: application/json; charset=UTF-8");
            header('Access-Control-Allow-Origin: *');
            echo json_encode(
                $data
            );
        }

        // * This method Executes the sql request variable from the above Method
        public function send_request($result) {
            $data = $this->filterResult($result, ['id_trim', 'make', 'model', 'generation', 'year_from', 'year_to']);
            $this->finalResponse = array(
                "status" => "success",
                "timestamp" => time(),
                "data" => $data
            );
            $this->response($this->finalResponse, 200);
        }

        // * This method Creates the JSON object from request result
        public function filterResult($result, $valid) {
            $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
            // Creating an empty associative array.
            $dataFiltered = array();
            foreach ($data as $row) {
                $rowFiltered = array();
                foreach ($valid as $key) {
                    if (array_key_exists($key, $row)) {
                        $rowFiltered[$key] = $row[$key];
                    }
                }
                $dataFiltered[] = $rowFiltered;
            }
            return $dataFiltered;
        }

        // * DONE, SignUp Request
        public function SignUp_Request($name, $surname, $email, $password, $PassConfirmation, $account) {
            $validInputs = $this->connectionObject->validateSignupInputs($name, $surname, $email, $password, $PassConfirmation);
            $userExists = $this->connectionObject->userExists($email);
            if($validInputs === "SUCCESSFUL" && $userExists === false){
                $response = $this->connectionObject->addUser($name, $surname, $email, $password, $account);
                if ($response === true) {
                    $api_key = $this->connectionObject->getAPI_Key($email);
                    $this->finalResponse = [
                        "status" => "success",
                        "timestamp" => time(),
                        "data" => $api_key
                    ];
                    $this->response($this->finalResponse, 200);
                } else {
                    $this->finalResponse = [
                        "status" => "error",
                        "timestamp" => time(),
                        "data" => $response
                    ];
                    $this->response($this->finalResponse, 400);
                }
            } else if($userExists === true){
                $this->finalResponse = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => "A User with the Same email Already Exists"
                ];
                $this->response($this->finalResponse, 400);
            } else {
                $this->finalResponse = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => $validInputs
                ];
                $this->response($this->finalResponse, 400);
            }
        }

        // * DONE, Login Request
        public function Login_Request($email, $password) {
            $validInputs = $this->connectionObject->validateLogin($email, $password);
            if ($validInputs === true) {
                $api_key = $this->connectionObject->getAPI_Key($email);
                $this->connectionObject->Logout_Request($api_key);
                if ($this->connectionObject->Login_Request($api_key)) {
                    $this->finalResponse = [
                        "status" => "success",
                        "timestamp" => time(),
                        "data" => $api_key
                    ];
                    $this->response($this->finalResponse, 200);
                } else {
                    $this->finalResponse = [
                        "status" => "error",
                        "timestamp" => time(),
                        "data" => "Internal Server Error!"
                    ];
                    $this->response($this->finalResponse, 400);
                }
            } else if (!$this->connectionObject->userExists($email)) {
                $this->finalResponse = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => "No User with the Email entered!"
                ];
                $this->response($this->finalResponse, 400);
            }
            else {
                $this->finalResponse = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => "The Password entered is Incorrect!"
                ];
                $this->response($this->finalResponse, 400);
            }
        }

        // * DONE, Logout Request
        public function Logout_Request($api_key) {
            $data_result = $this->connectionObject->Logout_Request($api_key);
            if ($data_result == true) {
                $this->finalResponse = [
                    "status" => "success",
                    "timestamp" => time(),
                    "data" => "User Successfully Logged Out!"
                ];
                $this->response($this->finalResponse, 200);
            } else {
                $this->finalResponse = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => "Error. Bad Request"
                ];
                $this->response($this->finalResponse, 400);
            }
        }

        // * DONE, Delete Account
        public function Delete_Account($api_key, $username, $password) {
            if ($this->connectionObject->keyExists($api_key)) {
                $data_result = $this->connectionObject->Delete_Account($api_key, $username, $password);
                if ($data_result == "Account Deletion Successful!") {
                    $this->finalResponse = [
                        "status" => "success",
                        "timestamp" => time(),
                        "data" => "Account Deletion Successful!"
                    ];
                    $this->response($this->finalResponse, 200);
                } else {
                    $this->finalResponse = [
                        "status" => "error",
                        "timestamp" => time(),
                        "data" => $data_result
                    ];
                    $this->response($this->finalResponse, 400);
                }
            } else {
                $this->finalResponse = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => "Incorrect API Key"
                ];
                $this->response($this->finalResponse, 400);
            }
        }

        // * DONE, Change Password
        public function Change_Password($param_1, $password, $new_password) {
            if ($this->connectionObject->keyExists($param_1)) {
                $data_result = null;
                if ($password == null) {
                    $data_result = $this->connectionObject->Change_Password($param_1, $new_password);
                } else {
                    $data_result = $this->connectionObject->Change_Password_2($param_1, $password, $new_password);
                }
                if ($data_result == true) {
                    $this->finalResponse = [
                        "status" => "success",
                        "timestamp" => time(),
                        "data" => "Password Changed Successfully!"
                    ];
                    $this->response($this->finalResponse, 200);
                } else {
                    $this->finalResponse = [
                        "status" => "error",
                        "timestamp" => time(),
                        "data" => $data_result
                    ];
                    $this->response($this->finalResponse, 400);
                }
            } else {
                $this->finalResponse = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => "Incorrect API Key"
                ];
                $this->response($this->finalResponse, 400);
            }
        }

        // * DONE, Generate New ApiKey
        public function Generate_ApiKey($api_key) {
            if ($this->connectionObject->keyExists($api_key)) {
                $data_result = $this->connectionObject->Generate_ApiKey($api_key);
                if ($data_result !== false) {
                    $this->finalResponse = [
                        "status" => "success",
                        "timestamp" => time(),
                        "data" => $data_result
                    ];
                    $this->response($this->finalResponse, 200);
                }  else {
                    $this->finalResponse = [
                        "status" => "error",
                        "timestamp" => time(),
                        "data" => "Internal Server Error!"
                    ];
                    $this->response($this->finalResponse, 400);
                }
            } else {
                $this->finalResponse = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => "Incorrect API Key"
                ];
                $this->response($this->finalResponse, 400);
            }
        }

        // * DONE, Preferences
        public function Preferences($api_key, $theme, $pref) {
            if ($this->connectionObject->keyExists($api_key)) {
                $data_result = $this->connectionObject->Add_Update_Preference($api_key, $theme, $pref);
                if ($data_result == true) {
                    $this->finalResponse = [
                        "status" => "success",
                        "timestamp" => time(),
                        "data" => "Preferences Set Successfully!"
                    ];
                    $this->response($this->finalResponse, 200);
                } else {
                    $this->finalResponse = [
                        "status" => "error",
                        "timestamp" => time(),
                        "data" => "Internal Server Error!"
                    ];
                    $this->response($this->finalResponse, 400);
                }
            } else {
                $this->finalResponse = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => "Incorrect API Key"
                ];
                $this->response($this->finalResponse, 400);
            }
        }

        // * DONE, Get Data
        public function Get_Data($api_key, $limit, $sort, $order) {
            if ($this->connectionObject->keyExists($api_key)) {
                $data_result = $this->connectionObject->Get_Data($api_key, $limit, $sort, $order);
                if ($data_result !== null) {
                    $this->send_request($data_result);
                } else {
                    $this->finalResponse = [
                        "status" => "error",
                        "timestamp" => time(),
                        "data" => "Error. Bad Request"
                    ];
                    $this->response($this->finalResponse, 400);
                }
            } else {
                $this->finalResponse = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => "Incorrect API Key"
                ];
                $this->response($this->finalResponse, 400);
            }
        }

        // * Not Used Yet: Helper Method for Implementing the Fuzzy Search
        public function fuzzySearch($key_param, $value_param) {
            $search_string = trim($value_param);
            if (empty($search_string) === false) {
                if (strlen($search_string) >= 3) {
                    $len = strlen($search_string)/3;
                    $param1 = '%' . $search_string . '%';
                    $param2 = '%' . substr($search_string, 0, 2*$len) . '%';
                    $param3 = '%' . substr($search_string, $len, strlen($search_string) - 1) . '%';
                    $fuzzySearch = "$key_param LIKE '$param1' OR $key_param LIKE '$param2' OR $key_param LIKE '$param3'";
                    return $fuzzySearch;
                } else { // Length of String Less Than 3
                    $param1 = '%' . $search_string . '%';
                    $param2 = '%' . substr($search_string, 0, strlen($search_string)/2) . '%';
                    $fuzzySearch = "$key_param LIKE '$param1' OR $key_param LIKE '$param2'";
                    return $fuzzySearch;
                }
            }
            return false;
        }

        // * DONE, Get Data
        public function Generate_External_data() {
            $api_key = "167f0ee3513942fb8691390781990393"; // Replace with your actual API key
            $api_url = "https://newsapi.org/v2/everything?q=bitcoin&apiKey=$api_key";
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            $response = curl_exec($ch);
            $this->response($response, 200);
            curl_close($ch);
            if (isset($response['status']) && $response['status'] == "ok") {
                if (isset($response['articles']) && is_array($response['articles'])) {
                    $this->finalResponse = [
                        "status" => "success",
                        "timestamp" => time(),
                        "data" => $response['articles']
                    ];
                    $this->response($this->finalResponse, 200);
                } else {
                    $this->finalResponse = [
                        "status" => "error",
                        "timestamp" => time(),
                        "data" => "No data found in the JSON Response"
                    ];
                    $this->response($this->finalResponse, 400);
                }
            } else {
                $this->finalResponse = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => "Invalid or empty JSON Response"
                ];
                $this->response($this->finalResponse, 400);
            }
        }
    } // * DONE
?>