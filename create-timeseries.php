<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require '../includes/class.BuildingOS.php';
require '../includes/class.Meter.php';
require 'includes/check-signed-in.php';

if (isset($_POST['submit'])) {
  $q = array(
    ':user_id' => $user_id,
    ':meter_id' => $_POST['meter_id'],
    ':dasharr1' => isset($_POST['dasharr1']) ? $_POST['dasharr1'] : null,
    ':fill1' => isset($_POST['fill1']) ? $_POST['fill1'] : null,
    ':meter_id2' => $_POST['meter_id2'],
    ':dasharr2' => isset($_POST['dasharr2']) ? $_POST['dasharr2'] : null,
    ':fill2' => isset($_POST['fill2']) ? $_POST['fill2'] : null,
    ':dasharr3' => isset($_POST['dasharr3']) ? $_POST['dasharr3'] : null,
    ':fill3' => isset($_POST['fill3']) ? $_POST['fill3'] : null,
    ':start' => $_POST['start'] ? $_POST['start'] : 0,
    ':ticks' => isset($_POST['ticks']) ? $_POST['ticks'] : 0,
    ':color1' => $_POST['color1'],
    ':color2' => $_POST['color2'],
    ':color3' => $_POST['color3'],
    ':label' => (!isset($_POST['title'])) ? null : $_POST['title']
  );
  $stmt = $db->prepare('INSERT INTO time_series_configs (user_id, meter_id, meter_id2, dasharr1, fill1, dasharr2, fill2, dasharr3, fill3, start, ticks, color1, color2, color3, label)
    VALUES (:user_id, :meter_id, :meter_id2, :dasharr1, :fill1, :dasharr2, :fill2, :dasharr3, :fill3, :start, :ticks, :color1, :color2, :color3, :label)');
  $stmt->execute($q);
  /*$api_id = $db->query("SELECT id FROM api WHERE user_id = {$user_id}")->fetchColumn();
  $meterClass = new Meter($db);
  foreach ($db->query('SELECT id, bos_uuid, url FROM meters WHERE ') as $meter) {
    $bos = new BuildingOS($db, $api_id);
    foreach ($resolutions as $res) {
      $bos->updateMeter($meter['id'], $meter['bos_uuid'], $meter_url, $res, $meterClass);
    }
  }*/
  if ($_POST['meter_id'] === $_POST['meter_id2']) {
    $stmt = $db->prepare('UPDATE meters SET timeseries_using = timeseries_using + 1 WHERE id = ?');
    $stmt->execute(array($_POST['meter_id']));
  } else {
    $stmt = $db->prepare('UPDATE meters SET timeseries_using = timeseries_using + 1 WHERE id = ?');
    $stmt->execute(array($_POST['meter_id']));
    $stmt = $db->prepare('UPDATE meters SET timeseries_using = timeseries_using + 1 WHERE id = ?');
    $stmt->execute(array($_POST['meter_id2']));
  }
}

$dropdown_html = '';
foreach ($db->query("SELECT * FROM buildings WHERE org_id IN (SELECT org_id FROM users_orgs_map WHERE user_id = {$user_id}) ORDER BY name ASC") as $building) {
  $stmt = $db->prepare('SELECT id, name FROM meters WHERE building_id = ? ORDER BY name');
  $stmt->execute(array($building['id']));
  $once = true;
  foreach($stmt->fetchAll() as $meter) {
    if ($once) {
      $once = false;
      $dropdown_html .= "<optgroup label='{$building['name']}'>";
    }
    $dropdown_html .= "<option value='{$meter['id']}'>{$meter['name']}</option>";
  }
  if (!$once) {
    $dropdown_html .= '</optgroup>';
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
    <title>Create time series</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
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
        <div class="col-xs-12 col-sm-12">
          <h1>Create time series URL</h1>
          <hr>
          <form action="" method="POST">
            <div class="form-group row">
              <label for="label" class="col-sm-3 form-control-label">Time series title</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="label" name="label">
                <small class="text-muted">If left blank will be the building name of the primary variable</small>
              </div>
            </div>
            <div class="form-group row">
              <label for="meter_id" class="col-sm-3 form-control-label">Primary variable</label>
              <div class="col-sm-9">
                <select style="width:100%" name="meter_id" id="meter_id" class="custom-select">
                  <?php echo $dropdown_html ?>
                </select>
                <input type="color" class="form-control" name="color1" value="#2ecc71" id="color1" style="margin-top:10px;margin-bottom:5px;height: 40px;padding: 0px;border: none">
                <label class="custom-control custom-checkbox">
                  <input id="dasharr1" name="dasharr1" type="checkbox" class="custom-control-input">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Dashed</span>
                </label>
                <input type="hidden" name="fill1" value="off">
                <label class="custom-control custom-checkbox">
                  <input id="fill1" name="fill1" type="checkbox" class="custom-control-input" checked>
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Filled</span>
                </label>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-3 form-control-label">Historical chart</label>
              <div class="col-sm-9">
                <input type="color" class="form-control" name="color2" value="#bdc3c7" id="color2" style="margin-bottom:5px;height: 40px;padding: 0px;border: none">
                <label class="custom-control custom-checkbox">
                  <input id="dasharr2" name="dasharr2" type="checkbox" class="custom-control-input">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Dashed</span>
                </label>
                <input type="hidden" name="fill2" value="off">
                <label class="custom-control custom-checkbox">
                  <input id="fill2" name="fill2" type="checkbox" class="custom-control-input" checked>
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Filled</span>
                </label>
              </div>
            </div>
            <div class="row">
              <div class="offset-sm-3 col-sm-9">
                <p class="text-center"><button class="btn btn-secondary" id="add-secondary">Add secondary variable</button></p>
              </div>
            </div>
            <div class="form-group row" id="secondary-variable" style="display: none">
              <label for="meter_id2" class="col-sm-3 form-control-label">Secondary variable</label>
              <div class="col-sm-9">
                <select style="width:100%" name="meter_id2" id="meter_id2" class="custom-select">
                  <?php echo $dropdown_html ?>
                </select>
                <input type="color" class="form-control" name="color3" value="#33A7FF" id="color3" style="margin-top:10px;margin-bottom:5px;height: 40px;padding: 0px;border: none">
                <label class="custom-control custom-checkbox">
                  <input id="dasharr3" name="dasharr3" type="checkbox" class="custom-control-input">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Dashed</span>
                </label>
                <input type="hidden" name="fill3" value="off">
                <label class="custom-control custom-checkbox">
                  <input id="fill3" name="fill3" type="checkbox" class="custom-control-input" checked>
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Filled</span>
                </label>
                <p><small><a href="#" id="remove-secondary">Remove secondary variable</a>.</small></p>
              </div>
            </div>
            <div class="form-group row">
              <label for="start" class="col-sm-3 form-control-label">Start Y-axis scale from</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="start" name="start">
                <small class="text-muted">If left blank the y-axis will be auto scaled</small>
              </div>
            </div>
            <div class="form-group row">
              <div class="offset-sm-3 col-sm-9">
                <label class="custom-control custom-checkbox">
                  <input id="ticks" name="ticks" type="checkbox" class="custom-control-input">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Show baseload and peak ticks</span>
                </label>
              </div>
            </div>
            <div class="form-group row">
              <div class="offset-sm-3 col-sm-9">
                <button type="submit" name="submit" id="submit" class="btn btn-primary">Save options</button>
              </div>
            </div>
          </form>
        </div>
        <!-- <div class="col-xs-12 col-sm-5">
          <h1>Preview</h1>
          <hr>
          <div id="preview-frame">
            <h2 class="text-center text-muted">Preview will appear here.</h2>
          </div>
        </div> -->
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>

    $('#add-secondary').on('click', function(e) {
      e.preventDefault();
      $('#secondary-variable').css('display', '');
      $(this).css('display', 'none');
    });

    $('#remove-secondary').on('click', function(e) {
      e.preventDefault();
      $('#secondary-variable').css('display', 'none');
      $('#add-secondary').css('display', '');
      $('#meter_id2').val($('#meter_id').val());
    });

    // function setPreview(qs) {
    //   console.log(qs);
    //   $('#preview-frame').html('<iframe frameborder="0" width="450px" height="450px" src="../time-series/chart.php?' + qs + '"></iframe>');
    // }

    // // http://stackoverflow.com/a/111545/2624391
    // function encodeQueryData(data) {
    //   var ret = [];
    //   for (var d in data)
    //     ret.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
    //   return ret.join("&");
    // }
    
    // $('#preview').on('click', function() {
    //   var data = {
    //     'meter_id': $('#meter_id').val(),
    //     'dasharr1': ($('#dasharr1').is(':checked') ? 'on' : 'off'),
    //     'fill1': ($('#fill1').is(':checked') ? 'on' : 'off'),
    //     'color1': $('#color1').val(),
    //     'meter_id2': $('#meter_id2').val(),
    //     'dasharr2': ($('#dasharr2').is(':checked') ? 'on' : 'off'),
    //     'fill2': ($('#fill2').is(':checked') ? 'on' : 'off'),
    //     'color3': $('#color3').val(),
    //     'dasharr3': ($('#dasharr3').is(':checked') ? 'on' : 'off'),
    //     'fill3': ($('#fill3').is(':checked') ? 'on' : 'off'),
    //     'color2': $('#color2').val(),
    //     'start': $('#start').val(),
    //     'ticks': ($('#ticks').is(':checked') ? 'on' : 'off')
    //   };
    //   setPreview(encodeQueryData(data));
    // });

    </script>
  </body>
</html>