
<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';
if (isset($_POST['submit'])) {
  // Update cwd_bos table
  $stmt = $db->prepare('UPDATE cwd_bos SET water_speed = ?, electricity_speed = ?, squirrel = ?, fish = ? WHERE user_id = ? LIMIT 1');
  $stmt->execute(array($_POST['gauge_water'], $_POST['gauge_electricity'], $_POST['gauge_squirrel'], $_POST['gauge_fish'], $user_id));
}
// Saving in a variable so data can be used multiple times on page
$gauges = '';
foreach ($db->query("SELECT id, title, title2 FROM gauges WHERE user_id = {$user_id}") as $gauge) {
  $gauge_name = ($gauge['title'] !== '') ? $gauge['title'] . $gauge['title2'] : 'Untitled gauge';
  $gauges .= "<option value='{$gauge['id']}'>{$gauge_name}</option>";
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
      <div style="clear: both;height: 20px"></div>
      <div class="row">
        <div class="col-sm-6 push-sm-3">
          <h1>Gauge Integration</h1>
          <hr>
          <form action="" method="POST">
            <fieldset class="form-group">
              <label for="gauge_water">Select the gauge to control the speed of the water</label>
              <select name="gauge_water" id="gauge_water" class="custom-select">
                <?php echo $gauges; ?>
              </select>
            </fieldset>
            <fieldset class="form-group">
              <label for="gauge_electricity">Select the gauge to control the speed of the electricity flow</label>
              <select name="gauge_electricity" id="gauge_electricity" class="custom-select">
                <?php echo $gauges; ?>
              </select>
            </fieldset>
            <fieldset class="form-group">
              <label for="gauge_fish">Select the gauge to control the mood of Wally Walleye</label>
              <select name="gauge_fish" id="gauge_fish" class="custom-select">
                <?php echo $gauges; ?>
              </select>
            </fieldset>
            <fieldset class="form-group">
              <label for="gauge_squirrel">Select the gauge to control the mood of Flash the Energy Squirrel</label>
              <select name="gauge_squirrel" id="gauge_squirrel" class="custom-select">
                <?php echo $gauges; ?>
              </select>
            </fieldset>
            <input type="submit" name="submit" class="btn btn-primary" value="Update">
          </form>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      <?php $current_values = $db->query("SELECT water_speed, electricity_speed, squirrel, fish FROM cwd_bos WHERE user_id = {$user_id} LIMIT 1")->fetch(); ?>
      $(function() {
        $("#gauge_water").val('<?php echo $current_values['water_speed'] ?>');
        $("#gauge_electricity").val('<?php echo $current_values['electricity_speed'] ?>');
        $("#gauge_squirrel").val('<?php echo $current_values['squirrel'] ?>');
        $("#gauge_fish").val('<?php echo $current_values['fish'] ?>');
      });
    </script>
  </body>
</html>