<?php
require_once 'functions.php';
require_once 'db.php';

start_secure_session();

$token = $_GET['token'] ?? '';
$token_hash = hash("sha256", $token);
$valid_request = false;
$error = '';

// 1. Validate Token
$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()");
$stmt->execute([$token_hash]);
$user = $stmt->fetch();

if ($user) {
    $valid_request = true;
} else {
    $error = "CRITICAL: This link is invalid or has expired.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_request) {
    verify_csrf_token($_POST['csrf_token']);
    
    $pass = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // --- UPDATED LOGIC START ---
    
    // Check 1: Do they match?
    if ($pass !== $confirm) {
        $error = "Error: Passwords do not match.";
    } 
    // Check 2: Simple length check only (Weak passwords allowed)
    elseif (strlen($pass) < 6) {
        $error = "Error: Password must be at least 6 characters.";
    } 
    else {
        // 3. Success - Update DB
        $new_hash = password_hash($pass, PASSWORD_DEFAULT);
        
        $update = $pdo->prepare("UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $update->execute([$new_hash, $user['id']]);

        $_SESSION['success_msg'] = "Credentials updated successfully.";
        redirect('login.php');
    }
    // --- UPDATED LOGIC END ---
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sarder Solutions | New Credentials</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --bg-color: #f3f4f6; }
        body { background: var(--bg-color); height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
        .card-neo { background: #fff; border: 2px solid #000; box-shadow: 6px 6px 0 #000; padding: 40px; max-width: 450px; width: 100%; border-radius: 0; }
        h4 { font-family: 'Space Mono', monospace; font-weight: 700; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 25px; }
        .btn-neo { background: #4ade80; color: #000; border: 2px solid #000; width: 100%; padding: 12px; font-weight: 700; font-family: 'Space Mono'; text-transform: uppercase; transition: 0.1s; }
        .btn-neo:hover { transform: translate(-2px, -2px); box-shadow: 4px 4px 0 #000; background: #22c55e; }
        .form-control { border: 2px solid #000; border-radius: 0; padding: 12px; }
        .form-control:focus { box-shadow: 4px 4px 0 #000; border-color: #000; outline: none; }
        .alert-error { border: 2px solid #000; background: #fee2e2; color: #000; font-weight: 600; border-radius: 0; }
        .password-reqs { font-size: 0.75rem; color: #666; margin-top: 5px; list-style: none; padding-left: 0; }
        .password-reqs li::before { content: "• "; color: #000; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card-neo">
        <h4><i class="bi bi-key-fill me-2"></i>SET NEW KEY</h4>

        <?php if (!$valid_request): ?>
            <div class="alert alert-error text-center">
                <i class="bi bi-x-octagon-fill d-block fs-1 mb-2"></i>
                <?php echo $error; ?>
            </div>
            <div class="text-center mt-3">
                <a href="forgot_password.php" class="btn btn-outline-dark fw-bold border-2 text-uppercase">Request New Link</a>
            </div>
        <?php else: ?>

            <?php if ($error): ?>
                <div class="alert alert-error mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="mb-3">
                    <label class="fw-bold text-uppercase small mb-1">New Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="form-text text-muted small">Minimum 6 characters. No complexity required.</div>
                </div>

                <div class="mb-4">
                    <label class="fw-bold text-uppercase small mb-1">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-neo">Update Credentials</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>