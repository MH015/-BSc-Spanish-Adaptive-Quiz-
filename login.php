<?php
/**
 * LOGIN / REGISTER PAGE
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 */

session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

$error = '';
$success = '';
$active_tab = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if (empty($email) || empty($password)) {
                $error = 'Please fill in all fields.';
            } else {
                $result = login_user($email, $password);
                if ($result['success']) {
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = $result['message'];
                }
            }
        }
        if ($_POST['action'] === 'register') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            if (empty($username) || empty($email) || empty($password)) {
                $error = 'Please fill in all fields.';
                $active_tab = 'register';
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match.';
                $active_tab = 'register';
            } else {
                $result = register_user($username, $email, $password);
                if ($result['success']) {
                    $success = $result['message'];
                    $active_tab = 'login';
                } else {
                    $error = $result['message'];
                    $active_tab = 'register';
                }
            }
        }
    }
}

if (isset($_GET['error'])) { $error = htmlspecialchars($_GET['error']); }
if (isset($_GET['success'])) { $success = htmlspecialchars($_GET['success']); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QuizNinja</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            padding: 0;
            background: var(--bg-color, #1a1a1a);
        }

        /* Left branding panel */
        .login-brand-panel {
            flex: 0 0 42%;
            background: linear-gradient(160deg, #111111 0%, #1a1a1a 50%, #111111 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .login-brand-panel::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-color, #d4a843);
        }

        .login-brand-panel::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -80px;
            width: 250px;
            height: 250px;
            border: 40px solid rgba(212, 168, 67, 0.08);
            border-radius: 50%;
        }

        .brand-content {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            color: #d4a843;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.75rem;
            letter-spacing: 0.02em;
        }

        .brand-accent {
            width: 50px;
            height: 3px;
            background: var(--accent-color, #d4a843);
            margin: 0 auto 1.5rem;
            border-radius: 2px;
        }

        .brand-tagline {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        .brand-features {
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            max-width: 280px;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .brand-feature-icon {
            width: 32px;
            height: 32px;
            background: rgba(212, 168, 67, 0.15);
            border: 1px solid rgba(212, 168, 67, 0.3);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
            color: var(--accent-color, #d4a843);
        }

        /* Right form panel */
        .login-form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background: #1a1a1a;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: #222222;
            border-radius: 8px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            border: 1px solid var(--gray-200, #333333);
            border-top: none;
            position: relative;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color, #d4a843), var(--accent-color, #d4a843));
            border-radius: 8px 8px 0 0;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 1.5rem;
            color: #ffffff;
            margin-bottom: 0.35rem;
        }

        .login-header p {
            color: var(--text-muted, rgba(255,255,255,0.4));
            font-size: 0.9rem;
            margin: 0;
        }

        /* Auth tabs */
        .auth-tabs {
            display: flex;
            margin-bottom: 1.75rem;
            background: var(--gray-50, #1a1a1a);
            border-radius: 6px;
            padding: 4px;
            border: 1px solid var(--gray-200, #333333);
        }

        .auth-tab {
            flex: 1;
            padding: 0.6rem;
            text-align: center;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-muted, rgba(255,255,255,0.4));
            cursor: pointer;
            border: none;
            border-radius: 4px;
            transition: all 0.2s ease;
            background: transparent;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .auth-tab:hover {
            color: var(--primary-color, #d4a843);
        }

        .auth-tab.active {
            background: #222222;
            color: var(--primary-dark, #111111);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .auth-form { display: none; }
        .auth-form.active { display: block; }

        .form-group { margin-bottom: 1.25rem; }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-primary, #ffffff);
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .form-control {
            width: 100%;
            padding: 0.7rem 0.85rem;
            border: 2px solid var(--gray-200, #333333);
            border-radius: 5px;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--text-primary, #ffffff);
            background: #222222;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color, #d4a843);
            box-shadow: 0 0 0 3px rgba(212, 168, 67, 0.12);
        }

        .form-hint {
            font-size: 0.78rem;
            color: var(--text-muted, rgba(255,255,255,0.4));
            margin-top: 0.3rem;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: #d4a843;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 0.5rem;
        }

        .btn-login:hover {
            color: #111;
            background: #c49a38;
            box-shadow: 0 4px 12px rgba(212, 168, 67, 0.25);
        }

        .login-footer {
            text-align: center;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--gray-200, #333333);
        }

        .login-footer p {
            font-size: 0.78rem;
            color: var(--text-muted, rgba(255,255,255,0.4));
            margin: 0;
            line-height: 1.5;
        }

        /* Alerts */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 5px;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
            border-left: 4px solid;
        }
        .alert-error {
            background: rgba(198,40,40,0.15);
            color: #ef6b6b;
            border-left-color: #ef6b6b;
        }
        .alert-success {
            background: rgba(46,125,50,0.15);
            color: #6abf6e;
            border-left-color: #6abf6e;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .login-brand-panel { display: none; }
            .login-wrapper { background: var(--primary-dark, #111111); }
            .login-form-panel { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Left branding panel -->
        <div class="login-brand-panel">
            <div class="brand-content">
                <div class="brand-logo">QuizNinja</div>
                <div class="brand-accent"></div>
                <p class="brand-tagline">Adaptive Spanish vocabulary learning that adjusts to your level</p>
                <div class="brand-features">
                    <div class="brand-feature">
                        <div class="brand-feature-icon">Q</div>
                        <span>96 questions across 4 categories</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-icon">A</div>
                        <span>Adaptive difficulty that learns with you</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-icon">P</div>
                        <span>Track your progress over time</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="login-form-panel">
            <div class="login-card">
                <div class="login-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to continue your learning journey</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="auth-tabs">
                    <button class="auth-tab <?php echo $active_tab === 'login' ? 'active' : ''; ?>" 
                            onclick="switchTab('login')">Login</button>
                    <button class="auth-tab <?php echo $active_tab === 'register' ? 'active' : ''; ?>" 
                            onclick="switchTab('register')">Register</button>
                </div>

                <!-- Login Form -->
                <form method="POST" class="auth-form <?php echo $active_tab === 'login' ? 'active' : ''; ?>" id="login-form">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label class="form-label" for="login-email">Email Address</label>
                        <input type="email" id="login-email" name="email" class="form-control" 
                               placeholder="you@example.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="login-pass">Password</label>
                        <input type="password" id="login-pass" name="password" class="form-control" 
                               placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn-login">Sign In</button>
                    <a href="forgot_password.php" style="display: block; text-align: center; margin-top: 1rem; color: #d4a843; font-size: 0.85rem; text-decoration: none;">Forgot Password?</a>
                </form>

                <!-- Register Form -->
                <form method="POST" class="auth-form <?php echo $active_tab === 'register' ? 'active' : ''; ?>" id="register-form">
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <label class="form-label" for="reg-user">Username</label>
                        <input type="text" id="reg-user" name="username" class="form-control" 
                               placeholder="Choose a username" required minlength="3">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="reg-email">Email Address</label>
                        <input type="email" id="reg-email" name="email" class="form-control" 
                               placeholder="you@example.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="reg-pass">Password</label>
                        <input type="password" id="reg-pass" name="password" class="form-control" 
                               placeholder="Minimum 8 characters" required minlength="8">
                        <span class="form-hint">Must be at least 8 characters long</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="reg-confirm">Confirm Password</label>
                        <input type="password" id="reg-confirm" name="confirm_password" class="form-control" 
                               placeholder="Confirm your password" required>
                    </div>
                    <button type="submit" class="btn-login">Create Account</button>
                </form>

                <div class="login-footer">
                    <p>BSc Computer Science Project<br>Matthew Holness &middot; 22068679</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            if (tab === 'login') {
                document.querySelector('.auth-tab:first-child').classList.add('active');
                document.getElementById('login-form').classList.add('active');
            } else {
                document.querySelector('.auth-tab:last-child').classList.add('active');
                document.getElementById('register-form').classList.add('active');
            }
        }
    </script>
</body>
</html>
