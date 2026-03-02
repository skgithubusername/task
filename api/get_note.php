<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die(json_encode([
        'success' => false,
        'message' => 'Invalid note ID'
    ]));
}

try {
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$note) {
        die(json_encode([
            'success' => false,
            'message' => 'Note not found'
        ]));
    }
    
    die(json_encode([
        'success' => true,
        'note' => $note
    ]));
} catch (PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => 'Failed to fetch note: ' . $e->getMessage()
    ]));
}
?>
