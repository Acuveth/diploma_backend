<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "diploma";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(array("status" => "error", "message" => "Connection failed: " . $conn->connect_error)));
}

$sql = "SELECT * FROM live_questions WHERE is_active = 1 LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $question = $result->fetch_assoc();
    echo json_encode($question);
} else {
    echo json_encode(array("status" => "no_active_question"));
}

$conn->close();
?>
