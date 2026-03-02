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
    // Get the note
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$note) {
        die(json_encode([
            'success' => false,
            'message' => 'Note not found'
        ]));
    }
    
    $description = $note['description'];
    
    // Groq API configuration - load from .env file
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
    
    $apiKey = getenv('GROQ_API_KEY');
    
    if (!$apiKey) {
        die(json_encode([
            'success' => false,
            'message' => 'Groq API key not configured. Please create .env file with GROQ_API_KEY.'
        ]));
    }
    
    $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    
    // Prepare the prompt for AI summarization
    $prompt = "Summarize the following text in a concise, clear manner. Provide a brief summary that captures the main points:\n\n" . $description;
    
    // Prepare the request body
    $postData = json_encode([
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'model' => 'meta-llama/llama-4-scout-17b-16e-instruct',
        'temperature' => 1,
        'max_completion_tokens' => 1024,
        'top_p' => 1,
        'stream' => false
    ]);
    
    // Make API request to Groq
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        die(json_encode([
            'success' => false,
            'message' => 'API request failed: ' . $error
        ]));
    }
    
    $result = json_decode($response, true);
    
    if ($httpCode !== 200) {
        die(json_encode([
            'success' => false,
            'message' => 'API returned error code: ' . $httpCode,
            'details' => $response
        ]));
    }
    
    // Check for summary in response
    if (!isset($result['choices'][0]['message']['content'])) {
        die(json_encode([
            'success' => false,
            'message' => 'Invalid response format from AI',
            'details' => $response
        ]));
    }
    
    $aiSummary = $result['choices'][0]['message']['content'];
    
    // Update the note with the generated summary
    $updateStmt = $pdo->prepare("
        UPDATE notes 
        SET summary = :summary 
        WHERE id = :id
    ");
    
    $updateStmt->execute([
        ':summary' => $aiSummary,
        ':id' => $id
    ]);
    
    die(json_encode([
        'success' => true,
        'summary' => $aiSummary,
        'message' => 'Summary generated successfully'
    ]));
} catch (PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]));
}
?>
