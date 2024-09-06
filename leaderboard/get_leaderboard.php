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

// Query to select all users who are not professors
$sql = "SELECT username, points, profile_picture FROM users WHERE is_professor = 0 ORDER BY points DESC";
$result = $conn->query($sql);

$users = array();

while($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);

$conn->close();

?>
