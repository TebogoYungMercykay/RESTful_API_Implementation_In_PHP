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
        // Now Executing things!
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
