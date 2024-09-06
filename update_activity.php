<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "diploma_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'];
    $today = date('Y-m-d');

    // Check the user's last activity date
    $stmt = $conn->prepare("SELECT consecutive_days, last_activity FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($consecutive_days, $last_activity);
    $stmt->fetch();
    $stmt->close();

    if ($last_activity === null || $last_activity === '') {
        // First activity
        $consecutive_days = 1;
    } else {
        $last_activity_date = new DateTime($last_activity);
        $today_date = new DateTime($today);
        $interval = $last_activity_date->diff($today_date)->days;

        if ($interval == 1) {
            // Consecutive day
            $consecutive_days++;
        } else if ($interval > 1) {
            // Missed days, reset count
            $consecutive_days = 1;
        }
        // If interval == 0, no need to update consecutive_days
    }

    // Update the user's consecutive days and last activity date
    $stmt = $conn->prepare("UPDATE users SET consecutive_days = ?, last_activity = ? WHERE id = ?");
    $stmt->bind_param("isi", $consecutive_days, $today, $user_id);
    $stmt->execute();
    $stmt->close();

    // Log the activity
    $stmt = $conn->prepare("INSERT INTO user_activity (user_id, activity_date) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['status' => 'success', 'consecutive_days' => $consecutive_days]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

$conn->close();
?>
