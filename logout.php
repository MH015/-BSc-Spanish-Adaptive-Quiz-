<?php
/**
 * LOGOUT PAGE
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 * PURPOSE:
 * Handles user logout by calling logout_user() from auth.php,
 * which unsets all session variables, deletes the session cookie,
 * and destroys the session. Redirects to login.php afterwards.
 *
 * This is a simple handler file — all the actual logout logic
 * is in includes/auth.php to maintain separation of concerns.
 */

require_once 'includes/auth.php';

logout_user();

header('Location: login.php?success=You have been logged out successfully.');
exit;
?>
