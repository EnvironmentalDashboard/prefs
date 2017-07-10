<?php
// error_reporting(-1);
// ini_set('display_errors', 'On');
require '../../includes/db.php';
require '../../includes/class.Meter.php';
$grouping = array();
$days = array('sun', 'mon', 'tue', 'wed', 'thur', 'fri', 'sat');
for ($group = 1; $group < 8; $group++) {
  $day_grouping = array();
  foreach ($days as $index => $day) {
    if ($_POST[$day] == $group) {
      $day_grouping[] = $index + 1;
    }
  }
  if (!empty($day_grouping)) {
    $grouping[] = array('days' => $day_grouping, $_POST['go_back_by'.$group] => $_POST['amount'.$group]);
  }
}
$stmt = $db->prepare('SELECT bos_uuid FROM meters WHERE id = ?');
$stmt->execute(array($_POST['rv_meter_id']));
$uuid = $stmt->fetchColumn();
$stmt = $db->prepare('INSERT INTO relative_values (meter_uuid, grouping, relative_value, permission) VALUES (?, ?, ?, ?)');
$json = json_encode($grouping);
$stmt->execute(array($uuid, $json, 0, 'gauges'));

echo '<label class="custom-control custom-radio"><input name="existing_configs" type="radio" class="custom-control-input" value="'.$db->lastInsertId().'"><span class="custom-control-indicator"></span><span class="custom-control-description"><code>'.$json.'</code></span></label>';
?>