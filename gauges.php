<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';
if (isset($_POST['submit'])) {
  if ($_POST['radio-stacked'] === 'yes') {
    $on = 1;
  }
  else {
    $on = 0;
  }
  $stmt = $db->prepare("UPDATE cwd_states SET gauge1 = ?, gauge2 = ?, gauge3 = ?, gauge4 = ?, `on` = ? WHERE resource = ? AND user_id = ? LIMIT 1");
  $stmt->execute(array($_POST['gauge1'], $_POST['gauge2'], $_POST['gauge3'], $_POST['gauge4'], $on, $_POST['resource'], $user_id));
}

$gauges = array();
foreach ($db->query("SELECT id, title, title2 FROM gauges WHERE user_id = {$user_id}") as $key => $value) {
  $gauges[$key]['id'] = $value['id'];
  $gauge_name = ($value['title'] !== '') ? $value['title'] .' '. $value['title2'] : 'Untitled gauge';
  $gauges[$key]['title'] = $gauge_name;
}
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
      <div style="clear: both;height: 20px"></div>
      <div class="row">
        <div class="col-sm-3">
          <ul class="nav flex-column nav-pills">
            <li class="nav-item">
              <a class="nav-link active" href="#" id="landing">Landing</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" id="electricity">Electricity</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" id="gas">Gas</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" id="stream">Stream</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" id="water">Water</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" id="weather">Weather</a>
            </li>
          </ul>
        </div>
        <div class="col-sm-9">
          <p>Select the gauges shown on the right of CWD. Gauge 1 is on the top, gauge 4 is on the bottom.</p>
          <?php foreach ($db->query("SELECT resource, gauge1, gauge2, gauge3, gauge4, `on` FROM cwd_states WHERE user_id = {$user_id}") as $row) { ?>
            <form action="" method="POST" id="<?php echo $row['resource'] ?>_form"<?php if ($row['resource'] !== 'landing') { echo ' style="display:none"';} ?>>
              <fieldset class="form-group">
                <label for="<?php echo $row['resource'] ?>_gauge1">Select gauge 1</label>
                <select class="custom-select" style="width:100%" id="<?php echo $row['resource'] ?>_gauge1" name="gauge1">
                  <?php
                  foreach ($gauges as $gauge) {
                    if ($gauge['id'] === $row['gauge1']) {
                      echo "<option value='{$gauge['id']}' selected='selected'>{$gauge['title']}</option>";
                    }
                    else {
                      echo "<option value='{$gauge['id']}'>{$gauge['title']}</option>";
                    }
                    
                  }
                  ?>
                </select>
              </fieldset>
              <fieldset class="form-group">
                <label for="<?php echo $row['resource'] ?>_gauge2">Select gauge 2</label>
                <select class="custom-select" style="width:100%" id="<?php echo $row['resource'] ?>_gauge2" name="gauge2">
                  <?php
                  foreach ($gauges as $gauge) {
                    if ($gauge['id'] === $row['gauge2']) {
                      echo "<option value='{$gauge['id']}' selected='selected'>{$gauge['title']}</option>";
                    }
                    else {
                      echo "<option value='{$gauge['id']}'>{$gauge['title']}</option>";
                    }
                    
                  }
                  ?>
                </select>
              </fieldset>
              <fieldset class="form-group">
                <label for="<?php echo $row['resource'] ?>_gauge3">Select gauge 3</label>
                <select class="custom-select" style="width:100%" id="<?php echo $row['resource'] ?>_gauge3" name="gauge3">
                  <?php
                  foreach ($gauges as $gauge) {
                    if ($gauge['id'] === $row['gauge3']) {
                      echo "<option value='{$gauge['id']}' selected='selected'>{$gauge['title']}</option>";
                    }
                    else {
                      echo "<option value='{$gauge['id']}'>{$gauge['title']}</option>";
                    }
                    
                  }
                  ?>
                </select>
              </fieldset>
              <fieldset class="form-group">
                <label for="<?php echo $row['resource'] ?>_gauge4">Select gauge 4</label>
                <select class="custom-select" style="width:100%" id="<?php echo $row['resource'] ?>_gauge4" name="gauge4">
                  <?php
                  foreach ($gauges as $gauge) {
                    if ($gauge['id'] === $row['gauge4']) {
                      echo "<option value='{$gauge['id']}' selected='selected'>{$gauge['title']}</option>";
                    }
                    else {
                      echo "<option value='{$gauge['id']}'>{$gauge['title']}</option>";
                    }
                    
                  }
                  ?>
                </select>
              </fieldset>
              <?php if ($row['resource'] !== 'landing') { ?>
              Resource visability
              <div class="c-inputs-stacked">
                <label class="c-input c-radio">
                  <input id="yes" value="yes" name="radio-stacked" type="radio"<?php if ($row['on'] === '1') { echo " checked"; } ?>>
                  <span class="c-indicator"></span>
                  Shown
                </label>
                <label class="c-input c-radio">
                  <input id="no" value="no" name="radio-stacked" type="radio"<?php if ($row['on'] === '0') { echo " checked"; } ?>>
                  <span class="c-indicator"></span>
                  Hidden
                </label>
              </div>
              <?php } else { echo '<input type="hidden" value="yes" name="radio-stacked" />'; } ?>
              <input type="hidden" value="<?php echo $row['resource'] ?>" name="resource">
              <button type="submit" name="submit" class="btn btn-primary" style='margin-top:10px'>Save changes</button>
            </form>
          <?php } ?>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script>
      var curr = '#landing_form';
      $('#landing, #electricity, #gas, #stream, #water, #weather').click(function(e) {
        e.preventDefault();
        $(curr.slice(0, -5)).removeClass('active');
        $(this).addClass('active')
        $(curr).css('display', 'none');
        curr = '#' + $(this).attr('id') + '_form';
        $(curr).css('display', 'initial');
      })
    </script>
  </body>
</html>