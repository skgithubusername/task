<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');

require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die(json_encode([
        'success' => false,
        'message' => 'Invalid note ID'
    ]));
}

try {
    $stmt = $pdo->prepare("DELETE FROM notes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() > 0) {
        die(json_encode([
            'success' => true,
            'message' => 'Note deleted successfully'
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
        'message' => 'Failed to delete note: ' . $e->getMessage()
    ]));
}
?>
