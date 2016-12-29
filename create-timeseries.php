<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Create Time Series</title>
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
      <div style="clear: both;height: 20px"></div>
      <p><a href="http://104.131.103.232/oberlin/time-series/index.php?meter_id=325&meter_id2=258&time=today&start=&step=&name=Chart+name+here&name2=&height=400&width=1200&color1=%232ecc71&color2=%23bdc3c7&color3=%2333A7FF&color4=%23f39c12&dasharr1=false&dasharr2=false&dasharr3=false&dasharr4=false&fill1=true&fill2=true&fill3=true&fill4=true">time series</a></p>
      <!--
      <div class="row">
        <div class="col-xs-12">
          <h1>Create a time series</h1>
          <hr>
          <div class="row">
            <form action="" method="POST">
              <div class="col-sm-6">
                <div class="form-group">
                  <label for="time" class="form-control-label">Time frame</label>
                  <select style="width:100%" name="time" id="time" class="c-select">
                    <option value="live">Live (current hour)</option>
                    <option value="today">Today</option>
                    <option value="week">Week</option>
                    <option value="month">Month</option>
                    <option value="year">Year</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="name" class="form-control-label">Variable name</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Electricity Use">
                </div>
                <div class="form-group">
                  <label for="building" class="form-control-label">Building name</label>
                  <select style="width:100%" name="building" id="building" class="c-select"></select>
                </div>
                <div class="form-group">
                  <label for="meter" class="form-control-label">Meter name</label>
                  <select style="width:100%" name="meter" id="meter" class="c-select"></select>
                </div>
                <div class="form-group">
                  <label for="name2" class="form-control-label">Second variable name</label>
                  <input type="text" class="form-control" id="name2" name="name2" placeholder="e.g. Outdoor Temperature">
                </div>
                <div class="form-group">
                  <label for="building2" class="form-control-label">Second variable building</label>
                  <select style="width:100%" name="building2" id="building2" class="c-select"></select>
                </div>
                <div class="form-group">
                  <label for="meter2" class="form-control-label">Second variable meter</label>
                  <select style="width:100%" name="meter2" id="meter2" class="c-select"></select>
                </div>
                <div class="form-group">
                  <!- This is the rounding of the big number on the right ->
                  <label for="rounding" class="form-control-label">Rounding precision</label>
                  <input type="text" class="form-control" id="rounding" name="rounding" placeholder="e.g. 2">
                </div>
                <a href="#" id="url" class="btn btn-primary" style="margin-top:20px;margin-bottom:20px">Get URL</a>
              </div>
              <div class="col-sm-6">
                <div class="form-group">
                  <label for="height" class="form-control-label">Height</label>
                  <input type="text" class="form-control" id="height" name="height" placeholder="e.g. 400" value="400">
                </div>
                <div class="form-group">
                  <label for="width" class="form-control-label">Width</label>
                  <input type="text" class="form-control" id="width" name="width" placeholder="e.g. 1200" value="1200">
                </div>
                <div class="form-group">
                  <label for="primary-color" class="form-control-label">Primary chart color</label>
                  <input type="text" class="form-control" id="primary-color" name="primary-color" placeholder="e.g. #2ecc71" value="#2ecc71">
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="dasharr1" id="dasharr1">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Dashed line</span>
                  </label>
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="fill1" id="fill1" checked="">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Filled</span>
                  </label>
                </div>
                <div class="form-group">
                  <label for="historical-color" class="form-control-label">Historical chart color</label>
                  <input type="text" class="form-control" id="historical-color" name="historical-color" placeholder="e.g. #bdc3c7" value="#bdc3c7">
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="dasharr2" id="dasharr2">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Dashed line</span>
                  </label>
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="fill2" id="fill2" checked="">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Filled</span>
                  </label>
                </div>
                <div class="form-group">
                  <label for="second-color" class="form-control-label">Second chart color</label>
                  <input type="text" class="form-control" id="second-color" name="second-color" placeholder="e.g. #33A7FF" value="#33A7FF">
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="dasharr3" id="dasharr3">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Dashed line</span>
                  </label>
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="fill3" id="fill3" checked="">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Filled</span>
                  </label>
                </div>
                <div class="form-group">
                  <label for="typcial-color" class="form-control-label">Typical chart color</label>
                  <input type="text" class="form-control" id="typcial-color" name="typcial-color" placeholder="e.g. #f39c12" value="#f39c12">
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="dasharr4" id="dasharr4">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Dashed line</span>
                  </label>
                  <label class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="fill4" id="fill4" checked="">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Filled</span>
                  </label>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      -->
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/js/bootstrap.min.js" integrity="sha384-vZ2WRJMwsjRMW/8U7i6PWi6AlO1L79snBrmgiDpgIWJ82z8eA5lenwvxbMV1PAh7" crossorigin="anonymous"></script>
<script>
// http://stackoverflow.com/questions/29802104/javascript-change-drop-down-box-options-based-on-another-drop-down-box-value
var buildings = {
<?php
$buildings = $db->query('SELECT * FROM buildings WHERE id IN (SELECT building_id FROM meters WHERE num_using > 0)');
$buildings = $buildings->fetchAll();
$num_buildings = count($buildings);
foreach($buildings as $building) {
  echo "'" . addslashes($building['name']) . "': [[";
  $stmt = $db->prepare('SELECT name, id FROM meters WHERE building_id = ? AND num_using > 0');
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
    echo "'" . addslashes($meter['id']) . "'";
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
?>
},
building_select = document.querySelector('#building'),
meter_select = document.querySelector('#meter'),
building_select2 = document.querySelector('#building2'),
meter_select2 = document.querySelector('#meter2');

setOptions(building_select, Object.keys(buildings), Object.keys(buildings));
setOptions(meter_select, buildings[building_select.value][0], buildings[building_select.value][1]);
building_select.addEventListener('change', function() {
  setOptions(meter_select, buildings[building_select.value][0], buildings[building_select.value][1]);
});

setOptions(building_select2, Object.keys(buildings), Object.keys(buildings));
setOptions(meter_select2, buildings[building_select2.value][0], buildings[building_select2.value][1]);
building_select2.addEventListener('change', function() {
  setOptions(meter_select2, buildings[building_select2.value][0], buildings[building_select2.value][1]);
});

function setOptions(dropDown, options, value) {
  dropDown.innerHTML = '';
  for (var i = 0; i < options.length; i++) {
    dropDown.innerHTML += '<option value="' + value[i] + '">' + options[i] + '</option>';
  }
}


// function setGauge(qs) {
//   $('#preview-frame').html('<img class="img-fluid" src="<?php echo "http://{$_SERVER['HTTP_HOST']}/time-series?"; ?>' + qs + '" />');
//   // var iframe = document.querySelector('#preview-frame');
//   // iframe.innerHTML = '';
//   // iframe.innerHTML = '<iframe frameborder="0" src="<?php //echo "http://{$_SERVER['HTTP_HOST']}/includes/gauge.php?"; ?>' + qs + '"></iframe>';
// }

// http://stackoverflow.com/a/111545/2624391
function encodeQueryData(data) {
  var ret = [];
  for (var d in data)
    ret.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
  return ret.join("&");
}

// document.getElementById("preview").addEventListener("click", function() {
//   var e = document.getElementById("meter"),
//       e2 = document.getElementById("meter2"),
//       e3 = document.getElementById("time");
//   var data = {
//     'meter_id': e.options[e.selectedIndex].value,
//     'meter_id2': e2.options[e2.selectedIndex].value,
//     'name': $('#name').val(),
//     'name2': $('#name2').val(),
//     'height': $("#height").val(),
//     'width': $("#width").val(),
//     'time': e3.options[e3.selectedIndex].value,
//     'color1': $('#primary-color').val(),
//     'color2': $('#historical-color').val(),
//     'color3': $('#second-color').val(),
//     'color4': $('#typcial-color').val(),
//     'dasharr': $('#dasharr').is(':checked')
//   };
//   setGauge(encodeQueryData(data));
// });

$( "#url" ).on( "click", function() {
  var e = document.getElementById("meter"),
      e2 = document.getElementById("meter2"),
      e3 = document.getElementById("time");
  var data = {
    'meter_id': e.options[e.selectedIndex].value,
    'meter_id2': e2.options[e2.selectedIndex].value,
    'name': $('#name').val(),
    'name2': $('#name2').val(),
    'height': $("#height").val(),
    'width': $("#width").val(),
    'time': e3.options[e3.selectedIndex].value,
    'color1': $('#primary-color').val(),
    'color2': $('#historical-color').val(),
    'color3': $('#second-color').val(),
    'color4': $('#typcial-color').val(),
    'dasharr1': $('#dasharr1').is(':checked'),
    'dasharr2': $('#dasharr2').is(':checked'),
    'dasharr3': $('#dasharr3').is(':checked'),
    'dasharr4': $('#dasharr4').is(':checked'),
    'fill1': $('#fill1').is(':checked'),
    'fill2': $('#fill2').is(':checked'),
    'fill3': $('#fill3').is(':checked'),
    'fill4': $('#fill4').is(':checked'),
    // 'rounding': $('#rounding').val()
  };
  window.prompt("Ctrl+C to copy", "<?php echo "http://{$_SERVER['HTTP_HOST']}/time-series?"; ?>" + encodeQueryData(data));
});
</script>
  </body>
</html>