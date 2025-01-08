<?php
header("Content-Type: application/json");

include "../../dbconfig.php";
include "../../library.php";

// Check for API key
$api_key = isset($_POST['api_key']) ? sanitize_input($_POST['api_key']) : null;
$response = [
    "status" => "success", // Default to success
    "data" => []
];

if ($api_key === null) {
    $response['status'] = "error";
    $response['message'] = "API key not provided";
} else {
    $conn = new mysqli(HOST, USER, PASS, DBNAME);
    if ($conn->connect_error) {
        error_log("Database Connection Failed: " . $conn->connect_error);
        $response['status'] = "error";
        $response['message'] = "Service unavailable";
    } else {
        $stmt = $conn->prepare("SELECT active, expires_at FROM api_keys WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $result = $stmt->get_result();
        $key_info = $result->fetch_assoc();

        if ($key_info && $key_info['active']) {
            // Check if the key has expired
            if ($key_info['expires_at'] === null || strtotime($key_info['expires_at']) > time()) {
                // Proceed with the original logic of the endpoint here

                $offer_code = "";
                $short_code = "";
                $description = "";
                $offer_id = "";

                // Retrieve the offer code and description from the POST data
                $offer_code = isset($_POST['offer_code']) ? sanitize_input($_POST['offer_code']) : null;
                $description = isset($_POST['description']) ? sanitize_input($_POST['description']) : null;

                // Check if offer code is provided
                if ($offer_code === null) {
                    $response['status'] = "error";
                    $response['message'] = "Offer code not provided";
                } else {
                    function getShortCode($offer_code, $description) {
                        $conn = new mysqli(HOST, USER, PASS, DBNAME);
                        if ($conn->connect_error) {
                            error_log("Database Connection Failed: " . $conn->connect_error);
                            return [
                                "short_code" => "",
                                "offer_code" => "",
                                "description" => "",
                                "offer_id" => ""
                            ];
                        }

                        do {
                            $unique = true;
                            $short_code = generateRandomString(); // Generate a random string
                        
                            // SQL query to check if the string exists in the database
                            $query = "SELECT COUNT(*) FROM offer_codes WHERE short_code = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("s", $short_code); // 's' specifies the variable type => 'string'
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                        
                            if ($row['COUNT(*)'] > 0) {
                                $unique = false; // String exists, set $unique to false
                            }
                        } while (!$unique);
                    
                        $stmt = $conn->prepare("INSERT INTO offer_codes (offer_code, description, short_code) VALUES (?, ?, ?)");

                        if ($stmt === false) {
                            error_log("Prepare failed: " . $conn->error);
                            $conn->close();
                            return [
                                "short_code" => "",
                                "offer_code" => "",
                                "description" => "",
                                "offer_id" => ""
                            ];
                        }

                        $stmt->bind_param("sss", $offer_code, $description, $short_code);
                        $stmt->execute();
                        $stmt->close();
                        $conn->close();

                        $offer_code_hash = hash('sha256', $offer_code, true);
                        $base58EncodedHash = base58_encode($offer_code_hash);

                        return [
                            "short_code" => $short_code,
                            "offer_code" => $offer_code,
                            "description" => $description, // Now we include the description from the request
                            "offer_id" => $base58EncodedHash
                        ];
                    }

                    $offer_details = getShortCode($offer_code, $description);

                    $response['data'] = $offer_details;
                }
            } else {
                $response['status'] = "error";
                $response['message'] = "API key has expired";
            }
        } else {
            $response['status'] = "error";
            $response['message'] = "Invalid or inactive API key";
        }
        $stmt->close();
        $conn->close();
    }
}

echo json_encode($response);
?>