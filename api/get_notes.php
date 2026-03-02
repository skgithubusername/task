<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once 'config.php';

try {
    $stmt = $pdo->query("
        SELECT id, title, description, summary, created_at 
        FROM notes 
        ORDER BY created_at DESC
    ");
    
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    die(json_encode([
        'success' => true,
        'notes' => $notes
    ]));
} catch (PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => 'Failed to fetch notes: ' . $e->getMessage()
    ]));
}
?>
