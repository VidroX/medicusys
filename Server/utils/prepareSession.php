<?php
session_start();
if (!isset($_SESSION['locale'])) {
    $_SESSION['locale'] = 'uk';
}
$dictionary = include 'locale/' . $_SESSION['locale'] . '.php';
?>