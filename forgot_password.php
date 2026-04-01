<?php
/**
 * FORGOT PASSWORD PAGE
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 *
 * Allows users to request a password reset by entering their email.
 * Generates a secure token stored in the password_resets table.
 * In production, the reset link would be emailed to the user.
 * For this prototype, the link is displayed on screen.
 */

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/auth.php';

$error = '';
$success = '';
$reset_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'forgot') {
        $email = trim(strtolower($_POST['email'] ?? ''));

        if (empty($email)) {
            $error = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if user exists
            $user = db_query_single("SELECT user_id, username FROM users WHERE email = ?", [$email]);

            if ($user) {
                // Generate a secure random token
                $token = bin2hex(random_bytes(32));

                // Set expiry to 1 hour from now
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Invalidate any existing tokens for this user
                db_execute("UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0", [$user['user_id']]);

                // Insert new reset token
                db_execute(
                    "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)",
                    [$user['user_id'], $token, $expires]
                );

                // Build the reset link
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $path = dirname($_SERVER['PHP_SELF']);
                $reset_link = $protocol . '://' . $host . $path . '/reset_password.php?token=' . $token;

                $success = 'Password reset link generated for ' . htmlspecialchars($user['username']) . '. In a production environment, this link would be sent via email.';
            } else {
                // Don't reveal whether the email exists (security best practice)
                $success = 'If an account with that email exists, a password reset link has been generated.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - QuizNinja</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .forgot-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #1a1a1a;
        }

        .forgot-card {
            width: 100%;
            max-width: 440px;
            background: #222;
            border-radius: 8px;
            padding: 2.5rem;
            border: 1px solid #333;
            position: relative;
            overflow: hidden;
        }

        .forgot-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #d4a843;
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .forgot-header h2 {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .forgot-header p {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.9rem;
            margin: 0;
            line-height: 1.5;
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

        .reset-link-box {
            margin-top: 1.25rem;
            padding: 1rem;
            background: #1a1a1a;
            border: 1px solid #d4a843;
            border-radius: 5px;
        }

        .reset-link-box .label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #d4a843;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.5rem;
        }

        .reset-link-box a {
            color: #d4a843;
            word-break: break-all;
            font-size: 0.85rem;
            text-decoration: underline;
        }

        .reset-link-box .note {
            margin-top: 0.75rem;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.35);
            line-height: 1.5;
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

        .proto-note {
            margin-top: 1.5rem;
            padding: 0.75rem 1rem;
            background: rgba(212, 168, 67, 0.08);
            border: 1px solid rgba(212, 168, 67, 0.2);
            border-radius: 5px;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.5;
        }

        .proto-note strong {
            color: #d4a843;
        }
    </style>
</head>
<body>
    <div class="forgot-wrapper">
        <div class="forgot-card">
            <div class="forgot-header">
                <h2>Forgot Password</h2>
                <p>Enter your email address and we'll generate a password reset link</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($reset_link): ?>
                <div class="reset-link-box">
                    <div class="label">Password Reset Link</div>
                    <a href="<?php echo htmlspecialchars($reset_link); ?>">
                        <?php echo htmlspecialchars($reset_link); ?>
                    </a>
                    <div class="note">
                        This link expires in 1 hour. In a production environment, this link
                        would be sent to the user's email address using PHP's mail() function
                        or a service like SendGrid/Mailgun.
                    </div>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="action" value="forgot">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="you@example.com" required>
                    </div>
                    <button type="submit" class="btn-reset">Send Reset Link</button>
                </form>
            <?php endif; ?>

            <div class="proto-note">
                <strong>Note:</strong> This is a local development environment without email
                functionality. In production, the reset link would be delivered via email rather
                than displayed on screen. The token is cryptographically secure (64-character hex
                string generated with random_bytes) and expires after 1 hour.
            </div>

            <a href="login.php" class="back-link">Back to Login</a>
        </div>
    </div>
</body>
</html>
