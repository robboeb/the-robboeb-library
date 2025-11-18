<?php
// Redirect to browse page as the default landing page
require_once __DIR__ . '/../config/constants.php';
header('Location: ' . BASE_URL . '/public/browse.php');
exit;
?>
