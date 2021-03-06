<?php 
// Assumes db.php already imported
$stmt = $db->prepare('SELECT token FROM users WHERE slug = ?');
$stmt->execute([$subdomain]);
// always log in if not running on site
if ($production_server && (!isset($_COOKIE['token']) || $stmt->fetchColumn() !== $_COOKIE['token'])) {
  header("Location: https://{$subdomain}.environmentaldashboard.org/prefs/");
}
?>