<?php
require_once 'functions.php';
require_once 'db.php';
start_secure_session();

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// 1. Fetch Current User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token']);

    // A. Update Info
    if (isset($_POST['update_info'])) {
        $name = $_POST['full_name'];
        $email = $_POST['email'];
        
        $update = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        if ($update->execute([$name, $email, $user_id])) {
            $_SESSION['user_name'] = $name; // Update session
            $message = "PROFILE DATA UPDATED SUCCESSFULLY.";
            // Refresh data
            $user['full_name'] = $name;
            $user['email'] = $email;
        } else {
            $error = "ERROR: UNABLE TO COMMIT CHANGES.";
        }
    }

    // B. Change Password
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if (!password_verify($current, $user['password'])) {
            $error = "AUTH_FAIL: CURRENT PASSWORD INCORRECT.";
        } elseif ($new !== $confirm) {
            $error = "ERROR: PASSWORDS DO NOT MATCH.";
        } elseif (strlen($new) < 6) {
            $error = "ERROR: MINIMUM LENGTH 6 CHARS REQUIRED.";
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$hash, $user_id]);
            $message = "SECURITY UPDATE: PASSWORD CHANGED.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Config | Sarder Solutions</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* --- NEO-BRUTALIST THEME --- */
        :root {
            --bg-body: #ffffff;
            --text-main: #000000;
            --accent: #b084fc; /* Purple Pop */
            --danger: #ff4d4d;
            --success: #4ade80;
            --border-width: 2px;
            --radius: 8px;
            --shadow-hard: 4px 4px 0 #000000;
            --shadow-hover: 6px 6px 0 #000000;
        }

        body { 
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 24px 24px;
            color: var(--text-main);
            min-height: 100vh;
        }

        /* --- CARDS --- */
        .card-neo {
            background: #ffffff;
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            box-shadow: var(--shadow-hard);
            height: 100%;
            overflow: hidden;
        }

        .card-header-neo {
            background: var(--accent);
            border-bottom: var(--border-width) solid #000;
            padding: 16px 24px;
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: -0.02em;
            font-size: 1rem;
        }

        .card-body { padding: 24px; }

        /* --- FORMS --- */
        label {
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            margin-bottom: 8px;
            display: block;
            color: #555;
        }

        .form-control {
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            padding: 12px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            box-shadow: 3px 3px 0 #e5e7eb;
            color: var(--text-main);
            transition: all 0.2s;
        }

        .form-control:focus {
            background: #ffffff;
            border-color: #000;
            box-shadow: 4px 4px 0 #000;
            outline: none;
        }

        .form-control[readonly] {
            background-color: #f3f4f6;
            color: #6b7280;
            box-shadow: none;
            border-color: #d1d5db;
        }

        /* --- BUTTONS --- */
        .btn-neo {
            background: #000;
            color: #fff;
            border: var(--border-width) solid #000;
            border-radius: var(--radius);
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            padding: 14px 20px;
            box-shadow: 4px 4px 0 var(--accent);
            transition: all 0.1s;
            width: 100%;
        }

        .btn-neo:hover {
            background: #222;
            color: #fff;
            box-shadow: 6px 6px 0 var(--accent);
            transform: translate(-2px, -2px);
        }

        .btn-neo:active {
            transform: translate(2px, 2px);
            box-shadow: 0 0 0 #000;
        }

        .btn-back {
            background: #fff;
            color: #000;
            border: var(--border-width) solid #000;
            font-weight: 600;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: var(--radius);
            box-shadow: 3px 3px 0 #000;
            display: inline-block;
            transition: 0.1s;
            font-size: 0.9rem;
        }
        .btn-back:hover {
            background: var(--accent);
            color: #000;
            transform: translate(-2px, -2px);
            box-shadow: 5px 5px 0 #000;
        }

        /* --- ALERTS --- */
        .alert {
            border-radius: var(--radius);
            border: var(--border-width) solid #000;
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            box-shadow: 4px 4px 0 #000;
        }
        .alert-success { background: var(--success); color: #000; }
        .alert-danger { background: var(--danger); color: #fff; border-color: #000; }
        
        h3 { 
            font-family: 'Space Mono', monospace; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: -0.03em; 
        }
        /* --- TOAST CONTAINER --- */
#toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* --- TOAST ITEM --- */
.toast-neo {
    min-width: 300px;
    background: #fff;
    border: 3px solid #000;
    padding: 15px;
    font-family: 'Space Mono', monospace;
    font-weight: 700;
    box-shadow: 6px 6px 0 #000;
    animation: slideIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* --- TOAST VARIANTS --- */
.toast-success { background: #4ade80; color: #000; }
.toast-error { background: #ff4d4d; color: #fff; border-color: #000; }
.toast-info { background: #fff; color: #000; }

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
    </style>
</head>
<body>

<div class="container mt-5 mb-5 position-relative" style="z-index: 1;">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <div class="d-flex justify-content-between align-items-center mb-5 border-bottom border-2 border-dark pb-3">
                <h3><i class="bi bi-gear-fill me-2 text-primary" style="color: var(--accent) !important;"></i>User Config</h3>
                <a href="index.php" class="btn-back"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
            </div>

            <?php if($message): ?>
                <div class="alert alert-success alert-dismissible fade show mb-4">
                    <i class="bi bi-check-square-fill me-2"></i> <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
<div class="col-12">
    <div class="card-neo d-flex flex-row flex-wrap align-items-center p-4 gap-4">
        
        <div style="width: 80px; height: 80px; border: 2px solid #000; overflow: hidden; border-radius: 50%; flex-shrink: 0;">
            <?php 
                $hasAvatar = !empty($user['avatar']) && file_exists($user['avatar']);
                $avUrl = $hasAvatar ? $user['avatar'] : 'https://via.placeholder.com/80?text=USER'; 
            ?>
            <img src="<?php echo $avUrl; ?>" id="avatarPreview" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        
        <div class="flex-grow-1">
            <h5 class="fw-bold m-0">PROFILE PHOTO</h5>
            <small class="text-muted">Square image, max 2MB.</small>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-neo w-auto px-4" onclick="document.getElementById('avatarInput').click()">
                UPLOAD
            </button>
            <input type="file" id="avatarInput" style="display:none;" onchange="uploadAvatar()">

            <?php if($hasAvatar): ?>
            <button class="btn btn-neo w-auto px-3" 
                    onclick="deleteAvatar()" 
                    title="Delete Avatar"
                    style="background: var(--danger); color: #fff; box-shadow: 4px 4px 0 #000; border-color: #000;">
                <i class="bi bi-trash-fill"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Keep your existing JS logic or use the one below
function uploadAvatar() {
    const file = document.getElementById('avatarInput').files[0];
    if(!file) return;
    const fd = new FormData();
    fd.append('avatar', file);
    fetch('api.php?action=upload_avatar', {method:'POST', body:fd})
    .then(r => r.json()).then(d => {
        if(d.status === 'success') {
            document.getElementById('avatarPreview').src = d.path + '?t=' + new Date().getTime();
            showToast('Avatar Updated', 'success');
            // Reload page to show Delete button if it was hidden
            setTimeout(() => location.reload(), 1000);
        } else showToast(d.message, 'error');
    });
}

function deleteAvatar() {
    if(!confirm("Remove profile photo?")) return;
    fetch('api.php?action=delete_avatar', {method:'POST'})
    .then(r => r.json()).then(d => {
        if(d.status === 'success') {
            document.getElementById('avatarPreview').src = 'https://via.placeholder.com/80?text=USER';
            document.getElementById('avatarInput').value = ''; 
            showToast('Avatar Removed', 'success');
            setTimeout(() => location.reload(), 1000);
        } else showToast(d.message, 'error');
    });
}
</script>
                <div class="col-md-6">
                    <div class="card-neo">
                        <div class="card-header-neo">
                            <i class="bi bi-person-badge me-2"></i> Identity Data
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                
                                <div class="mb-3">
                                    <label>Full Name</label>
                                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label>Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <div class="mb-4">
                                    <label>Access Level</label>
                                    <input type="text" class="form-control" value="<?php echo strtoupper($user['role']); ?>" readonly>
                                </div>
                                
                                <button type="submit" name="update_info" class="btn-neo">
                                    Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card-neo">
                        <div class="card-header-neo" style="background: var(--danger); color: white;">
                            <i class="bi bi-shield-lock-fill me-2"></i> Security Key
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                
                                <div class="mb-3">
                                    <label>Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required placeholder="••••••••">
                                </div>
                                
                                <hr style="border: 1px solid #000; opacity: 0.2; margin: 20px 0;">
                                
                                <div class="mb-3">
                                    <label>New Password</label>
                                    <input type="password" name="new_password" class="form-control" required placeholder="Min 6 chars">
                                </div>
                                
                                <div class="mb-4">
                                    <label>Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat new">
                                </div>
                                
                                <button type="submit" name="change_password" class="btn-neo" style="box-shadow: 4px 4px 0 var(--danger);">
                                    Update Key
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
<div id="toast-container"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toast Function
    function showToast(message, type = 'info', duration = 3000) {
        const toastContainer = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.classList.add('toast-neo');

        if (type === 'success') {
            toast.classList.add('toast-success');
        } else if (type === 'error') {
            toast.classList.add('toast-error');
        } else {
            toast.classList.add('toast-info');
        }

        toast.innerHTML = `
            <span>${message}</span>
            <button class="btn-close" aria-label="Close"></button>
        `;

        toastContainer.appendChild(toast);

        // Close button functionality
        toast.querySelector('.btn-close').addEventListener('click', () => {
            toastContainer.removeChild(toast);
        });

        // Auto-remove after duration
        setTimeout(() => {
            if (toastContainer.contains(toast)) {
                toastContainer.removeChild(toast);
            }
        }, duration);
    }
    function deleteAvatar() {
    if(!confirm("Remove profile photo?")) return;
    fetch('api.php?action=delete_avatar', {method:'POST'})
    .then(r => r.json()).then(d => {
        if(d.status === 'success') {
            document.getElementById('avatarPreview').src = 'https://via.placeholder.com/80?text=USER';
            document.getElementById('avatarInput').value = ''; 
            showToast('Avatar Removed', 'success');
            setTimeout(() => location.reload(), 1000);
        } else showToast(d.message, 'error');
    });
}
</script>
</body>
</html>