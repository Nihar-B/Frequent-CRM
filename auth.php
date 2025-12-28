<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // 1. Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 2. Verify Password
        if ($user && password_verify($password, $user['password'])) {
            
            if ($user['status'] !== 'active') {
                $_SESSION['error'] = "Account is inactive.";
                header("Location: login.php");
                exit;
            }

            // 3. Set Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: login.php");
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: login.php");
        exit;
    }
}
?>