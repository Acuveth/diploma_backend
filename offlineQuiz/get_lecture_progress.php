<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "diploma";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// Validate input
if ($user_id === null) {
    echo json_encode(array("status" => "error", "message" => "Invalid input"));
    exit();
}

error_log("get_lecture_progress.php - Received: user_id=$user_id");

$sql = "SELECT lecture_progress FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $progress = array("lecture_progress" => intval($row['lecture_progress']));
} else {
    $progress = array("lecture_progress" => 1);  // Default to lecture 1 if not found
}

echo json_encode($progress);

$stmt->close();
$conn->close();
?>
