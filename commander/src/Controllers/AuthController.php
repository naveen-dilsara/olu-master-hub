<?php

namespace Olu\Commander\Controllers;

use Olu\Commander\Models\User;

class AuthController {
    
    public function login() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $userModel = new User();
            
            // Temporary: Ensure admin exists for first run
            // In production, this would be a seed or migration
            // Temporary: Ensure Admin User Exists
            if ($username === 'naveen@olutk.com') {
                $userModel->ensureAdminExists('naveen@olutk.com', 'Naveen@991217');
            }

            $user = $userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: /');
                exit;
            } else {
                $error = "Invalid credentials";
            }
        }

        // Render basic login view (no master layout)
        require __DIR__ . '/../../views/auth/login.php';
    }

    public function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }
}
