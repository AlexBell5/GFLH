<?php 
/*
 * Accessibility and preferences settings page
 * Provides screen reader, voice selection, speed control, and translation options
 */
session_start();


$host = 'localhost';
$db = 'GFLH';
$dbuser = 'root';
$pass = '';

$conn = new mysqli($host, $dbuser, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screen Reader Settings</title>
    <link rel="stylesheet" href="../styles/settings.css" />
    <link rel="stylesheet" href="../styles/navbar.css" />
</head>
<body>
    <!-- Include navbar inside body -->
    <?php include('../includes/navbar.php'); ?>

<div id="screen-reader-container">
    <h2>Accessibility Settings</h2>
    <button id="toggle-reader">Enable Screen Reader</button>
    <label>
        Speed: <input type="range" id="speed-control" min="0.5" max="2" step="0.1" value="1">
    </label>
    <label>
        Voice: <select id="voice-select"></select>
    </label>
    Translator:
    <div id="google_translate_element"></div>
</div>


<script type="text/javascript">
function googleTranslateElementInit() {
    new google.translate.TranslateElement(
        { pageLanguage: 'en' },
        'google_translate_element'
    );
}
</script>

<script type="text/javascript"
        src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
</script>

<script src="../scripts/settings.js"></script>
</body>
</html>
