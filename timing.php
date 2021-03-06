<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';
$timing = $db->query("SELECT * FROM timing WHERE user_id = {$user_id} LIMIT 1")->fetch();
if (isset($_POST['submit'])) {
  $stmt = $db->prepare("UPDATE timing SET message_section = ?, delay = ?, `interval` = ? WHERE user_id = ?");
  $stmt->execute(array($_POST['message_section'], $_POST['delay'], $_POST['interval'], $user_id));
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
      <div style="clear: both;height: 20px"></div>
      <div class="container">
        <div class="row">
          <div class="col-sm-6 push-sm-3">
            <h1>Timing</h1>
            <hr>
            <form accept="" method="POST">
              <fieldset class="form-group">
                <label for="message_section">Seconds for each message</label>
                <input type="text" class="form-control" id="message_section" name="message_section" value="<?php echo $timing['message_section']; ?>">
                <small class="text-muted">How long should each message be displayed for before rotating to the next message?</small>
              </fieldset>
              <fieldset class="form-group">
                <label for="delay">Seconds before play mode</label>
                <input type="text" class="form-control" id="delay" name="delay" value="<?php echo $timing['delay']; ?>">
                <small class="text-muted">After a period of inaction, the Citywide Dashboard will start to switch between "Water", "Electrictity", etc. automatically. How long should the period of inaction be?</small>
              </fieldset>
              <fieldset class="form-group">
                <label for="interval">Seconds on each screen when in play mode</label>
                <input type="text" class="form-control" id="interval" name="interval" value="<?php echo $timing['interval']; ?>">
                <small class="text-muted">When switching between "Water", "Electrictity", etc. automatically, how long should the dashboard stay on each type of resource?</small>
              </fieldset>
              <input type="submit" class="btn btn-primary" name="submit">
            </form>
          </div>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>