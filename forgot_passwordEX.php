<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'functions.php';
require_once 'db.php';

// --- LOAD PHPMAILER MANUALLY ---
require 'vendor/src/Exception.php';
require 'vendor/src/PHPMailer.php';
require 'vendor/src/SMTP.php';

start_secure_session();

// --- CONFIGURATION ---
define('RATE_LIMIT_SECONDS', 120); 

$message = "";
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token']);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (isset($_SESSION['last_reset_request']) && (time() - $_SESSION['last_reset_request'] < RATE_LIMIT_SECONDS)) {
        $remaining = RATE_LIMIT_SECONDS - (time() - $_SESSION['last_reset_request']);
        $message = "Please wait $remaining seconds before requesting another link.";
        $msg_type = "warning";
    } else {
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32)); 
            $token_hash = hash("sha256", $token);
            $expiry = date("Y-m-d H:i:s", time() + 3600); 

            $update = $pdo->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?");
            $update->execute([$token_hash, $expiry, $user['id']]);

            // Construct Link
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $path = dirname($_SERVER['PHP_SELF']);
            $link = "$protocol://$host$path/reset_password.php?token=$token";

            // --- SMTP SENDING LOGIC ---
            $mail = new PHPMailer(true);

            try {
                // 1. Server Settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'YOUR_GMAIL_ADDRESS@gmail.com'; // <--- CHANGE THIS
                $mail->Password   = 'YOUR_GMAIL_APP_PASSWORD';      // <--- CHANGE THIS
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // 2. Recipients
                $mail->setFrom('no-reply@frequent-crm.gt.tc', 'FrequentCRM Security');
                $mail->addAddress($email);

                // 3. Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "
                    <h3>Password Reset</h3>
                    <p>We received a request to reset your password for <b>FrequentCRM</b>.</p>
                    <p>Click the link below to set a new secure key:</p>
                    <p><a href='$link' style='background:#000; color:#fff; padding:10px 20px; text-decoration:none; font-weight:bold;'>RESET PASSWORD</a></p>
                    <p style='color:#666; font-size:12px;'>If you did not request this, please ignore this email.</p>
                ";

                $mail->send();
                
            } catch (Exception $e) {
                // Log error silently, don't show user
                error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        }

        $_SESSION['last_reset_request'] = time();
        
        // Always show success message for security
        $message = "If an account exists for <b>$email</b>, we have sent instructions.";
        $msg_type = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sarder Solutions | Recovery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --bg-color: #f3f4f6; --accent: #000; }
        body { background: var(--bg-color); height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
        .card-neo { background: #fff; border: 2px solid #000; box-shadow: 6px 6px 0 #000; padding: 40px; max-width: 450px; width: 100%; border-radius: 0; }
        h4 { font-family: 'Space Mono', monospace; font-weight: 700; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 25px; }
        .btn-neo { background: #000; color: #fff; border: 2px solid #000; width: 100%; padding: 12px; font-weight: 700; font-family: 'Space Mono'; text-transform: uppercase; transition: 0.1s; }
        .btn-neo:hover { transform: translate(-2px, -2px); box-shadow: 4px 4px 0 #000; color: #fff; }
        .form-control { border: 2px solid #000; border-radius: 0; padding: 12px; }
        .form-control:focus { box-shadow: 4px 4px 0 #000; border-color: #000; outline: none; }
        .alert-neo { border: 2px solid #000; background: #dcfce7; color: #166534; font-weight: 600; border-radius: 0; }
        .alert-warning-neo { border: 2px solid #000; background: #fef3c7; color: #000; }
    </style>
</head>
<body>
    <div class="card-neo">
        <h4><i class="bi bi-shield-lock-fill me-2"></i>RECOVERY PROTOCOL</h4>

        <?php if ($message): ?>
            <div class="alert <?php echo $msg_type == 'success' ? 'alert-neo' : 'alert-warning-neo'; ?> mb-4">
                <i class="bi <?php echo $msg_type == 'success' ? 'bi-check-circle-fill' : 'bi-hourglass-split'; ?> me-2"></i> 
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="mb-4">
                <label class="fw-bold text-uppercase small mb-1">Corporate Email</label>
                <input type="email" name="email" class="form-control" required placeholder="user@sarder.inc">
            </div>
            <button type="submit" class="btn btn-neo">Initiate Reset Sequence</button>
        </form>

        <div class="mt-4 text-center">
            <a href="login.php" class="text-dark text-decoration-none fw-bold small text-uppercase hover-underline">
                <i class="bi bi-arrow-left me-1"></i> Return to Login
            </a>
        </div>
    </div>
</body>
</html>