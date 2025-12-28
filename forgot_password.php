<?php
require_once 'functions.php';
require_once 'db.php';

start_secure_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token']);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        // 1. Generate Token
        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token);

        // 2. Save Token to DB (Expires in 1 hour)
        $update = $pdo->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
        $update->execute([$token_hash, $email]);

        // 3. GENERATE LINK (Auto-detects port 8080)
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST']; 
        $scriptPath = dirname($_SERVER['PHP_SELF']);
        
        $resetLink = "$protocol://$host$scriptPath/reset_password.php?token=" . $token;

        // 4. SHOW LINK ON SCREEN (Developer Mode)
        $_SESSION['dev_link'] = $resetLink;
        $_SESSION['success'] = "USER IDENTIFIED. TOKEN GENERATED.";
    } else {
        $_SESSION['error'] = "ERROR: USER_NOT_FOUND.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sarder Solutions | Recovery</title>
    
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
            /* Dot Pattern Background */
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

        .recovery-card {
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
        .alert-success { background: var(--success); color: #000; }
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

        /* Developer Link Button */
        .btn-dev-link {
            background: var(--success);
            color: #000;
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            font-weight: 700;
            font-family: 'Space Mono', monospace;
            text-transform: uppercase;
            box-shadow: var(--shadow-hard);
            text-decoration: none;
            display: block;
            padding: 12px;
            text-align: center;
            font-size: 0.9rem;
            transition: 0.1s;
        }
        .btn-dev-link:hover {
            background: #86efac;
            color: #000;
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 #000;
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
    <div class="recovery-card">
        <h4><i class="bi bi-key-fill me-2 text-primary" style="color: var(--accent) !important;"></i>Recovery</h4>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-square-fill me-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            
            <?php if(isset($_SESSION['dev_link'])): ?>
                <div class="mb-4">
                    <label class="text-success small mb-2">DEV_MODE: LINK GENERATED</label>
                    <a href="<?php echo $_SESSION['dev_link']; ?>" class="btn-dev-link">
                        <i class="bi bi-unlock-fill me-2"></i> Reset Password
                    </a>
                </div>
                <?php unset($_SESSION['dev_link']); ?>
            <?php endif; ?>

        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="mb-4">
                <label>Target Identifier (Email)</label>
                <input type="email" name="email" class="form-control" required placeholder="user@sarder.inc">
            </div>
            <button type="submit" class="btn btn-neo">Find User Account</button>
        </form>
        
        <div class="mt-4 text-center border-top border-2 border-dark pt-3">
            <a href="login.php" class="back-link">
                <i class="bi bi-arrow-return-left me-1"></i> Return to Login
            </a>
        </div>
    </div>
</body>
</html>