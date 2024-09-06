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

$user_id = $_POST['user_id'];
$message = $_POST['message'];

$sql = "INSERT INTO messages (user_id, message) VALUES ('$user_id', '$message')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(array('status' => 'success'));
} else {
    echo json_encode(array('status' => 'error', 'message' => $conn->error));
}

$conn->close();
?>
