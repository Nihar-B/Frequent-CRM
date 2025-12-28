<?php
// PUBLIC ENDPOINT - NO AUTHENTICATION REQUIRED (Secure via API Key)
require_once 'db.php';
require_once 'assignment_engine.php'; // Include the brain

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow your website to talk to this

// 1. Simple Security (API Key)
// In your HTML form, add <input type="hidden" name="api_key" value="crm_secret_key_123">
$validKey = "crm_secret_key_123";
if (($_POST['api_key'] ?? '') !== $validKey) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        // 2. Run Assignment Rules
        // We pass the POST data to the engine to decide the owner
        $assignedTo = getAssignedUser($pdo, $_POST);

        // 3. Calculate Score (Simplified Lead Scoring)
        $score = 10;
        if (!empty($_POST['company'])) $score += 10;
        if (strpos($_POST['email'], '@gmail') === false) $score += 20; // B2B Email bonus

        // 4. Insert Customer
        $stmt = $pdo->prepare("INSERT INTO customers (first_name, last_name, email, state, company, status, source, potential_value, score, assigned_to) VALUES (?, ?, ?, ?, ?, 'Lead', 'Web Form', ?, ?, ?)");
        
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['state'] ?? '', // Capture State
            $_POST['company'],
            $_POST['potential_value'] ?? 0,
            $score,
            $assignedTo // The Magic ID from the Engine
        ]);

        // 5. Create Notification Task for the Owner
        $newId = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO tasks (title, description, due_date, status, assigned_to, related_to, related_id) VALUES (?, ?, CURDATE(), 'Pending', ?, 'customer', ?)")
            ->execute(["New Web Lead: " . $_POST['first_name'], "Please contact immediately.", $assignedTo, $newId]);

        echo json_encode(['status' => 'success', 'assigned_to' => $assignedTo]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>