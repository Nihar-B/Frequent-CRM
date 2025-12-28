<?php
// Salesforce-style Assignment Rules Engine

function getAssignedUser($pdo, $data) {
    // 1. EXTRACT DATA
    $state = strtoupper(trim($data['state'] ?? ''));
    $value = floatval($data['potential_value'] ?? 0);

    // --- RULE 1: GEOGRAPHIC ASSIGNMENT ---
    // If State is NY, assign to "User A" (Let's assume User ID 2 is the NY specialist)
    if ($state === 'NY') {
        return 2; // Hardcoded ID for User A (Update this to match your DB)
    }

    // --- RULE 2: HIGH VALUE / ENTERPRISE ---
    // If Company Size/Value > 500 (or $50,000), assign to Manager (ID 1)
    if ($value > 50000) {
        return 1; // ID for Admin/Manager
    }

    // --- RULE 3: ROUND ROBIN (FALLBACK) ---
    // If no specific rules match, rotate among Sales Reps
    return getRoundRobinUser($pdo);
}

function getRoundRobinUser($pdo) {
    // 1. Get all eligible Sales Reps
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'sales_rep' AND deleted_at IS NULL ORDER BY id ASC");
    $reps = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($reps)) return 1; // Fallback to Admin if no reps exist

    // 2. Find who got the LAST lead
    // We look at the most recent customer created to see who owns it
    $lastLead = $pdo->query("SELECT assigned_to FROM customers WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 1")->fetchColumn();

    // 3. Determine Next Owner
    if (!$lastLead || !in_array($lastLead, $reps)) {
        // If first run, or last owner wasn't a rep, pick the first one
        return $reps[0];
    }

    // Find index of last owner
    $key = array_search($lastLead, $reps);
    
    // If last owner was the last person in the list, loop back to start
    if ($key === count($reps) - 1) {
        return $reps[0];
    } else {
        // Otherwise, give it to the next person
        return $reps[$key + 1];
    }
}
?>