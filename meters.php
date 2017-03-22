<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
function gaugeURL($rv_id, $meter_id, $color, $bg, $height, $width, $font_family, $title, $title2, $border_radius, $rounding, $ver, $units) {
  $q = http_build_query(array(
    'rv_id' => $rv_id,
    'meter_id' => $meter_id,
    'color' => $color,
    'bg' => $bg,
    'height' => $height,
    'width' => $width,
    'font_family' => $font_family,
    'title' => $title,
    'title2' => $title2,
    'border_radius' => $border_radius,
    'rounding' => $rounding,
    'ver' => $ver,
    'units' => $units,
  ));
  return "http://104.131.103.232/".basename(dirname(__DIR__))."/gauges/gauge.php?" . $q;
}
function timeseriesURL2($meter_id, $dasharr1, $fill1, $meter_id2, $dasharr2, $fill2, $dasharr3, $fill3, $start, $ticks, $color1, $color2, $color3) {
  $q = http_build_query(array(
    'meter_id' => $meter_id,
    'dasharr1' => $dasharr1,
    'fill1' => $fill1,
    'meter_id2' => $meter_id2,
    'dasharr2' => $dasharr2,
    'fill2' => $fill2,
    'dasharr3' => $dasharr3,
    'fill3' => $fill3,
    'start' => $start,
    'ticks' => $ticks,
    'color1' => $color1,
    'color2' => $color2,
    'color3' => $color3
  ));
  return "http://{$_SERVER['HTTP_HOST']}/".explode('/', $_SERVER['REQUEST_URI'])[1]."/time-series/chart.php?" . $q;
}
/*
foreach ($db->query("SELECT * FROM gauges WHERE user_id = {$user_id} AND meter_id = {$meter['id']}") as $gauge) {
                    $url = gaugeURL($gauge['rv_id'], $gauge['meter_id'], $gauge['color'], $gauge['bg'], $gauge['height'], $gauge['width'], $gauge['font_family'], $gauge['title'], $gauge['title2'], $gauge['border_radius'], $gauge['rounding'], $gauge['ver'], $gauge['units']);
                    echo "<a href='{$url}' target='_blank'><li><iframe frameborder='0' height='{$gauge['height']}' width='{$gauge['width']}' src='{$url}'></iframe></li></a>";
                  }


----

foreach ($db->query("SELECT * FROM time_series_configs WHERE user_id = {$user_id} AND meter_id1 = {$meter['id']} OR meter_id2 = {$meter['id']}") as $timeseries) {
                    $url2 = timeseriesURL2($timeseries['meter_id1'], $timeseries['dasharr1'], $timeseries['fill1'], $timeseries['meter_id2'], $timeseries['dasharr2'], $timeseries['fill2'], $timeseries['dasharr3'], $timeseries['fill3'], $timeseries['start'], $timeseries['ticks'], $timeseries['color1'], $timeseries['color2'], $timeseries['color3']);
                    echo "<li><a href='{$url2}' target='_blank'>Open timeseries</a></li>";
                  }
 */
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>CWD Backend</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="clear: both;height: 40px"></div>
      <?php foreach ($db->query("SELECT id, name FROM buildings WHERE user_id = {$user_id} ORDER BY name ASC") as $building) { ?>
      <div class="row">
        <div class="col-sm-12">
          <h3><?php echo $building['name']; ?></h3>
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Meter name</th>
                <th>Gauges</th>
                <th>Time series</th>
                <th>Orb</th>
                <th>Relative value configuration</th>
                <th>Last updated</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($db->query("SELECT id, bos_uuid, name, last_updated, gauges_using, timeseries_using, for_orb FROM meters WHERE building_id = {$building['id']} ORDER BY gauges_using DESC, timeseries_using DESC, name ASC") as $meter) {
                echo "<tr>";
                echo "<td>{$meter['name']}</td>";
                echo $meter['gauges_using'] > 0 ? "<td><a href='#'>{$meter['gauges_using']} saved gauges</a></td>" : "<td class='text-muted'>No saved gauges</td>";
                echo $meter['timeseries_using'] > 0 ? "<td><a href='#'>{$meter['timeseries_using']} saved time series</a></td>" : "<td class='text-muted'>No saved time series</td>";
                if ($meter['for_orb'] > 0) {
                  $stmt = $db->prepare('SELECT INET_NTOA(ip) AS ip FROM orbs WHERE elec_uuid = ? OR water_uuid = ?');
                  $stmt->execute(array($meter['bos_uuid'], $meter['bos_uuid']));
                  $ip = $stmt->fetchColumn();
                  echo "<td>{$ip}</td>";
                } else {
                  echo "<td>-</td>";
                }
                $stmt = $db->prepare('SELECT grouping, relative_value FROM relative_values WHERE meter_uuid = ?');
                $stmt->execute(array($meter['bos_uuid']));
                $relative_values = $stmt->fetchAll();
                if (count($relative_values) === 0) {
                  echo "<td>No configurations</td>";
                } else {
                  echo "<td>";
                  foreach ($relative_values as $rv) {
                    echo "<p><code style='font-size:10px'>{$rv['grouping']}</code>&nbsp;<b>{$rv['relative_value']}</b></p>";
                  }
                  echo "</td>";
                }
                echo "<td>";
                $diff = time() - $meter['last_updated'];
                if ($diff <= 60) {
                  echo "{$diff} seconds ago";
                }
                elseif ($diff <= 3600) {
                  echo floor($diff/60) . ' minutes ago';
                }
                else {
                  echo 'over an hour ago';
                }
                echo "</td>";
                echo "</tr>";
              } ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php } ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script>
      function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
      }
    </script>
  </body>
</html>