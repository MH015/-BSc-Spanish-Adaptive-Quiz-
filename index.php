<?php
/**
 *
 * INDEX PAGE - Entry Point
 * QuizNinja - Adaptive Quiz Web Application
 * Author: Matthew Holness
 * Student ID: 22068679
 * 
 */

session_start();

// Redirect based on login status
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
?>
