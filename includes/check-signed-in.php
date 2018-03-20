<?php 
// Assumes db.php already imported
$symlink = explode('/', $_SERVER['REQUEST_URI'])[1];
$stmt = $db->prepare('SELECT token FROM users WHERE slug = ?');
$stmt->execute([$symlink]);
if ($stmt->rowCount() === 0) { // first part of url (i.e. $symlink) not in db -- default to oberlin, change $symblink to oberlin
  $stmt = $db->query('SELECT token FROM users WHERE slug = \'oberlin\'');
  $symlink = 'oberlin';
}
// always log in if not running on site
if ($production_server && (!isset($_COOKIE['token']) || $stmt->fetchColumn() !== $_COOKIE['token'])) {
  header("Location: https://environmentaldashboard.org/{$symlink}/prefs/");
}
?>