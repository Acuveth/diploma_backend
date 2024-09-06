<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "diploma";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(array("status" => "error", "message" => "Connection failed: " . $conn->connect_error)));
}

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
$question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : null;
$selected_option = isset($_POST['selected_option']) ? $_POST['selected_option'] : null;
$activation_time = isset($_POST['activation_time']) ? $_POST['activation_time'] : null;
$submit_time = isset($_POST['submit_time']) ? $_POST['submit_time'] : null;

if ($user_id === null || $question_id === null || $selected_option === null || $activation_time === null || $submit_time === null) {
    die(json_encode(array("status" => "error", "message" => "Invalid input")));
}

// Convert activation and submit times to DateTime objects
$activation_time = new DateTime($activation_time);
$submit_time = new DateTime($submit_time);
$interval = $activation_time->diff($submit_time);
$seconds_taken = $interval->s + ($interval->i * 60);

// Check if the selected option is correct
$sql = "SELECT correct_option FROM live_questions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $correct_option = $row['correct_option'];

    $is_correct = (strcasecmp($correct_option, $selected_option) == 0) ? 1 : 0;

    // Calculate points based on time taken to answer
    $points = 10; // Base points
    if ($seconds_taken <= 2) {
        $points = 10; // Full points for answering within 2 seconds
    } elseif ($seconds_taken <= 9) {
        $points = max(3, 10 - ($seconds_taken - 2)); // Decrease points per second after 2 seconds
    } else {
        $points = 3; // Minimum points
    }

    // Store the user's answer in live_answers table with the submission time and points
    $sql = "INSERT INTO live_answers (user_id, question_id, selected_option, is_correct, submit_time, points) 
            VALUES (?, ?, ?, ?, NOW(), ?) 
            ON DUPLICATE KEY UPDATE selected_option = ?, is_correct = ?, submit_time = NOW(), points = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisisiis", $user_id, $question_id, $selected_option, $is_correct, $points, $selected_option, $is_correct, $points);

    if ($stmt->execute()) {
        if ($is_correct) {
            // Update the user's total points
            $stmt = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
            $stmt->bind_param("ii", $points, $user_id);
            $stmt->execute();
        }

        // Deactivate the current question
        $stmt = $conn->prepare("UPDATE live_questions SET is_active = 0 WHERE id = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();

        // Activate the next question if available
        $stmt = $conn->prepare("UPDATE live_questions SET is_active = 1 WHERE id = (
                                    SELECT id FROM live_questions WHERE id > ? ORDER BY id ASC LIMIT 1)");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();

        $stmt->store_result();  // Store the result to get the number of affected rows

        if ($stmt->affected_rows > 0) {
            echo json_encode(array("status" => "success", "is_correct" => $is_correct, "points" => $points));
        } else {
            echo json_encode(array("status" => "success", "is_correct" => $is_correct, "points" => $points, "message" => "No more questions"));
        }
    } else {
        echo json_encode(array("status" => "error", "message" => "Failed to store answer"));
    }
} else {
    echo json_encode(array("status" => "error", "message" => "Question not found"));
}

$stmt->close();
$conn->close();
?>
