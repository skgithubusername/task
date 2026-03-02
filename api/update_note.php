<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die(json_encode([
        'success' => false,
        'message' => 'Invalid note ID'
    ]));
}

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
        UPDATE notes 
        SET title = :title, description = :description, summary = :summary
        WHERE id = :id
    ");
    
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':summary' => $summary,
        ':id' => $id
    ]);
    
    if ($stmt->rowCount() > 0) {
        die(json_encode([
            'success' => true,
            'message' => 'Note updated successfully'
        ]));
    } else {
        die(json_encode([
            'success' => false,
            'message' => 'Note not found'
        ]));
    }
} catch (PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => 'Failed to update note: ' . $e->getMessage()
    ]));
}
?>
