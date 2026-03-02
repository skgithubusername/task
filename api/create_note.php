<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    die(json_encode([
        'success' => false,
        'message' => 'Invalid input'
    ]));
}

$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$summary = trim($input['summary'] ?? '');

if (empty($title)) {
    die(json_encode([
        'success' => false,
        'message' => 'Title is required'
    ]));
}

if (empty($description)) {
    die(json_encode([
        'success' => false,
        'message' => 'Description is required'
    ]));
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO notes (title, description, summary) 
        VALUES (:title, :description, :summary)
    ");
    
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':summary' => $summary
    ]);
    
    die(json_encode([
        'success' => true,
        'message' => 'Note created successfully',
        'note_id' => $pdo->lastInsertId()
    ]));
} catch (PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => 'Failed to create note: ' . $e->getMessage()
    ]));
}
?>
