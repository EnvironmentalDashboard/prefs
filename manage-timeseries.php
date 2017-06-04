<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
function timeseriesURL($meter_id, $dasharr1, $fill1, $meter_id2, $dasharr2, $fill2, $dasharr3, $fill3, $start, $ticks, $color1, $color2, $color3) {
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
  return "//{$_SERVER['HTTP_HOST']}/".explode('/', $_SERVER['REQUEST_URI'])[1]."/time-series/chart.php?" . $q;
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
  return "//{$_SERVER['HTTP_HOST']}/".explode('/', $_SERVER['REQUEST_URI'])[1]."/time-series/index.php?" . $q;
}
if (isset($_POST['submit'])) {
  $stmt = $db->prepare('DELETE FROM time_series_configs WHERE id = ?');
  $stmt->execute(array($_POST['id']));
  $stmt = $db->prepare('UPDATE meters SET timeseries_using = timeseries_using - 1 WHERE id = ?');
  $stmt->execute(array($_POST['meter_id1']));
  if ($_POST['meter_id1'] !== $_POST['meter_id2']) {
    $stmt = $db->prepare('UPDATE meters SET timeseries_using = timeseries_using - 1 WHERE id = ?');
    $stmt->execute(array($_POST['meter_id2']));
  }
}
if (isset($_POST['refresh'])) {
  $api_id = $db->query("SELECT api_id FROM users WHERE id = {$user_id}")->fetchColumn();
  $resolutions = array('live', 'quarterhour', 'hour', 'month');
  for ($i = 0; $i < count($resolutions); $i++) {
    $stmt = $db->prepare('SELECT recorded FROM meter_data
      WHERE meter_id = ? AND resolution = ? AND value IS NOT NULL
      ORDER BY recorded DESC LIMIT 1');
    $stmt->execute(array($_POST['meter_id1'], $resolutions[$i]));
    $amount = $stmt->fetchColumn();
    // http://stackoverflow.com/a/3819422/2624391
    exec('bash -c "exec nohup setsid php /var/www/html/oberlin/scripts/update-meter.php --api_id=\''.$api_id.
      '\' --meter_id='.escapeshellarg($_POST['meter_id1']).
      ' --res=\''.$resolutions[$i].'\' --amount=\''.$amount.'\' > /dev/null 2>&1 &"');
    if ($_POST['meter_id1'] !== $_POST['meter_id2']) {
      $stmt = $db->prepare('SELECT recorded FROM meter_data
      WHERE meter_id = ? AND resolution = ? AND value IS NOT NULL
      ORDER BY recorded DESC LIMIT 1');
      $stmt->execute(array($_POST['meter_id2'], $resolutions[$i]));
      $amount = $stmt->fetchColumn();
      exec('bash -c "exec nohup setsid php /var/www/html/oberlin/scripts/update-meter.php --api_id=\''.$api_id.
      '\' --meter_id='.escapeshellarg($_POST['meter_id2']).
      ' --res=\''.$resolutions[$i].'\' --amount=\''.$amount.'\' > /dev/null 2>&1 &"');
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Manage time series</title>
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
      <div style="clear:both;height:20px"></div>
      <div class="row">
        <div class="col-xs-12">
          <table class="table table-responsive table-sm">
            <thead>
              <tr>
                <th>Title</th>
                <th>Raw chart</th>
                <th>URLs</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $page = (empty($_GET['page'])) ? 0 : intval($_GET['page']) - 1;
              $count = $db->query("SELECT COUNT(*) FROM time_series_configs WHERE user_id = {$user_id}")->fetchColumn();
              $limit = 5;
              $offset = $limit * $page;
              $final_page = ceil($count / $limit);
              foreach ($db->query("SELECT * FROM time_series_configs WHERE user_id = {$user_id} ORDER BY id ASC LIMIT {$offset}, {$limit}") as $row) {
                echo "<tr>";
                  echo '<td>';
                  echo $db->query("SELECT buildings.name FROM buildings WHERE user_id = {$user_id} AND buildings.id IN (SELECT meters.building_id FROM meters WHERE meters.id = {$row['meter_id1']}) LIMIT 1")->fetchColumn() . ' ';
                  echo $db->query("SELECT name FROM meters WHERE id = {$row['meter_id1']}")->fetchColumn();
                  if ($row['meter_id1'] !== $row['meter_id2']) {
                    echo ' vs. ';
                    echo $db->query("SELECT buildings.name FROM buildings WHERE user_id = {$user_id} AND buildings.id IN (SELECT meters.building_id FROM meters WHERE meters.id = {$row['meter_id2']}) LIMIT 1")->fetchColumn() . ' ';
                    echo $db->query("SELECT name FROM meters WHERE id = {$row['meter_id2']}")->fetchColumn();
                  }
                  echo '</td>';
                  $url = timeseriesURL($row['meter_id1'], $row['dasharr1'], $row['fill1'], $row['meter_id2'], $row['dasharr2'], $row['fill2'], $row['dasharr3'], $row['fill3'], $row['start'], $row['ticks'], $row['color1'], $row['color2'], $row['color3']);
                  $url2 = timeseriesURL2($row['meter_id1'], $row['dasharr1'], $row['fill1'], $row['meter_id2'], $row['dasharr2'], $row['fill2'], $row['dasharr3'], $row['fill3'], $row['start'], $row['ticks'], $row['color1'], $row['color2'], $row['color3']);
                  echo "<td><object style='max-width:400px' type='image/svg+xml' data='{$url}'></object></td>\n";
                  echo "<td>
                  <p><a href='{$url2}' target='_blank'>Webpage with time series and title</a></p>
                  <p><a href='{$url2}&webpage=notitle' target='_blank'>Blank webpage with timeseries (resizeable)</a></p>
                  <p><a href='{$url}' target='_blank'>Raw SVG chart</a></p>
                  </td>";
                  echo "<td><a onclick=\"javascript:alert('not yet');\" class='btn btn-secondary'>Edit</a></td>";
                  echo "<td>
                        <form action='' method='POST'>
                        <input type='hidden' name='meter_id1' value='{$row['meter_id1']}'>
                        <input type='hidden' name='meter_id2' value='{$row['meter_id2']}'>
                        <input type='submit' class='btn btn-secondary' value='Refresh data' name='refresh'>
                        </form>
                        </td>";
                  echo "<td>
                        <form action='' method='POST'>
                        <input type='hidden' name='id' value='{$row['id']}'>
                        <input type='hidden' name='meter_id1' value='{$row['meter_id1']}'>
                        <input type='hidden' name='meter_id2' value='{$row['meter_id2']}'>
                        <input type='submit' class='btn btn-danger' value='Delete' name='submit'>
                        </form>
                        </td>";
                echo "</tr>";
              } ?>
            </tbody>
          </table>
          <nav aria-label="Page navigation" class="text-center">
            <ul class="pagination pagination-lg" style="display: inline-flex">
              <?php if ($page > 0) { ?>
              <li class="page-item">
                <a class="page-link" href="?sort=<?php echo $_GET['sort'] ?>&page=<?php echo $page ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                  <span class="sr-only">Previous</span>
                </a>
              </li>
              <?php }
              for ($i = 1; $i <= $final_page; $i++) {
                if ($page + 1 === $i) {
                  echo '<li class="page-item active"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                }
                else {
                  echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                }
              }
              if ($page + 1 < $final_page) { ?>
              <li class="page-item">
                <a class="page-link" href="?sort=<?php echo $_GET['sort'] ?>&page=<?php echo $page + 2 ?>" aria-label="Next">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script>

    function setPreview(qs) {
      console.log(qs);
      $('#preview-frame').html('<iframe frameborder="0" width="450px" height="450px" src="<?php echo "//{$_SERVER['HTTP_HOST']}/".basename(dirname(__DIR__))."/time-series/chart.php?"; ?>' + qs + '"></iframe>');
    }

    // stackoverflow.com/a/111545/2624391
    function encodeQueryData(data) {
      var ret = [];
      for (var d in data)
        ret.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
      return ret.join("&");
    }
    
    $('#preview').on('click', function() {
      var data = {
        'meter_id': $('#meter_id').val(),
        'dasharr1': ($('#dasharr1').is(':checked') ? 'on' : 'off'),
        'fill1': ($('#fill1').is(':checked') ? 'on' : 'off'),
        'color1': $('#color1').val(),
        'meter_id2': $('#meter_id2').val(),
        'dasharr2': ($('#dasharr2').is(':checked') ? 'on' : 'off'),
        'fill2': ($('#fill2').is(':checked') ? 'on' : 'off'),
        'color3': $('#color3').val(),
        'dasharr3': ($('#dasharr3').is(':checked') ? 'on' : 'off'),
        'fill3': ($('#fill3').is(':checked') ? 'on' : 'off'),
        'color2': $('#color2').val(),
        'start': $('#start').val(),
        'ticks': ($('#ticks').is(':checked') ? 'on' : 'off')
      };
      setPreview(encodeQueryData(data));
    });

    </script>
  </body>
</html>