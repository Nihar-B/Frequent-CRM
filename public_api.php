<?php
// PUBLIC ENDPOINT - NO AUTHENTICATION REQUIRED (Secure via API Key)
require_once 'db.php';
require_once 'assignment_engine.php'; // Include the brain

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow your website to talk to this

// 1. Simple Security (API Key)
$validKey = "crm_secret_key_123";
if (($_POST['api_key'] ?? '') !== $validKey) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid API Key']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        $email = trim($_POST['email']);

        // --- STEP A: CHECK FOR DUPLICATES ---
        $checkStmt = $pdo->prepare("SELECT id, assigned_to, first_name FROM customers WHERE email = ?");
        $checkStmt->execute([$email]);
        $existingCustomer = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingCustomer) {
            // --- SCENARIO 1: EXISTING CUSTOMER ---
            // Do NOT insert. Just notify the existing owner.
            
            $customerId = $existingCustomer['id'];
            $assignedTo = $existingCustomer['assigned_to']; // Keep the original owner
            
            $taskTitle = "Re-Inquiry: " . $existingCustomer['first_name'];
            $taskDesc = "Existing customer submitted the form again. Potential upsell or new budget.";

        } else {
            // --- SCENARIO 2: NEW CUSTOMER ---
            
            // 2. Run Assignment Rules (Only for new people)
            $assignedTo = getAssignedUser($pdo, $_POST);

            // 3. Calculate Score
            $score = 10;
            if (!empty($_POST['company'])) $score += 10;
            if (strpos($email, '@gmail') === false) $score += 20;

            // 4. Insert Customer
            $stmt = $pdo->prepare("INSERT INTO customers (first_name, last_name, email, state, company, status, source, potential_value, score, assigned_to) VALUES (?, ?, ?, ?, ?, 'Lead', 'Web Form', ?, ?, ?)");
            
            $stmt->execute([
                $_POST['first_name'],
                $_POST['last_name'],
                $email,
                $_POST['state'] ?? '', 
                $_POST['company'],
                $_POST['potential_value'] ?? 0,
                $score,
                $assignedTo
            ]);

            $customerId = $pdo->lastInsertId();
            
            $taskTitle = "New Web Lead: " . $_POST['first_name'];
            $taskDesc = "Please contact immediately.";
        }

        // 5. Create Task (This happens for BOTH new and existing customers so no lead is missed)
        $pdo->prepare("INSERT INTO tasks (title, description, due_date, status, assigned_to, related_to, related_id) VALUES (?, ?, CURDATE(), 'Pending', ?, 'customer', ?)")
            ->execute([$taskTitle, $taskDesc, $assignedTo, $customerId]);

        echo json_encode(['status' => 'success', 'assigned_to' => $assignedTo]);

    } catch (Exception $e) {
        // Handle unique constraint violation gracefully if race condition occurs
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
             echo json_encode(['status' => 'success', 'message' => 'Already exists (handled)']);
        } else {
             echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
?>