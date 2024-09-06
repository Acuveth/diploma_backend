<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "diploma";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_POST['user_id'];
    $profilePicture = $_POST['profile_picture'];

    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $profilePicture, $userID);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Profile picture updated."]);
    } else {
        // Log the error message
        error_log("Error updating profile picture: " . $stmt->error);
        echo json_encode(["status" => "error", "message" => "Failed to update profile picture."]);
    }
    $stmt->close();
}

$conn->close();
?>
