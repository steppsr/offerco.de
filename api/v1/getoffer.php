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

                // Retrieve the short code from the POST data
                $short_code = isset($_POST['short_code']) ? sanitize_input($_POST['short_code']) : null;

                // Check if shortcode is provided
                if ($short_code === null) {
                    $response['status'] = "error";
                    $response['message'] = "Shortcode not provided";
                } else {
                
                    function getOfferCode($short_code) {
                        $conn = new mysqli(HOST, USER, PASS, DBNAME);
                        if ($conn->connect_error) {
                            error_log("Database Connection Failed: " . $conn->connect_error);
                            return [
                                "offer_code" => "",
                                "description" => "",
                                "offer_id" => ""
                            ];
                        }

                        $stmt = $conn->prepare("SELECT offer_code, description FROM offer_codes WHERE short_code = ?");
                        if ($stmt === false) {
                            error_log("Prepare failed: " . $conn->error);
                            $conn->close();
                            return [
                                "offer_code" => "",
                                "description" => "",
                                "offer_id" => ""
                            ];
                        }
                        $stmt->bind_param("s", $short_code);
                        $stmt->execute();

                        $result = $stmt->get_result();

                        $offer_code = "";
                        $description = "";
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $offer_code = $row["offer_code"];
                            $description = $row["description"];
                        }

                        $stmt->close();
                        $conn->close();

                        $offer_code_hash = hash('sha256', $offer_code, true);
                        $base58EncodedHash = base58_encode($offer_code_hash);

                        return [
                            "offer_code" => $offer_code,
                            "description" => $description,
                            "offer_id" => $base58EncodedHash
                        ];
                    }

                    $offer_details = getOfferCode($short_code);

                    $response['data'] = [
                        "short_code" => $short_code,
                        "offer_code" => $offer_details['offer_code'],
                        "description" => $offer_details['description'],
                        "offer_id" => $offer_details['offer_id']
                    ];
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