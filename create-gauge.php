<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';

$default_color = '#ecf0f1';
$default_bg = '#2ecc71';
$default_height = '190';
$default_width = '290';
$default_font_family = 'Futura, Helvetica, sans-serif';
$default_border_radius = '3';
$default_precision = '1';
$default_ver = 'html';
if (isset($_POST['submit'])) {
  if (empty($_POST['existing_configs'])) {
    die('You must select a relative value configuration');
  }
  $q = array(
    ':user_id' => $user_id,
    ':rv_id' => $_POST['existing_configs'],
    ':meter_id' => $_POST['meter'],
    ':color' => $_POST['color'],
    ':bg' => $_POST['bg'],
    ':height' => $_POST['height'],
    ':width' => $_POST['width'],
    ':font_family' => $_POST['font_family'],
    ':title' => $_POST['title'],
    ':title2' => $_POST['title2'],
    ':border_radius' => $_POST['border_radius'],
    ':rounding' => ($_POST['rounding'] == '') ? null : $_POST['rounding'],
    ':ver' => $_POST['radio'],
    ':units' => $_POST['units']
  );
  $stmt = $db->prepare('INSERT INTO gauges (user_id, rv_id, meter_id, color, bg, height, width, font_family, title, title2, border_radius, rounding, ver, units)
    VALUES (:user_id, :rv_id, :meter_id, :color, :bg, :height, :width, :font_family, :title, :title2, :border_radius, :rounding, :ver, :units)');
  $stmt->execute($q);
  $stmt = $db->prepare('UPDATE meters SET gauges_using = gauges_using + 1 WHERE id = ?');
  $stmt->execute(array($_POST['meter']));
}

$buildings = $db->query("SELECT * FROM buildings WHERE org_id IN (SELECT org_id FROM users_orgs_map WHERE user_id = {$user_id})");
$buildings = $buildings->fetchAll();
$num_buildings = count($buildings);
// var_dump($buildings);
ob_start();
foreach($buildings as $building) {
  $stmt = $db->prepare('SELECT id, name FROM meters WHERE building_id = ?');
  $stmt->execute(array($building['id']));
  $meters = $stmt->fetchAll();
  $num_meters = count($meters);
  if ($num_meters === 0) {
    continue;
  }
  echo "'" . addslashes($building['name']) . "': [[";
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">

    <!-- Modal -->
    <div class="modal fade" id="rv_modal" tabindex="-1" role="dialog" aria-labelledby="rv_modal_label" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <form action="includes/add-rv-config.php" id="modal_form">
            <div class="modal-header">
              <h5 class="modal-title" id="rv_modal_label">Customize relative value</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              <table class="table">
                <thead>
                  <tr>
                    <th>&nbsp;</th>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tue</th>
                    <th>Wed</th>
                    <th>Thur</th>
                    <th>Fri</th>
                    <th>Sat</th>
                    <th>Go back by</th>
                    <th>Go back amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th scope="row">Group 1</th>
                    <td><input type="radio" value="1" name="sun"></td>
                    <td><input type="radio" value="1" name="mon" checked></td>
                    <td><input type="radio" value="1" name="tue" checked></td>
                    <td><input type="radio" value="1" name="wed" checked></td>
                    <td><input type="radio" value="1" name="thur" checked></td>
                    <td><input type="radio" value="1" name="fri" checked></td>
                    <td><input type="radio" value="1" name="sat"></td>
                    <td>
                      <select class="form-control go-back-by" name="go_back_by1">
                        <option value="npoints">a number of points</option>
                        <option value="start">a fixed amount of time</option>
                      </select>
                    </td>
                    <td><input type="text" name="amount1" class="form-control" value="7"></td>
                  </tr>
                  <tr>
                    <th scope="row">Group 2</th>
                    <td><input type="radio" value="2" name="sun" checked></td>
                    <td><input type="radio" value="2" name="mon"></td>
                    <td><input type="radio" value="2" name="tue"></td>
                    <td><input type="radio" value="2" name="wed"></td>
                    <td><input type="radio" value="2" name="thur"></td>
                    <td><input type="radio" value="2" name="fri"></td>
                    <td><input type="radio" value="2" name="sat"  checked></td>
                    <td>
                      <select class="form-control go-back-by" name="go_back_by2">
                        <option value="npoints">a number of points</option>
                        <option value="start">a fixed amount of time</option>
                      </select>
                    </td>
                    <td><input type="text" name="amount2" class="form-control" value="5"></td>
                  </tr>
                  <tr>
                    <th scope="row">Group 3</th>
                    <td><input type="radio" value="3" name="sun"></td>
                    <td><input type="radio" value="3" name="mon"></td>
                    <td><input type="radio" value="3" name="tue"></td>
                    <td><input type="radio" value="3" name="wed"></td>
                    <td><input type="radio" value="3" name="thur"></td>
                    <td><input type="radio" value="3" name="fri"></td>
                    <td><input type="radio" value="3" name="sat"></td>
                    <td>
                      <select class="form-control go-back-by" name="go_back_by3">
                        <option value="npoints">a number of points</option>
                        <option value="start">a fixed amount of time</option>
                      </select>
                    </td>
                    <td><input type="text" name="amount3" class="form-control"></td>
                  </tr>
                  <tr>
                    <th scope="row">Group 4</th>
                    <td><input type="radio" value="4" name="sun"></td>
                    <td><input type="radio" value="4" name="mon"></td>
                    <td><input type="radio" value="4" name="tue"></td>
                    <td><input type="radio" value="4" name="wed"></td>
                    <td><input type="radio" value="4" name="thur"></td>
                    <td><input type="radio" value="4" name="fri"></td>
                    <td><input type="radio" value="4" name="sat"></td>
                    <td>
                      <select class="form-control go-back-by" name="go_back_by4">
                        <option value="npoints">a number of points</option>
                        <option value="start">a fixed amount of time</option>
                      </select>
                    </td>
                    <td><input type="text" name="amount4" class="form-control"></td>
                  </tr>
                  <tr>
                    <th scope="row">Group 5</th>
                    <td><input type="radio" value="5" name="sun"></td>
                    <td><input type="radio" value="5" name="mon"></td>
                    <td><input type="radio" value="5" name="tue"></td>
                    <td><input type="radio" value="5" name="wed"></td>
                    <td><input type="radio" value="5" name="thur"></td>
                    <td><input type="radio" value="5" name="fri"></td>
                    <td><input type="radio" value="5" name="sat"></td>
                    <td>
                      <select class="form-control go-back-by" name="go_back_by5">
                        <option value="npoints">a number of points</option>
                        <option value="start">a fixed amount of time</option>
                      </select>
                    </td>
                    <td><input type="text" name="amount5" class="form-control"></td>
                  </tr>
                  <tr>
                    <th scope="row">Group 6</th>
                    <td><input type="radio" value="6" name="sun"></td>
                    <td><input type="radio" value="6" name="mon"></td>
                    <td><input type="radio" value="6" name="tue"></td>
                    <td><input type="radio" value="6" name="wed"></td>
                    <td><input type="radio" value="6" name="thur"></td>
                    <td><input type="radio" value="6" name="fri"></td>
                    <td><input type="radio" value="6" name="sat"></td>
                    <td>
                      <select class="form-control go-back-by" name="go_back_by6">
                        <option value="npoints">a number of points</option>
                        <option value="start">a fixed amount of time</option>
                      </select>
                    </td>
                    <td><input type="text" name="amount6" class="form-control"></td>
                  </tr>
                  <tr>
                    <th scope="row">Group 7</th>
                    <td><input type="radio" value="7" name="sun"></td>
                    <td><input type="radio" value="7" name="mon"></td>
                    <td><input type="radio" value="7" name="tue"></td>
                    <td><input type="radio" value="7" name="wed"></td>
                    <td><input type="radio" value="7" name="thur"></td>
                    <td><input type="radio" value="7" name="fri"></td>
                    <td><input type="radio" value="7" name="sat"></td>
                    <td>
                      <select class="form-control go-back-by" name="go_back_by7">
                        <option value="npoints">a number of points</option>
                        <option value="start">a fixed amount of time</option>
                      </select>
                    </td>
                    <td><input type="text" name="amount7" class="form-control"></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="modal-footer">
              <input type="hidden" name="rv_meter_id" id="rv_meter_id" value="">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
          </form>
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
      <div style="clear:both;height:20px"></div>
      <div class="row">
        <div class="col-xs-12 col-sm-7">
          <h1>Create a gauge</h1>
          <hr>
          <form action="" method="POST">
            <div class="form-group row">
              <label for="building" class="col-sm-3 form-control-label">Building name</label>
              <div class="col-sm-9">
                <select style="width:100%" name="building" id="building" class="custom-select"></select>
              </div>
            </div>
            <div class="form-group row">
              <label for="meter" class="col-sm-3 form-control-label">Meter name</label>
              <div class="col-sm-9">
                <select style="width:100%" name="meter" id="meter" class="custom-select"></select>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-sm-3"><p>Select relative value configuration</p></div>
              <div class="col-sm-9">
                <div id="radios"></div>
                <button type="button" id="modal_btn" class="btn btn-primary btn-block" data-toggle="modal" data-id="" data-target="#rv_modal">Customize relative value calculation</button>
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
                <input type="color" class="form-control" id="color" name="color" placeholder="e.g. #ecf0f1" value="<?php echo $default_color; ?>" maxlength="10" style="height:40px;padding: 0px;border: none">
              </div>
            </div>
            <div class="form-group row">
              <label for="bg" class="col-sm-3 form-control-label">Background</label>
              <div class="col-sm-9">
                <input type="color" class="form-control" id="bg" name="bg" placeholder="e.g. #2ecc71" value="<?php echo $default_bg; ?>" maxlength="10" style="height:40px;padding: 0px;border: none">
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
              <label for="units" class="col-sm-3 form-control-label">Units</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="units" name="units">
                <small class="text-muted">If left blank the default will be used.</small>
              </div>
            </div>
            <div class="form-group row">
              <div class="offset-sm-3 col-sm-9">
                <label class="custom-control custom-radio">
                  <input id="html" value="html" name="radio" type="radio"<?php echo ($default_ver === 'html') ? ' checked' : ''; ?> class="custom-control-input">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">HTML version</span>
                </label>
                <label class="custom-control custom-radio">
                  <input id="svg" value="svg" name="radio" type="radio"<?php echo ($default_ver === 'svg') ? ' checked' : ''; ?> class="custom-control-input">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">SVG version</span>
                </label>
              </div>
            </div>
            <div class="form-group row">
              <div class="offset-sm-3 col-sm-9">
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
    <script
    src="https://code.jquery.com/jquery-3.1.1.min.js"
    integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
    crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
    // THE JS HERE IS A BIT MESSY AND ALSO NOT ALL JQUERY... //
    // http://stackoverflow.com/questions/29802104/javascript-change-drop-down-box-options-based-on-another-drop-down-box-value
    var buildings = {
    <?php echo $javascript; ?>
    };
    var building_select = document.querySelector('#building');
    var meter_select = document.querySelector('#meter');

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
      $('#preview-frame').html('<iframe frameborder="0" height="' + $("#height").val() + '" width="' + $("#width").val() + '" src="<?php echo "http://{$_SERVER['HTTP_HOST']}/".basename(dirname(__DIR__))."/gauges/gauge.php?"; ?>' + qs + '"></iframe>');
    }

    // http://stackoverflow.com/a/111545/2624391
    function encodeQueryData(data) {
      var ret = [];
      for (var d in data)
        ret.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
      return ret.join("&");
    }
    
    $('#preview').on('click', function() {
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
      };
      console.log(data);
      setGauge(encodeQueryData(data));
    });

    <?php
    $saved_rv_configs = array();
    $cur_id = null;
    foreach ($db->query('SELECT relative_values.grouping, relative_values.id, meters.id AS meter_id FROM relative_values INNER JOIN meters ON meters.bos_uuid = relative_values.meter_uuid WHERE permission =\'gauges\'') as $row) {
      if ($cur_id !== $row['meter_id']) {
        $saved_rv_configs[$row['meter_id']] = array(array(intval($row['id']), json_decode($row['grouping'])));
      } else {
        $saved_rv_configs[$row['meter_id']][] = array(intval($row['id']), json_decode($row['grouping']));
      }
      $cur_id = $row['meter_id'];
    }
    ?>
    var saved_rv_configs = <?php echo json_encode($saved_rv_configs); ?>;

    function updateConfigs() {
      $('#radios').html('');
      var val = $('#meter').val();
      $('#modal_btn').attr('data-id', val);
      if (val in saved_rv_configs) {
        $.each(saved_rv_configs[val], function( index, value ) {
          if (index === 0) {
            $('#radios').html('<p>Select an already existing configuration:</p>');
          }
          $('#radios').append('<label class="custom-control custom-radio"><input name="existing_configs" type="radio" class="custom-control-input" value="'+value[0]+'"><span class="custom-control-indicator"></span><span class="custom-control-description"><code>'+JSON.stringify(value[1])+'</code></span></label>');
        });
        $('#radios').append('<hr><p>Or create a new configuration below:</p>');
      } else {
        $('#radios').html('<p class="text-muted">There are no relative value configurations for this meter.</p>');
      }
    }
    updateConfigs();
    $('#meter, #building').on('change', updateConfigs);

    $('#rv_modal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget); // Button that triggered the modal
      var id = button.data('id');
      var modal = $(this)
      $('#rv_meter_id').val(id);
      if (id in saved_rv_configs) {
        console.log(saved_rv_configs[id]);
      }
    });

    $('#modal_form').on('submit', function( event ) {
      event.preventDefault();
      $('#rv_modal').modal('hide');
      var form = $(this);
      $.ajax( {
        type: "POST",
        url: form.attr( 'action' ),
        data: form.serialize(),
        success: function( response ) {
          $('#radios').prepend(response);
        }
      } );
    });
    </script>
  </body>
</html>