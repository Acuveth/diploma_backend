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

// Calculate the timestamp for 4 hours ago
$fourHoursAgo = date('Y-m-d H:i:s', strtotime('-4 hours'));

// Modify the SQL query to select only messages from the last 4 hours
$sql = "SELECT * FROM messages WHERE timestamp >= ? ORDER BY timestamp DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $fourHoursAgo);
$stmt->execute();
$result = $stmt->get_result();

$messages = array();

while($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);

$stmt->close();
$conn->close();
?>
