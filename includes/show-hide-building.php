<?php
if (isset($_POST['action']) && isset($_POST['building_id'])) {
  require '../../includes/db.php';
  $stmt = $db->prepare('UPDATE buildings SET hidden = ? WHERE id = ?');
  $stmt->execute(array(($_POST['action'] === 'show-building') ? 0 : 1, $_POST['building_id']));
}
?>