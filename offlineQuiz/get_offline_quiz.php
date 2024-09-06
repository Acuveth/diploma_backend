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

$lecture_id = isset($_GET['lecture_id']) ? intval($_GET['lecture_id']) : null;

// Validate input
if ($lecture_id === null) {
    echo json_encode(array("status" => "error", "message" => "Invalid input"));
    exit();
}

$sql = "SELECT id, question_text, question_type, option_a, option_b, option_c, option_d, correct_option, correct_answer FROM questions WHERE lecture_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lecture_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = array();
while ($row = $result->fetch_assoc()) {
    // For multiple-choice questions, include options and correct_option
    if ($row['question_type'] === 'multiple_choice') {
        $questions[] = array(
            'id' => $row['id'],
            'question_text' => $row['question_text'],
            'question_type' => $row['question_type'],
            'options' => array(
                'a' => $row['option_a'],
                'b' => $row['option_b'],
                'c' => $row['option_c'],
                'd' => $row['option_d']
            ),
            'correct_option' => $row['correct_option']
        );
    } 
    // For text input questions, include the correct answer
    else if ($row['question_type'] === 'text_input') {
        $questions[] = array(
            'id' => $row['id'],
            'question_text' => $row['question_text'],
            'question_type' => $row['question_type'],
            'correct_answer' => $row['correct_answer']
        );
    }
}

echo json_encode($questions);

$stmt->close();
$conn->close();
?>
