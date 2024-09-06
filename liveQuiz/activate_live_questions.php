<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "diploma";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(array("status" => "error", "message" => "Connection failed: " . $conn->connect_error)));
}

try {
    // Deactivate all questions first
    $conn->query("UPDATE live_questions SET is_active = 0");

    // Activate all questions
    $conn->query("UPDATE live_questions SET is_active = 1");

    echo json_encode(array("status" => "success"));
} catch (Exception $e) {
    echo json_encode(array("status" => "error", "message" => $e->getMessage()));
}

$conn->close();
?>
