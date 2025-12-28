<?php

require_once 'functions.php';
require_once 'db.php';

start_secure_session();

// Handle Login Logic directly here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token']);
    
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                $error = "ACCOUNT STATUS: INACTIVE.";
            } else {
                // Regenerate session ID to prevent Session Fixation attacks
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                
                redirect('index.php');
            }
        } else {
            $error = "ACCESS DENIED: INVALID CREDENTIALS.";
        }
    } catch (Exception $e) {
        $error = "SYSTEM ERROR: DATABASE UNREACHABLE.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sarder Solutions | Login</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* --- NEO-BRUTALIST THEME VARIABLES --- */
        :root {
            --bg-color: #ffffff;
            --text-main: #000000;
            --accent: #b084fc; /* The Purple Pop */
            --danger: #ff4d4d;
            --success: #4ade80;
            --border-width: 2px;
            --shadow-offset: 4px;
            --radius: 8px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            /* Changed from fixed height to min-height for better mobile scrolling */
            min-height: 100vh; 
            display: flex;
            align-items: center;
            justify-content: center;
            /* Added padding to prevent card from touching edges on small screens */
            padding: 20px; 
            
            /* Dot Pattern Background */
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 20px 20px;
        }

        h1, h2, h3, h4, h5, h6, .brand-font {
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        /* --- LOGIN CARD --- */
        .login-card {
            background: #ffffff;
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 8px 8px 0 #000; /* Signature Hard Shadow */
            position: relative;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: var(--border-width) solid #000;
        }

        .brand-header i {
            font-size: 2.5rem;
            color: var(--accent);
            filter: drop-shadow(2px 2px 0 #000);
        }

        /* --- FORMS --- */
        label {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            background: #fff;
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            padding: 12px 16px;
            font-size: 1rem;
            font-weight: 500;
            color: #000;
            transition: all 0.2s;
            box-shadow: 3px 3px 0 transparent;
        }

        .form-control:focus {
            background: #fff;
            box-shadow: 4px 4px 0 #000;
            border-color: #000;
            transform: translate(-2px, -2px);
            outline: none;
        }

        /* --- BUTTONS --- */
        .btn-neo {
            background: #000;
            color: #fff;
            border: var(--border-width) solid #000;
            padding: 14px;
            width: 100%;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: var(--radius);
            box-shadow: 5px 5px 0 var(--accent);
            transition: all 0.1s;
        }

        .btn-neo:hover {
            transform: translate(-2px, -2px);
            box-shadow: 7px 7px 0 var(--accent);
            background: #222;
            color: #fff;
        }

        .btn-neo:active {
            transform: translate(2px, 2px);
            box-shadow: 0 0 0 #000;
        }

        /* --- ALERTS --- */
        .alert {
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 4px 4px 0 #000;
            padding: 12px;
            margin-bottom: 24px;
        }
        .alert-danger { background: #fee2e2; color: #000; }
        .alert-success { background: #dcfce7; color: #000; }

        /* --- LINKS & EXTRAS --- */
        .forgot-link {
            color: #000;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: underline;
            text-decoration-thickness: 2px;
        }
        .forgot-link:hover {
            background: var(--accent);
            text-decoration: none;
        }

        .form-check-input {
            border: 2px solid #000;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-check-input:checked {
            background-color: var(--accent);
            border-color: #000;
        }

        /* --- RESPONSIVE ADJUSTMENTS --- */
        @media (max-width: 576px) {
            .login-card {
                padding: 25px; /* Reduce internal padding */
                box-shadow: 6px 6px 0 #000; /* Slightly smaller shadow */
            }
            
            .brand-header {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }

            .brand-header i {
                font-size: 2rem;
            }

            h4.brand-font {
                font-size: 1.25rem;
            }

            .btn-neo {
                padding: 12px; /* Slightly smaller button for mobile */
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-header">
            <i class="bi bi-box-seam-fill mb-2 d-block"></i>
            <h4 class="brand-font m-0">FrequentCRM</h4>
            <small class="text-muted fw-bold">INTERNAL CONSOLE</small>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i> 
                <div><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="mb-4">
                <label>Operator ID</label>
                <input type="email" name="email" class="form-control" required placeholder="user@sarder.inc">
            </div>
            
            <div class="mb-4">
                <label>Access Key</label>
                <input type="password" name="password" class="form-control" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="showPass" onclick="togglePass()">
                    <label class="form-check-label small fw-bold" for="showPass" style="font-size: 0.75rem; padding-top: 2px;">
                        SHOW KEY
                    </label>
                </div>
                <a href="forgot_password.php" class="forgot-link">Lost Key?</a>
            </div>

            <button type="submit" class="btn btn-neo">
                Initialize Session
            </button>
        </form>
    </div>

    <script>
        function togglePass() {
            var x = document.querySelector('input[name="password"]');
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }
    </script>
</body>
</html>