<?php
/**
 * RESET PASSWORD PAGE
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 *
 * Validates the reset token from the URL, checks it hasn't
 * expired or been used, then allows the user to set a new password.
 * The password is hashed with bcrypt before storing.
 */

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

$error = '';
$success = '';
$valid_token = false;
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if (!empty($token)) {
    // Validate the token
    $reset = db_query_single(
        "SELECT pr.reset_id, pr.user_id, pr.expires_at, pr.used, u.username, u.email
         FROM password_resets pr
         JOIN users u ON pr.user_id = u.user_id
         WHERE pr.token = ?",
        [$token]
    );

    if (!$reset) {
        $error = 'Invalid reset link. Please request a new password reset.';
    } elseif ($reset['used']) {
        $error = 'This reset link has already been used. Please request a new one.';
    } elseif (strtotime($reset['expires_at']) < time()) {
        $error = 'This reset link has expired. Please request a new one.';
    } else {
        $valid_token = true;
    }
} else {
    $error = 'No reset token provided. Please use the link from the forgot password page.';
}

// Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset' && $valid_token) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password)) {
        $error = 'Please enter a new password.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Hash the new password with bcrypt
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);

        // Update the user's password in the database
        $update_success = db_execute(
            "UPDATE users SET password_hash = ? WHERE user_id = ?",
            [$new_hash, $reset['user_id']]
        );

        if ($update_success) {
            // Mark the token as used so it can't be reused
            db_execute(
                "UPDATE password_resets SET used = 1 WHERE reset_id = ?",
                [$reset['reset_id']]
            );

            $success = 'Your password has been reset successfully. You can now log in with your new password.';
            $valid_token = false; // Hide the form
        } else {
            $error = 'Failed to update password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - QuizNinja</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .reset-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #1a1a1a;
        }

        .reset-card {
            width: 100%;
            max-width: 440px;
            background: #222;
            border-radius: 8px;
            padding: 2.5rem;
            border: 1px solid #333;
            position: relative;
            overflow: hidden;
        }

        .reset-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #d4a843;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .reset-header h2 {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .reset-header p {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.9rem;
            margin: 0;
        }

        .user-info {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 5px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .user-info strong {
            color: #d4a843;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .form-control {
            width: 100%;
            padding: 0.7rem 0.85rem;
            border: 1px solid #444;
            border-radius: 5px;
            font-size: 0.95rem;
            font-family: inherit;
            color: #fff;
            background: #1a1a1a;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #d4a843;
            box-shadow: 0 0 0 3px rgba(212, 168, 67, 0.2);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .form-hint {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.3);
            margin-top: 0.3rem;
        }

        .btn-reset {
            width: 100%;
            padding: 0.75rem;
            background: #d4a843;
            color: #111;
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

        .btn-reset:hover {
            background: #c49a38;
            box-shadow: 0 4px 12px rgba(212, 168, 67, 0.3);
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 5px;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
            border-left: 4px solid;
        }

        .alert-error {
            background: rgba(198, 40, 40, 0.15);
            color: #ef6b6b;
            border-left-color: #ef6b6b;
        }

        .alert-success {
            background: rgba(46, 125, 50, 0.15);
            color: #6abf6e;
            border-left-color: #6abf6e;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.85rem;
            text-decoration: none;
        }

        .back-link:hover {
            color: #d4a843;
        }
    </style>
</head>
<body>
    <div class="reset-wrapper">
        <div class="reset-card">
            <div class="reset-header">
                <h2>Reset Password</h2>
                <p>Enter your new password below</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <a href="login.php" class="btn-reset" style="display: block; text-align: center; text-decoration: none; margin-top: 1rem;">Go to Login</a>
            <?php endif; ?>

            <?php if ($valid_token && !$success): ?>
                <div class="user-info">
                    Resetting password for <strong><?php echo htmlspecialchars($reset['username']); ?></strong>
                    (<?php echo htmlspecialchars($reset['email']); ?>)
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="reset">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label class="form-label" for="new-pass">New Password</label>
                        <input type="password" id="new-pass" name="new_password" class="form-control"
                               placeholder="Enter new password" required minlength="8">
                        <span class="form-hint">Must be at least 8 characters long</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm-pass">Confirm Password</label>
                        <input type="password" id="confirm-pass" name="confirm_password" class="form-control"
                               placeholder="Confirm new password" required>
                    </div>

                    <button type="submit" class="btn-reset">Reset Password</button>
                </form>
            <?php endif; ?>

            <?php if (!$valid_token && !$success): ?>
                <a href="forgot_password.php" class="back-link">Request a new reset link</a>
            <?php endif; ?>

            <a href="login.php" class="back-link">Back to Login</a>
        </div>
    </div>
</body>
</html>
