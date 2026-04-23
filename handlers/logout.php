<?php
/*
 * Destroy user session and redirect
 * Clears all session data and returns user to homepage
 */
session_start();
session_destroy();
header("Location: ../index.php");
exit;

