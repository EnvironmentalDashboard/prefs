<?php
error_reporting(-1);
ini_set('display_errors', 'On');
date_default_timezone_set('America/New_York');
require '../includes/db.php';
require 'includes/check-signed-in.php';
if (isset($_POST['building-id']) && isset($_POST['building-image'])) {
  $stmt = $db->prepare('UPDATE buildings SET custom_img = ? WHERE id = ?');
  $stmt->execute(array($_POST['building-image'], $_POST['building-id']));
}
function time_ago($last_updated) {
  if ($last_updated === 0) {
    return "Never updated";
  }
  $diff = time() - $last_updated;
  if ($diff <= 60) {
    return "Updated {$diff}s ago";
  }
  elseif ($diff <= 3600) {
    return 'Updated ' . floor($diff/60) . ' mins ago';
  }
  else {
    return 'Updated over an hour ago';
  }
}
function name_that_grouping($grouping) {
  $grouping = json_decode($grouping, true);
  if ($grouping[0]['days'] == array(2,3,4,5,6) && $grouping[1]['days'] == array(1,7) ||
      $grouping[1]['days'] == array(2,3,4,5,6) && $grouping[0]['days'] == array(1,7)) {
    return 'Weekdays vs. weekends';
  }
  return 'Custom grouping';
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Meters</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="clear: both;height: 30px"></div>
      <div class="row">
        <div class="col-sm-8">
          <h1>Meters</h1>
          <p>On average, the last attempt to update a meter was made <?php echo $db->query("SELECT ROUND(AVG(UNIX_TIMESTAMP() - live_last_updated)/60, 2) AS minutes FROM meters WHERE source = 'buildingos' AND ((gauges_using > 0 OR for_orb > 0 OR timeseries_using > 0) OR bos_uuid IN (SELECT DISTINCT meter_uuid FROM relative_values WHERE permission = 'orb_server' AND meter_uuid != ''))")->fetchColumn(); ?> minutes ago.</p>
        </div>
        <div class="col-sm-4">
          <form action="" method="GET" id='sortbyform'>
            Show: 
            <select class="form-control" name="sortby" id="sortby">
              <option value="meters_collected">Meters data are collected for</option>
              <option value="ignored_meters" <?php echo (isset($_GET['sortby']) && $_GET['sortby'] === 'ignored_meters') ? 'selected' : ''; ?>>Meters data are not collected</option>
              <option value="all" <?php echo (isset($_GET['sortby']) && $_GET['sortby'] === 'all') ? 'selected' : ''; ?>>All meters</option>
            </select>
          </form>
        </div>
      </div>
      <?php
      $page = (empty($_GET['page'])) ? 0 : intval($_GET['page']) - 1;
      $count = $db->query("SELECT COUNT(*) FROM buildings WHERE org_id IN (SELECT org_id FROM users_orgs_map WHERE user_id = {$user_id})")->fetchColumn();
      $limit = 10;
      $offset = $limit * $page;
      $final_page = ceil($count / $limit);
      foreach ($db->query("SELECT id, name, hidden, custom_img FROM buildings WHERE org_id IN (SELECT org_id FROM users_orgs_map WHERE user_id = {$user_id}) ORDER BY hidden ASC, name ASC LIMIT {$offset}, {$limit}") as $building) {
        if ($db->query("SELECT COUNT(*) FROM meters WHERE building_id = {$building['id']}")->fetchColumn() === '0') { // skip buildings with no meters
          continue;
        }
      ?>
      <div class="row">
        <div class="col-sm-12">
          <h3 style="display: inline;"><?php echo $building['name']; ?>
            <div class="btn-group" role="group" aria-label="Basic example">
              <button type="button" class="btn btn-light btn-sm <?php echo ($building['hidden'] === '0') ? 'active' : ''; ?> btn-secondary show-building" data-building_id="<?php echo $building['id'] ?>" data-action="show-building">Shown</button>
              <button type="button" class="btn btn-light btn-sm <?php echo ($building['hidden'] === '0') ? '' : 'active'; ?> btn-secondary hide-building" data-building_id="<?php echo $building['id'] ?>" data-action="hide-building">Hidden</button>
            </div>
          </h3>
          <?php if ($building['custom_img'] === null) { ?>
          <form action="" method="POST" class="form-inline" style="display: inline; position: relative; bottom:2px; margin-left: 10px">
            <input type="hidden" name="building-id" value="<?php echo $building['id'] ?>">
            <?php $rand = uniqid(); ?>
            <label class="sr-only" for="<?php echo $rand ?>">Building image URL</label>
            <input type="text" class="form-control form-control-sm mb-2 mr-sm-2 mb-sm-0" id="<?php echo $rand ?>" placeholder="Building image URL" name="building-image">
            <button type="submit" class="btn btn-sm btn-primary">Submit</button>
          </form>
          <?php } ?>
          <table class="table table-sm" style="overflow-x: scroll;margin-bottom: 50px;margin-top: 10px">
            <thead>
              <tr>
                <th>BuildingOS&nbsp;ID</th>
                <th>Meter&nbsp;name</th>
                <?php if (!isset($_GET['sortby']) || $_GET['sortby'] !== 'meters_collected') { ?>
                <th>Last update attempt</th>
                <th>Last recorded reading</th>
                <?php } ?>
                <th>Relative value configuration</th>
                <th>Gauges</th>
                <th>Time&nbsp;series</th>
                <th>Orb</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if (isset($_GET['sortby']) && $_GET['sortby'] === 'ignored_meters') {
                $sql = 'AND (gauges_using = 0 AND for_orb = 0 AND timeseries_using = 0) ';
              } elseif (isset($_GET['sortby']) && $_GET['sortby'] === 'all') {
                $sql = '';
              } else {
                $sql = 'AND (gauges_using > 0 OR for_orb > 0 OR timeseries_using > 0) OR bos_uuid IN (SELECT DISTINCT meter_uuid FROM relative_values WHERE permission = \'orb_server\') ';
              }
              foreach ($db->query("SELECT id, org_id, bos_uuid, name, url, current, live_last_updated, gauges_using, timeseries_using, for_orb, orb_server FROM meters WHERE building_id = {$building['id']} {$sql}ORDER BY name ASC") as $meter) {
                $tr_class = '';
                if ($meter['current'] === null) {
                  $tr_class = 'table-danger';
                } else if (strtotime('-15 minutes') > $meter['live_last_updated']) {
                  $tr_class = 'table-warning';
                }
                echo "<tr class='{$tr_class}'>";
                echo "<td>{$meter['bos_uuid']}</td>";
                echo "<td>{$meter['name']}</td>";
                if (!isset($_GET['sortby']) || $_GET['sortby'] !== 'meters_collected') {
                  echo "<td>".date('F j, Y, g:i a', $meter['live_last_updated'])."</td>";
                  $last_recorded_reading = $db->query("SELECT recorded FROM meter_data WHERE meter_id = {$meter['id']} AND resolution = 'live' ORDER BY recorded DESC LIMIT 1")->fetchColumn();
                  if ($last_recorded_reading == 0) {
                    echo "<td>Never updated</td>";
                  } else {
                    echo "<td>".date('F j, Y, g:i a', $last_recorded_reading)."</td>";
                  }
                }
                $stmt = $db->prepare('SELECT grouping, last_updated FROM relative_values WHERE meter_uuid = ?');
                $stmt->execute(array($meter['bos_uuid']));
                $relative_values = $stmt->fetchAll();
                $count = count($relative_values);
                if ($count === 0) {
                  echo "<td>-</td>";
                } else {
                  echo "<td>";
                  $tmp = 0;
                  foreach ($relative_values as $rv) {
                    echo name_that_grouping($rv['grouping']);
                    echo ' ('.time_ago($rv['last_updated']).')';
                    echo (++$tmp == $count) ? "\n" : ", \n";
                  }
                  echo "</td>";
                }
                echo $meter['gauges_using'] > 0 ? "<td>{$meter['gauges_using']}</td>" : "<td class='text-muted'>-</td>";
                echo $meter['timeseries_using'] > 0 ? "<td>{$meter['timeseries_using']}</td>" : "<td class='text-muted'>-</td>";
                if ($meter['for_orb'] > 0) {
                  $stmt = $db->prepare('SELECT INET_NTOA(ip) AS ip FROM orbs WHERE elec_uuid = ? OR water_uuid = ?');
                  $stmt->execute(array($meter['bos_uuid'], $meter['bos_uuid']));
                  $ip = $stmt->fetchColumn();
                  echo "<td>{$ip}</td>";
                } else {
                  echo "<td>-</td>";
                }
                echo "</tr>";
              } ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php
      }
      parse_str($_SERVER['QUERY_STRING'], $tmp_qs);
      unset($tmp_qs['page']);
      $qs = http_build_query($tmp_qs);
      ?>
      <div class="row">
        <div class="col-sm-12">
          <nav aria-label="Page navigation" class="text-center">
            <ul class="pagination pagination-lg">
              <?php if ($page > 0) { ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page . "&{$qs}" ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                  <span class="sr-only">Previous</span>
                </a>
              </li>
              <?php }
              for ($i = 1; $i <= $final_page; $i++) {
                if ($page + 1 === $i) {
                  echo '<li class="page-item active"><a class="page-link" href="?page=' . $i . "&{$qs}" . '">' . $i . '</a></li>';
                }
                else {
                  echo '<li class="page-item"><a class="page-link" href="?page=' . $i . "&{$qs}" . '">' . $i . '</a></li>';
                }
              }
              if ($page + 1 < $final_page) { ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 2 . "&{$qs}" ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                  <span class="sr-only">Next</span>
                </a>
              </li>
              <?php } ?>
            </ul>
          </nav>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      $('#sortby').on('change', function() {
        $('#sortbyform').submit();
      });
      $('.show-building').on('click', function() {
        $.post('includes/show-hide-building.php', {action: $(this).data('action'), building_id: $(this).data('building_id')}, function(data) {
          console.log(data);
        })
      });
      $('.hide-building').on('click', function() {
        $.post('includes/show-hide-building.php', {action: $(this).data('action'), building_id: $(this).data('building_id')}, function(data) {
          console.log(data);
        })
      });
    </script>
  </body>
</html>