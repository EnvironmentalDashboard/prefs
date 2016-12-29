<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
function gaugeURL($meter_id, $data_interval, $color, $bg, $height, $width, $font_family, $title, $title2, $border_radius, $rounding, $ver, $units, $start) {
  $q = http_build_query(array(
    'meter_id' => $meter_id,
    'data_interval' => $data_interval,
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
    'start' => $start
  ));
  return "http://{$_SERVER['HTTP_HOST']}/oberlin/gauges/gauge.php?" . $q;
}
$default_color = '#ecf0f1';
$default_bg = '#2ecc71';
$default_height = '190';
$default_width = '290';
$default_font_family = 'Futura, Helvetica, sans-serif';
$default_border_radius = '3';
$default_precision = '1';
$default_ver = 'html';
$default_start = '-2 weeks';
if (isset($_POST['submit'])) {
  $q = array(
    ':meter_id' => $_POST['meter'],
    ':data_interval' => $_POST['data_interval'],
    ':color' => $_POST['color'],
    ':bg' => $_POST['bg'],
    ':height' => $_POST['height'],
    ':width' => $_POST['width'],
    ':font_family' => $_POST['font_family'],
    ':title' => $_POST['title'],
    ':title2' => $_POST['title2'],
    ':border_radius' => $_POST['border_radius'],
    ':rounding' => $_POST['rounding'],
    ':ver' => $_POST['radio'],
    ':units' => $_POST['units'],
    ':start' => $_POST['start']
  );
  $stmt = $db->prepare('INSERT INTO gauges (meter_id, data_interval, color, bg, height, width, font_family, title, title2, border_radius, rounding, ver, units, start)
    VALUES (:meter_id, :data_interval, :color, :bg, :height, :width, :font_family, :title, :title2, :border_radius, :rounding, :ver, :units, :start)');
  $stmt->execute($q);
  $stmt = $db->prepare('UPDATE meters SET num_using = num_using + 1 WHERE id = ?');
  $stmt->execute(array($_POST['meter']));
}

$buildings = $db->query('SELECT * FROM buildings');
$buildings = $buildings->fetchAll();
$num_buildings = count($buildings);
ob_start();
foreach($buildings as $building) {
  echo "'" . addslashes($building['name']) . "': [[";
  $stmt = $db->prepare('SELECT id, name FROM meters WHERE building_id = ?');
  $stmt->execute(array($building['id']));
  $meters = $stmt->fetchAll();
  $num_meters = count($meters);
  foreach($meters as $meter) {
    echo "'" . addslashes($meter['name']) . "'";
    if ($num_meters-- !== 1) {
      echo ",";
    }
  }
  echo "],[";
  $num_meters = count($meters);
  foreach($meters as $meter) {
    echo "'" . $meter['id'] . "'";
    if ($num_meters-- !== 1) {
      echo ",";
    }
  }
  echo "]]";
  if ($num_buildings-- !== 1) {
    echo ",";
  }
  echo "\n\n";
}
$javascript = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Create gauge</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/css/bootstrap.min.css" integrity="sha384-y3tfxAZXuh4HwSYylfB+J125MxIs6mR5FOHamPBG064zB+AFeWH94NdvaCBm8qnd" crossorigin="anonymous">
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
        <div class="col-xs-12 col-sm-7">
          <h1>Create a gauge</h1>
          <hr>
          <form action="" method="POST">
            <div class="form-group row">
              <label for="building" class="col-sm-3 form-control-label">Building name</label>
              <div class="col-sm-9">
                <select style="width:100%" name="building" id="building" class="c-select"></select>
              </div>
            </div>
            <div class="form-group row">
              <label for="meter" class="col-sm-3 form-control-label">Meter name</label>
              <div class="col-sm-9">
                <select style="width:100%" name="meter" id="meter" class="c-select"></select>
              </div>
            </div>
            <div class="form-group row">
              <label for="data_interval" class="col-sm-3 form-control-label">Data interval</label>
              <div class="col-sm-9">
                <select style="width:100%" name="data_interval" id="data_interval" class="c-select">
                  <option value="[1, 2, 3, 4, 5, 6, 7]">All days</option>
                  <option value="[2, 3, 4, 5, 6], [1, 7]">Group as [weekdays], [weekends]</option>
                  <option value="[2, 4, 6], [3, 5], [1, 7]">Group as [Monday, Wednesday, Friday], [Tuesday, Thursday], [Saturday, Sunday]</option>
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label for="title" class="col-sm-3 form-control-label">Gauge title</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="title" name="title" placeholder="Gauge title">
              </div>
            </div>
            <div class="form-group row">
              <label for="title2" class="col-sm-3 form-control-label">Gauge title line 2</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="title2" name="title2" placeholder="Gauge title (optional)">
              </div>
            </div>
            <div class="form-group row">
              <label for="color" class="col-sm-3 form-control-label">Color</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="color" name="color" placeholder="e.g. #ecf0f1" value="<?php echo $default_color; ?>" maxlength="10">
              </div>
            </div>
            <div class="form-group row">
              <label for="bg" class="col-sm-3 form-control-label">Background</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="bg" name="bg" placeholder="e.g. #2ecc71" value="<?php echo $default_bg; ?>" maxlength="10">
              </div>
            </div>
            <div class="form-group row">
              <label for="height" class="col-sm-3 form-control-label">Height</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="height" name="height" placeholder="e.g. 190" value="<?php echo $default_height; ?>" maxlength="5">
              </div>
            </div>
            <div class="form-group row">
              <label for="width" class="col-sm-3 form-control-label">Width</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="width" name="width" placeholder="e.g. 290" value="<?php echo $default_width; ?>" maxlength="5">
              </div>
            </div>
            <div class="form-group row">
              <label for="font_family" class="col-sm-3 form-control-label">Font family</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="font_family" name="font_family" placeholder="e.g. Helvetica" value="<?php echo $default_font_family; ?>">
              </div>
            </div>
            <div class="form-group row">
              <label for="border_radius" class="col-sm-3 form-control-label">Border radius</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="border_radius" name="border_radius" placeholder="e.g. 3" value="<?php echo $default_border_radius; ?>">
              </div>
            </div>
            <div class="form-group row">
              <label for="rounding" class="col-sm-3 form-control-label">Rounding precision</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="rounding" name="rounding" placeholder="e.g. 1" value="">
                <small class="text-muted">Leave blank to automatically round</small>
              </div>
            </div>
            <div class="form-group row">
              <label for="start" class="col-sm-3 form-control-label">Lengh of data</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="start" name="start" value="<?php echo $default_start; ?>">
                <small class="text-muted">How far back should the historical data go?</small>
              </div>
            </div>
            <div class="form-group row">
              <label for="units" class="col-sm-3 form-control-label">Units</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="units" name="units">
                <small class="text-muted">If left blank the default will be used.</small>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-offset-3 col-sm-9">
                <label class="c-input c-radio">
                  <input id="html" value="html" name="radio" type="radio"<?php echo ($default_ver === 'html') ? ' checked' : ''; ?>>
                  <span class="c-indicator"></span>
                  HTML version
                </label>
                <label class="c-input c-radio">
                  <input id="svg" value="svg" name="radio" type="radio"<?php echo ($default_ver === 'svg') ? ' checked' : ''; ?>>
                  <span class="c-indicator"></span>
                  SVG version
                </label>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-offset-3 col-sm-9">
                <a href="#" id="preview" class="btn btn-secondary">Preview gauge</a>
                <button type="submit" name="submit" class="btn btn-primary">Save gauge</button>
              </div>
            </div>
          </form>
        </div>
        <div class="col-xs-12 col-sm-5">
          <h1>Preview</h1>
          <hr>
          <div id="preview-frame">
            <h2 class="text-xs-center text-muted">Preview will appear here.</h2>
          </div>
        </div>
      </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/js/bootstrap.min.js" integrity="sha384-vZ2WRJMwsjRMW/8U7i6PWi6AlO1L79snBrmgiDpgIWJ82z8eA5lenwvxbMV1PAh7" crossorigin="anonymous"></script>
<script>
// THE JS HERE IS A BIT MESSY AND ALSO NOT ALL JQUERY... //
// http://stackoverflow.com/questions/29802104/javascript-change-drop-down-box-options-based-on-another-drop-down-box-value
var buildings = {
<?php echo $javascript; ?>
},
building_select = document.querySelector('#building'),
meter_select = document.querySelector('#meter');

setOptions(building_select, Object.keys(buildings), Object.keys(buildings));
setOptions(meter_select, buildings[building_select.value][0], buildings[building_select.value][1]);
building_select.addEventListener('change', function() {
  setOptions(meter_select, buildings[building_select.value][0], buildings[building_select.value][1]);
});

function setOptions(dropDown, options, value) {
  dropDown.innerHTML = '';
  for (var i = 0; i < options.length; i++) {
    dropDown.innerHTML += '<option value="' + value[i] + '">' + options[i] + '</option>';
  }
}


function setGauge(qs) {
  $('#preview-frame').html('<iframe frameborder="0" height="' + $("#height").val() + '" width="' + $("#width").val() + '" src="<?php echo "http://{$_SERVER['HTTP_HOST']}/gauges/gauge.php?"; ?>' + qs + '"></iframe>');
}

// http://stackoverflow.com/a/111545/2624391
function encodeQueryData(data) {
  var ret = [];
  for (var d in data)
    ret.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
  return ret.join("&");
}

document.getElementById("preview").addEventListener("click", function() {
  var e = document.getElementById("meter");
  var data = {
    'meter_id': e.options[e.selectedIndex].value,
    'color': $("#color").val(),
    'bg': $("#bg").val(),
    'height': $("#height").val(),
    'width': $("#width").val(),
    'title': $("#title").val(),
    'title2': $("#title2").val(),
    'font_family': $("#font_family").val(),
    'units': $("#units").val(),
    'ver': $('input[name="radio"]:checked').val(),
    'start': $("#start").val()
  };
  console.log(data);
  setGauge(encodeQueryData(data));
});
</script>
  </body>
</html>