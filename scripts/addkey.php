<?php
include "../../dbconfig.php";

if ($argc != 2) {
    echo "Usage: php add_api_key.php <user_id>\n";
    exit(1);
}

$userId = $argv[1];

$conn = new mysqli(HOST, USER, PASS, DBNAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate a new unique API key
do {
    $newApiKey = bin2hex(random_bytes(16)); // Example: 32 character hex string
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM api_keys WHERE api_key = ?");
    $checkStmt->bind_param("s", $newApiKey);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();
} while ($count > 0);

$active = 1;
$expiresAt = null; // Set this if you want the key to expire, e.g., '2025-01-01 00:00:00'

$insertStmt = $conn->prepare("INSERT INTO api_keys (user_id, api_key) VALUES (?, ?)");

$insertStmt->bind_param("ss", $userId, $newApiKey);
if ($insertStmt->execute()) {
    echo "New API key added: " . $newApiKey . "\n";
} else {
    echo "Error adding new API key: " . $conn->error . "\n";
}

$insertStmt->close();
$conn->close();
?>
