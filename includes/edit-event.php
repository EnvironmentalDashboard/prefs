<?php
require '../../includes/db.php';
date_default_timezone_set('America/New_York');
$date = strtotime($_POST['date']);
$date2 = strtotime($_POST['date2']);
if (!$date) {
	$error = "Error parsing date \"{$_POST['date']}\"";
} elseif (!$date2) {
	$error = "Error parsing date \"{$_POST['date2']} {$_POST['time2']}\"";
} elseif (empty($_POST['event'])) {
	$error = 'You forgot to fill in a field';
}
else {
	$repeat_end = (strtotime($_POST['end_date']) === false) ? 0 : strtotime($_POST['end_date']);
	$stmt = $db->prepare('UPDATE calendar SET event = ?, start = ?, `end` = ?, description = ?, loc_id = ?, custom_loc = ?, screen_ids = ?, img_size = ?, repeat_every = ?, repeat_end = ? WHERE id = ?');
	$_POST['description'] = (isset($_POST['description'])) ? $_POST['description'] : ''; // if a description isnt in form, empty string
	$stmt->execute(array($_POST['event'], $date, $date2, $_POST['description'], $_POST['loc'], $_POST['custom_loc'], implode(',', $_POST['screen_loc']), $_POST['img_size'], $_POST['repeat_every'], $repeat_end, $_POST['id']));
	$success = 'Your event was successfully uploaded and will be reviewed';
}
?>