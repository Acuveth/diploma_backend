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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'];
    $password = $data['password'];

    // Prepare and execute the statement to fetch user data
    $stmt = $conn->prepare("SELECT id, password, last_login, fires FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hashedPassword, $lastLogin, $fires);
    $stmt->fetch();
    $stmt->close();

    // Verify the password
    if ($hashedPassword && password_verify($password, $hashedPassword)) {
        $currentDate = new DateTime();
        $lastLoginDate = new DateTime($lastLogin);
        $interval = $lastLoginDate->diff($currentDate);

        // Check if login is on a different day
        if ($interval->days == 1) {
            $fires += 1; // User logged in on the consecutive day
        } elseif ($interval->days > 1) {
            $fires = 1; // Reset fires because a day was missed, start counting again
        } else {
            // Same day login, fires do not change
        }

        // Update the user's last login time and fires count in the database
        $stmt = $conn->prepare("UPDATE users SET last_login = ?, fires = ? WHERE id = ?");
        $lastLoginFormatted = $currentDate->format('Y-m-d H:i:s');
        $stmt->bind_param("sii", $lastLoginFormatted, $fires, $id);
        $stmt->execute();
        $stmt->close();

        // Prepare the response
        $response = [
            'status' => 'success',
            'user_id' => $id,
            'fires' => $fires
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Invalid username or password'
        ];
    }

    // Return the response as JSON
    echo json_encode($response);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

// Close the database connection
$conn->close();
?>
