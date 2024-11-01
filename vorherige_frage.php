<?php
session_start();

// Prüfen, ob die Session die Keys "fragen" und "fragenindex" enthält
if (!isset($_SESSION['fragen']) || !isset($_SESSION['fragenindex'])) {
    header('Location: index.php');
    exit();
}

// Fragenindex dekrementieren, wenn größer als 0
if ($_SESSION['fragenindex'] > 0) {
    $_SESSION['fragenindex']--;
}

// Weiterleitung zu index.php
header('Location: index.php');
exit();