<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "diploma";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(array("status" => "error", "message" => "Connection failed: " . $conn->connect_error)));
}

// Retrieve and sanitize input parameters
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;

// Validate input
if ($user_id === null) {
    echo json_encode(array("status" => "error", "message" => "Invalid input"));
    exit();
}

error_log("update_lecture_progress.php - Received: user_id=$user_id");

// Update the lecture_progress and add 100 points in the users table
$sql = "UPDATE users SET lecture_progress = lecture_progress + 1, points = COALESCE(points, 0) + 100 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(array("status" => "success", "message" => "Lecture progress and points updated successfully."));
} else {
    error_log("SQL Error: " . $stmt->error); // Log the exact error
    echo json_encode(array("status" => "error", "message" => "Failed to update lecture progress and points."));
}

$stmt->close();
$conn->close();
?>
