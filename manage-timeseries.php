<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
function timeseriesURL($meter_id, $dasharr1, $fill1, $meter_id2, $dasharr2, $fill2, $dasharr3, $fill3, $start, $ticks) {
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
    'ticks' => $ticks
  ));
  return "http://{$_SERVER['HTTP_HOST']}/".basename(dirname(__DIR__))."/time-series/chart.php?" . $q;
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
          <table class="table">
            <thead>
              <tr>
                <th>&nbsp;</th>
                <th>URL</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <?php foreach ($db->query('SELECT * FROM time_series_configs') as $row) {
                  $url = timeseriesURL($row['meter_id1'], $row['dasharr1'], $row['fill1'], $row['meter_id2'], $row['dasharr2'], $row['fill2'], $row['dasharr3'], $row['fill3'], $row['start'], $row['ticks']);
                  echo "<td><object style='max-width:400px' type='image/svg+xml' data='{$url}'></object></td>\n";
                  echo "<td>{$url}</td>";
                } ?>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script>

    function setPreview(qs) {
      console.log(qs);
      $('#preview-frame').html('<iframe frameborder="0" width="450px" height="450px" src="<?php echo "http://{$_SERVER['HTTP_HOST']}/".basename(dirname(__DIR__))."/time-series/chart.php?"; ?>' + qs + '"></iframe>');
    }

    // http://stackoverflow.com/a/111545/2624391
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