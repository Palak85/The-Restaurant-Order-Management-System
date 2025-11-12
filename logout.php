<?php
// Start or resume session
session_start();

// Destroy the session
session_destroy();

// Redirect to the login page
header('Location: login.html');
exit;
?>