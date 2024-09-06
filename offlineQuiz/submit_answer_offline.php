<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "diploma";

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(array("status" => "error", "message" => "Database connection failed: " . $conn->connect_error));
    exit();
}

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
$question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : null;
$selected_option = isset($_POST['selected_option']) ? $_POST['selected_option'] : null;
$text_answer = isset($_POST['text_answer']) ? trim($_POST['text_answer']) : null;

// Validate input
if ($user_id === null || $question_id === null || ($selected_option === null && $text_answer === null)) {
    echo json_encode(array("status" => "error", "message" => "Invalid input"));
    exit();
}

// Get the question details
$sql = "SELECT question_type, correct_option, correct_answer FROM questions WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(array("status" => "error", "message" => "Prepare failed: " . $conn->error));
    exit();
}
$stmt->bind_param("i", $question_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(array("status" => "error", "message" => "Question not found"));
    exit();
}

$row = $result->fetch_assoc();
$question_type = $row['question_type'];
$is_correct = 0;

// Handle multiple-choice questions
if ($question_type === 'multiple_choice') {
    if ($selected_option === null) {
        echo json_encode(array("status" => "error", "message" => "No option selected for a multiple-choice question"));
        exit();
    }

    $correct_option = $row['correct_option'];
    $is_correct = (strcasecmp($correct_option, $selected_option) === 0) ? 1 : 0;
}

// Handle text input questions
else if ($question_type === 'text_input') {
    if ($text_answer === null) {
        echo json_encode(array("status" => "error", "message" => "No answer provided for a text input question"));
        exit();
    }

    $correct_answer = strtolower(trim($row['correct_answer']));
    $provided_answer = strtolower($text_answer);

    $is_correct = ($correct_answer === $provided_answer) ? 1 : 0;
}

// Store the answer - separate queries for multiple-choice and text input
if ($question_type === 'multiple_choice') {
    $sql = "INSERT INTO user_answers (user_id, question_id, selected_option, text_answer, is_correct) 
            VALUES (?, ?, ?, NULL, ?) 
            ON DUPLICATE KEY UPDATE selected_option = ?, text_answer = NULL, is_correct = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(array("status" => "error", "message" => "Prepare failed: " . $conn->error));
        exit();
    }
    $stmt->bind_param("iisiis", $user_id, $question_id, $selected_option, $is_correct, $selected_option, $is_correct);

} else if ($question_type === 'text_input') {
    $sql = "INSERT INTO user_answers (user_id, question_id, selected_option, text_answer, is_correct) 
            VALUES (?, ?, NULL, ?, ?) 
            ON DUPLICATE KEY UPDATE selected_option = NULL, text_answer = ?, is_correct = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(array("status" => "error", "message" => "Prepare failed: " . $conn->error));
        exit();
    }
    $stmt->bind_param("iissis", $user_id, $question_id, $text_answer, $is_correct, $text_answer, $is_correct);
}

if ($stmt->execute()) {
    echo json_encode(array("status" => "success", "is_correct" => $is_correct));
} else {
    echo json_encode(array("status" => "error", "message" => "Failed to store answer: " . $stmt->error));
}

$stmt->close();
$conn->close();
?>
