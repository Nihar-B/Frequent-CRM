<?php
require_once 'functions.php';
require_once 'db.php';

start_secure_session();

$token = $_GET['token'] ?? '';
$token_hash = hash("sha256", $token);
$error_critical = '';

// Validate Token
$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()");
$stmt->execute([$token_hash]);
$user = $stmt->fetch();

if (!$user) {
    $error_critical = "INVALID_TOKEN: EXPIRED OR UNKNOWN.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error_critical) {
    verify_csrf_token($_POST['csrf_token']);
    
    $pass1 = $_POST['password'];
    $pass2 = $_POST['confirm_password'];

    if (strlen($pass1) < 6) {
        $error = "ERROR: MIN LENGTH 6 CHARS.";
    } elseif ($pass1 !== $pass2) {
        $error = "ERROR: PASSWORDS DO NOT MATCH.";
    } else {
        $new_hash = password_hash($pass1, PASSWORD_DEFAULT);
        
        // Update password and Clear token
        $update = $pdo->prepare("UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $update->execute([$new_hash, $user['id']]);

        $_SESSION['success'] = "PASSWORD UPDATED. ACCESS RESTORED.";
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sarder Solutions | Set Key</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* --- NEO-BRUTALIST THEME --- */
        :root {
            --bg-color: #ffffff;
            --text-main: #000000;
            --accent: #b084fc; /* Purple Pop */
            --success: #4ade80; 
            --danger: #ff4d4d; 
            --border-width: 2px;
            --radius: 8px;
            --shadow-hard: 4px 4px 0 #000000;
        }

        body { 
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 24px 24px;
            height: 100vh;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: var(--text-main);
        }

        h4 {
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: var(--border-width) solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
            letter-spacing: -0.03em;
        }

        .reset-card {
            background: #ffffff;
            padding: 40px;
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            box-shadow: 8px 8px 0 #000;
            width: 100%;
            max-width: 450px;
            position: relative;
        }

        /* --- ALERTS --- */
        .alert {
            border-radius: var(--radius);
            border: var(--border-width) solid #000;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: var(--shadow-hard);
            font-family: 'Space Mono', monospace;
        }
        .alert-danger { background: #fee2e2; color: #000; }

        /* --- FORM ELEMENTS --- */
        label {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            margin-bottom: 8px;
            display: block;
            letter-spacing: 0.05em;
        }

        .form-control {
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            padding: 12px 16px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            box-shadow: 3px 3px 0 #e5e7eb;
            transition: all 0.2s;
        }

        .form-control:focus {
            background: #ffffff;
            border-color: #000;
            box-shadow: var(--shadow-hard);
            color: #000;
            outline: none;
        }

        /* --- BUTTONS --- */
        .btn-neo {
            background: #000;
            color: #fff;
            border: var(--border-width) solid #000;
            width: 100%;
            padding: 14px;
            font-weight: 700;
            font-family: 'Space Mono', monospace;
            text-transform: uppercase;
            border-radius: var(--radius);
            box-shadow: 5px 5px 0 var(--accent);
            transition: 0.1s;
        }

        .btn-neo:hover {
            background: #222;
            color: #fff;
            box-shadow: 7px 7px 0 var(--accent);
            transform: translate(-2px, -2px);
        }

        .btn-neo:active {
            transform: translate(2px, 2px);
            box-shadow: 0 0 0 #000;
        }

        .back-link {
            text-decoration: none;
            color: #000;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
            border-bottom: 2px solid transparent;
            transition: 0.2s;
        }
        .back-link:hover { 
            border-bottom: 2px solid var(--accent);
        }

    </style>
</head>
<body>
    <div class="reset-card">
        <h4><i class="bi bi-shield-lock-fill me-2 text-primary" style="color: var(--accent) !important;"></i>New Key</h4>
        
        <?php if($error_critical): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-octagon-fill me-2"></i> <?php echo htmlspecialchars($error_critical); ?>
            </div>
            <div class="text-center mt-4">
                <a href="forgot_password.php" class="btn-neo" style="box-shadow: 5px 5px 0 #999; background: #fff; color: #000;">REQUEST NEW LINK</a>
            </div>
        <?php else: ?>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="mb-4">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Min 6 Chars">
                </div>
                
                <div class="mb-4">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat Password">
                </div>
                
                <button type="submit" class="btn-neo">Update Credentials</button>
            </form>
            
        <?php endif; ?>
    </div>
</body>
</html>