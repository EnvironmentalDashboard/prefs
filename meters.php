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
// if (isset($_POST['refresh'])) {
//   $api_id = $db->query("SELECT api_id FROM users WHERE id = {$user_id}")->fetchColumn();
//   $resolutions = array('live', 'quarterhour', 'hour', 'month');
//   for ($i = 0; $i < count($resolutions); $i++) {
//     $stmt = $db->prepare('SELECT recorded FROM meter_data
//       WHERE meter_id = ? AND resolution = ? AND value IS NOT NULL
//       ORDER BY recorded DESC LIMIT 1');
//     $stmt->execute(array($_POST['meter_id'], $resolutions[$i]));
//     $amount = $stmt->fetchColumn();
//     // http://stackoverflow.com/a/3819422/2624391
//     exec('bash -c "exec nohup setsid php /var/www/html/oberlin/scripts/update-meter.php --api_id=\''.$api_id.
//       '\' --meter_id='.escapeshellarg($_POST['meter_id']).
//       ' --res=\''.$resolutions[$i].'\' --amount=\''.$amount.'\' > /dev/null 2>&1 &"');
//   }
// }
if (isset($_POST['delete-meter-id'])) {
  $stmt = $db->prepare('DELETE FROM meter_data WHERE meter_id = ? AND resolution = ?');
  $stmt->execute(array($_POST['delete-meter-id'], $_POST['delete-res']));
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
function time_ago($last_updated) {
  $diff = time() - $last_updated;
  if ($diff <= 60) {
    return "{$diff}s ago";
  }
  elseif ($diff <= 3600) {
    return floor($diff/60) . ' mins ago';
  }
  else {
    return 'over an hour ago';
  }
}
function name_that_grouping($grouping) {
  $grouping = json_decode($grouping, true);
  if ($grouping[0]['days'] == array(2,3,4,5,6) && $grouping[1]['days'] == array(1,7) ||
      $grouping[1]['days'] == array(2,3,4,5,6) && $grouping[0]['days'] == array(1,7)) { // lol, php
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <!-- Modal -->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modal-title" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modal-title">Loading</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="script_status">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

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
          <p>Clicking a relative value configuration will recalculate and display the relative value and update all identical configurations. Updating the database with the &quot;Sync data&quot; button will request all data from the BuildingOS API recorded since the last recording in the database. Deleting data for a meter can be used to view the result of a large API request.</p>
          <p>Each meter is updated every <?php echo $db->query('SELECT ROUND(AVG(UNIX_TIMESTAMP() - live_last_updated)/60, 2) AS minutes FROM meters WHERE (gauges_using > 0 OR for_orb > 0 OR orb_server > 0 OR timeseries_using > 0) AND user_id = '.intval($user_id))->fetchColumn(); ?> minutes.</p>
        </div>
        <div class="col-sm-4">
          <form action="" method="POST" id='sortbyform'>
            Show: 
            <select class="form-control" name="sortby" id="sortby">
              <option value="meters_collected">Meters data are collected for</option>
              <option value="ignored_meters">Meters data are not collected</option>
              <option value="all">All meters</option>
            </select>
          </form>
        </div>
      </div>
      <!-- <p style="font-size:13px"><span class="bg-success" style="height: 15px;width: 15px;display: inline-block;position: relative;top: 2px">&nbsp;</span> Data are being cached because a saved time series/gauge/old orb is using it or it is used by environmentalorb.org.</p>
      <p style="margin-bottom: 20px;font-size:13px"><span class="bg-inverse" style="height: 15px;width: 15px;display: inline-block;position: relative;top: 2px">&nbsp;</span> Data are not collected for this meter because no apps use it.</p> -->
      <?php foreach ($db->query("SELECT id, name FROM buildings WHERE user_id = {$user_id} ORDER BY name ASC") as $building) {
        if ($db->query("SELECT COUNT(*) FROM meters WHERE building_id = {$building['id']}")->fetchColumn() === '0') {
          continue;
        }
      ?>
      <div class="row">
        <div class="col-sm-12">
          <h3><?php echo $building['name']; ?></h3>
          <table class="table table-sm" style="overflow-x: scroll;">
            <thead>
              <tr>
                <th>Meter&nbsp;ID</th>
                <th>Meter&nbsp;name</th>
                <th>Relative value configuration</th>
                <th>BuildingOS&nbsp;data</th>
                <th>Delete data</th>
                <th>Gauges</th>
                <th>Time&nbsp;series</th>
                <th>Orb</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if (isset($_GET['sortby']) && $_GET['sortby'] === 'ignored_meters') {
                $sql = 'AND (gauges_using = 0 AND for_orb = 0 AND orb_server = 0 AND timeseries_using = 0) ';
              } elseif (isset($_GET['sortby']) && $_GET['sortby'] === 'all') {
                $sql = '';
              } else {
                $sql = 'AND (gauges_using > 0 OR for_orb > 0 OR orb_server > 0 OR timeseries_using > 0) ';
              }
              foreach ($db->query("SELECT id, user_id, bos_uuid, name, url, live_last_updated, quarterhour_last_updated, hour_last_updated, month_last_updated, gauges_using, timeseries_using, for_orb, orb_server FROM meters WHERE building_id = {$building['id']} {$sql}ORDER BY name ASC") as $meter) {
                echo "<tr>";
                echo "<td>{$meter['id']}</td>";
                echo "<td>{$meter['name']}</td>";
                $stmt = $db->prepare('SELECT grouping FROM relative_values WHERE meter_uuid = ?');
                $stmt->execute(array($meter['bos_uuid']));
                $relative_values = $stmt->fetchAll();
                if (count($relative_values) === 0) {
                  echo "<td>-</td>";
                } else {
                  echo "<td>";
                  foreach ($relative_values as $rv) {
                    echo "<button type='button' style='margin-bottom:10px;margin-right:10px' class='btn btn-sm btn-secondary' data-action='update-rv' data-meter_id='{$meter['id']}' data-grouping='{$rv['grouping']}' data-toggle='modal' data-target='#modal'>".name_that_grouping($rv['grouping'])."</button>";
                  }
                  echo "</td>";
                }
                echo "<td>
                <div class=\"dropdown\">
                  <button class=\"btn btn-secondary btn-sm dropdown-toggle\" type=\"button\" id=\"refresh-data-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                    Sync data
                  </button>
                  <div class=\"dropdown-menu\" aria-labelledby=\"refresh-data-toggle\">
                    <button type='button' class='dropdown-item' data-action='update-data' data-url='{$meter['url']}' data-user_id='{$meter['user_id']}' data-meter_uuid='{$meter['bos_uuid']}' data-meter_id='{$meter['id']}' data-resolution='live' data-toggle='modal' data-target='#modal'>Live (updated ".time_ago($meter['live_last_updated']).")</button>
                    <button type='button' class='dropdown-item' data-action='update-data' data-url='{$meter['url']}' data-user_id='{$meter['user_id']}' data-meter_uuid='{$meter['bos_uuid']}' data-meter_id='{$meter['id']}' data-resolution='quarterhour' data-toggle='modal' data-target='#modal'>Quarter hour (updated ".time_ago($meter['quarterhour_last_updated']).")</button>
                    <button type='button' class='dropdown-item' data-action='update-data' data-url='{$meter['url']}' data-user_id='{$meter['user_id']}' data-meter_uuid='{$meter['bos_uuid']}' data-meter_id='{$meter['id']}' data-resolution='hour' data-toggle='modal' data-target='#modal'>Hour (updated ".time_ago($meter['hour_last_updated']).")</button>
                    <button type='button' class='dropdown-item' data-action='update-data' data-url='{$meter['url']}' data-user_id='{$meter['user_id']}' data-meter_uuid='{$meter['bos_uuid']}' data-meter_id='{$meter['id']}' data-resolution='month' data-toggle='modal' data-target='#modal'>Month (updated ".time_ago($meter['month_last_updated']).")</button>
                  </div>
                </div>
                </td>";
                echo "<td>
                <div class=\"dropdown\">
                  <button class=\"btn btn-danger btn-sm dropdown-toggle\" type=\"button\" id=\"dropdownMenuButton\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">Delete data</button>
                  <div class=\"dropdown-menu\" aria-labelledby=\"dropdownMenuButton\">
                    <form action='' method='POST'>
                      <input type='hidden' name='delete-meter-id' value='{$meter['id']}'>
                      <input type='hidden' name='delete-res' value='live'>
                      <button type='submit' class='dropdown-item'>Delete live data</button>
                    </form>
                    <form action='' method='POST'>
                      <input type='hidden' name='delete-meter-id' value='{$meter['id']}'>
                      <input type='hidden' name='delete-res' value='quarterhour'>
                      <button type='submit' class='dropdown-item'>Delete quarterhour data</button>
                    </form>
                    <form action='' method='POST'>
                      <input type='hidden' name='delete-meter-id' value='{$meter['id']}'>
                      <input type='hidden' name='delete-res' value='hour'>
                      <button type='submit' class='dropdown-item'>Delete hour data</button>
                    </form>
                    <form action='' method='POST'>
                      <input type='hidden' name='delete-meter-id' value='{$meter['id']}'>
                      <input type='hidden' name='delete-res' value='month'>
                      <button type='submit' class='dropdown-item'>Delete month data</button>
                    </form>
                  </div>
                </div>
                </td>";
                echo $meter['gauges_using'] > 0 ? "<td><a href='#'>{$meter['gauges_using']} saved gauges</a></td>" : "<td class='text-muted'>-</td>";
                echo $meter['timeseries_using'] > 0 ? "<td><a href='#'>{$meter['timeseries_using']} saved time series</a></td>" : "<td class='text-muted'>-</td>";
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
      <?php } ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script>
      function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
      }
      $('#sortby').on('change', function() {
        $('#sortbyform').submit();
      });
      
      $('#modal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var modal = $(this);
        var insert_here = $('#script_status');
        insert_here.empty();
        if (action === 'update-rv') {
          $('#modal-title').text('Retrieving data from BuildingOS API');
          $.getJSON( "../scripts/update-meter-rv.php", {meter_id: button.data('meter_id'), grouping: JSON.stringify(button.data('grouping'))})
            .done(function( json ) {
              // console.log( "JSON Data: " + json );
              $('#modal-title').text('Data returned from BuildingOS API');
              $.each(json, function(key, value) {
                insert_here.append('<h6>'+key+'</h6>').append('<pre><code>'+value+'</code></pre>');
              });
            })
            .fail(function( jqxhr, textStatus, error ) {
              var err = textStatus + ", " + error;
              console.log( "Request Failed: " + err, jqxhr.responseText );
          });
        } else if (action === 'update-data') {
          $('#modal-title').text('Calculating relative value');
          $.getJSON( "../scripts/update-meter.php", {user_id: button.data('user_id'), meter_id: button.data('meter_id'), meter_uuid: button.data('meter_uuid'), meter_url: button.data('url'), res: button.data('resolution')})
            .done(function( json ) {
              // console.log( "JSON Data: " + json );
              $('#modal-title').text('Relative value calculation');
              $.each(json, function(key, value) {
                insert_here.append('<h6>'+key+'</h6>').append('<pre><code>'+value+'</code></pre>');
              });
            })
            .fail(function( jqxhr, textStatus, error ) {
              var err = textStatus + ", " + error;
              console.log( "Request Failed: " + err, jqxhr.responseText );
          });
        }
        // modal.find('.modal-title').text('New message to ' + recipient)
        // modal.find('.modal-body input').val(recipient)
      });
    </script>
  </body>
</html>