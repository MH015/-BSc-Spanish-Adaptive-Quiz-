<?php
/**
 * LOGOUT PAGE
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 */

require_once 'includes/auth.php';

logout_user();

header('Location: login.php?success=You have been logged out successfully.');
exit;
?>
