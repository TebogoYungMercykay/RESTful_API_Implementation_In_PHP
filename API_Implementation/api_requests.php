<?php
    // *--------------- START: API Requests Class Starts Here ---------------
    require_once('post_request.php');

    // * Storing the Input data in the $json_data variable
    $json_data = file_get_contents('php://input');

    // * NOW THE IMPLEMENTATION IS COMPLETE => Handling Incoming Requests
    if ($_SERVER['REQUEST_METHOD'] == 'GET') { // Handling GET request
        $api_request_object = new POST_Requests();
        $api_request_object->Generate_External_data();
        $response = ["message" => "This is a GET request."];
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST') { // handling POST Requests
        // Now Executing things!!
        $data = json_decode($json_data, true);
        $api_request_object = new POST_Requests();
        if (isset($data['type'])) {
            // Some More Functionality Loading ...
            if ($data['type'] == "signup") {
                $name = $data['signup']['name'];
                $surname = $data['signup']['surname'];
                $email = $data['signup']['email'];
                // Decrypting the base64 email
                // $email_dec = base64_decode($email);
                // if ($email_dec !== false) {
                //     $email = $email_dec;
                // }
                $password = $data['signup']['password'];
                // Decrypting the base64 password
                // $password_dec = base64_decode($password);
                // if ($password_dec !== false) {
                //     $password = $password_dec;
                // }
                $PassConfirmation = $data['signup']['PassConfirmation'];
                // Decrypting the base64 password
                // $password_con_dec = base64_decode($PassConfirmation);
                // if ($password_dec !== false) {
                //     $PassConfirmation = $password_con_dec;
                // }
                $account = $data['signup']['account'];
                $api_request_object->SignUp_Request($name, $surname, $email, $password, $PassConfirmation, $account);
            } else if ($data['type'] == "login") {
                $email = $data['login']['username'];
                // Decrypting the base64 email
                // $email_dec = base64_decode($email);
                // if ($email_dec !== false) {
                //     $email = $email_dec;
                // }
                $password = $data['login']['password'];
                // Decrypting the base64 password
                // $password_dec = base64_decode($password);
                // if ($password_dec !== false) {
                //     $password = $password_dec;
                // }
                $api_request_object->Login_Request($email, $password);
            } else if ($data['type'] == "logout") {
                $apikey = $data['logout']['apikey'];
                $api_request_object->Logout_Request($apikey);
            } else if ($data['type'] == "delete_account") {
                $apikey = $data['delete_account']['apikey'];
                $username = $data['delete_account']['username'];
                // Decrypting the base64 email
                // $username_dec = base64_decode($username);
                // if ($username_dec !== false) {
                //     $username = $username_dec;
                // }
                $password = $data['delete_account']['password'];
                // Decrypting the base64 password
                // $password_dec = base64_decode($password);
                // if ($password_dec !== false) {
                //     $password = $password_dec;
                // }
                $api_request_object->Delete_Account($apikey, $username, $password);
            } else if ($data['type'] == "change_password" && isset($data['change_password']['username']) && isset($data['change_password']['password'])) {
                $username = $data['change_password']['username'];
                $new_password = $data['change_password']['new_password'];
                // Decrypting the base64 new_password
                // $password_con_dec = base64_decode($new_password);
                // if ($password_dec !== false) {
                //     $new_password = $password_con_dec;
                // }
                $password = $data['change_password']['password'];
                // $password_dec = base64_decode($password);
                // if ($password_dec !== false) {
                //     $password = $password_dec;
                // }
                $api_request_object->Change_Password($username, $password, $new_password);
            } else if ($data['type'] == "change_password" && isset($data['change_password']['apikey'])) {
                $apikey = $data['change_password']['apikey'];
                // Decrypting the base64 new_password
                // $password_con_dec = base64_decode($new_password);
                // if ($password_dec !== false) {
                //     $new_password = $password_con_dec;
                // }
                $new_password = $data['change_password']['new_password'];
                $api_request_object->Change_Password($apikey, null, $new_password);
            } else if ($data['type'] == "generate_apikey") {
                $apikey = $data['generate_apikey']['apikey'];
                $api_request_object->Generate_ApiKey($apikey);
            } else if ($data['type'] == "preferences") {
                $apikey = $data['preferences']['apikey'];
                $theme = $data['preferences']['theme'];
                $pref = $data['preferences']['pref'];
                $api_request_object->Preferences($apikey, $theme, $pref);
            } else if ($data['type'] == "get_data") {
                $apikey = $data['get_data']['apikey'];
                $limit = $data['get_data']['limit'];
                $sort = $data['get_data']['sort'];
                $order = $data['get_data']['order'];
                $api_request_object->Get_Data($apikey, $limit, $sort, $order);
            } else if ($data['type'] == "generate_external_data") {
                $api_request_object->Generate_External_data();
            } else {
                header("HTTP/1.1 400");
                header("Content-Type: application/json; charset=UTF-8");
                header('Access-Control-Allow-Origin: *');
                $data = [
                    "status" => "error",
                    "timestamp" => time(),
                    "data" => "Error. Bad Request"
                ];
                echo json_encode(
                    $data
                );
            }
        } else {
            header("HTTP/1.1 400");
            header("Content-Type: application/json; charset=UTF-8");
            header('Access-Control-Allow-Origin: *');
            $data = [
                "status" => "error",
                "timestamp" => time(),
                "data" => "Error. Post parameters are Missing"
            ];
            echo json_encode(
                $data
            );
        }
    } else {
        header("HTTP/1.1 400");
        header("Content-Type: application/json; charset=UTF-8");
        header('Access-Control-Allow-Origin: *');
        $data = [
            "status" => "error",
            "timestamp" => time(),
            "data" => "Error. Bad Request"
        ];
        echo json_encode(
            $data
        );
    }
    // * DONE
?>
