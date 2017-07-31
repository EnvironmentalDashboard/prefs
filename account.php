<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require '../includes/class.BuildingOS.php';
require 'includes/check-signed-in.php';
if (isset($_POST['delete-account'])) {
  $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
  $stmt->execute(array($user_id));
  // all the orgs that use the api record created by $user_id
  foreach ($db->query("SELECT id, url FROM orgs WHERE api_id IN (SELECT id FROM api WHERE user_id = {$user_id})") as $org) {
    // the user_ids (other than the current $user_id) that are associated with the org that has api credentials of an account being deleted
    $other_users = $db->query("SELECT DISTINCT user_id FROM users_orgs_map WHERE org_id = {$org['id']} AND user_id != {$user_id}")->fetchAll();
    // if the org is used by other dashboard accounts
    if (count($other_users) > 0) { // org needs a new api_id
      foreach ($other_users as $row) { // these users are also associated with this organization, so theyre api credentials should be able to retrieve data
        $bos = new BuildingOS($db, $db->query("SELECT id FROM api WHERE user_id = {$row['user_id']} LIMIT 1")->fetchColumn());
        if (in_array($org['url'], $bos->getOrganizations())) { // if the org is in the list returned from api
          $stmt = $db->prepare('UPDATE orgs SET api_id = ? WHERE id = ?');
          $stmt->execute(array($db->query("SELECT id FROM api WHERE user_id = {$row['user_id']} LIMIT 1")->fetchColumn(), $org['id']));
          break;
        }
      }
    } else { // delete org, and all the buildings/meters belonging to it
      $stmt = $db->prepare('DELETE FROM orgs WHERE id = ?');
      $stmt->execute(array($org['id']));
      $stmt = $db->prepare('DELETE FROM buildings WHERE org_id = ?');
      $stmt->execute(array($org['id']));
      $stmt = $db->prepare('DELETE FROM meter_data WHERE meter_id IN (SELECT id FROM meters WHERE org_id = ?)');
      $stmt->execute(array($org['id']));
      $stmt = $db->prepare('DELETE FROM meters WHERE org_id = ?');
      $stmt->execute(array($org['id']));
    }
  }
  $stmt = $db->prepare('DELETE FROM users_orgs_map WHERE user_id = ?');
  $stmt->execute(array($user_id));
  $stmt = $db->prepare('DELETE FROM api WHERE user_id = ?');
  $stmt->execute(array($user_id));
  $stmt = $db->prepare('DELETE FROM cwd_bos WHERE user_id = ?');
  $stmt->execute(array($user_id));
  $stmt = $db->prepare('DELETE FROM cwd_landscape_components WHERE user_id = ?');
  $stmt->execute(array($user_id)); // In the future we might also want to delete all the images on the server associated with the records being deleted
  $stmt = $db->prepare('DELETE FROM cwd_messages WHERE user_id = ?');
  $stmt->execute(array($user_id));
  $stmt = $db->prepare('DELETE FROM cwd_states WHERE user_id = ?');
  $stmt->execute(array($user_id));
  $stmt = $db->prepare('DELETE FROM time_series WHERE user_id = ?');
  $stmt->execute(array($user_id));
  $stmt = $db->prepare('DELETE FROM timing WHERE user_id = ?');
  $stmt->execute(array($user_id));
  shell_exec('sudo rm '.escapeshellarg("/var/www/html/{$symlink}"));
  header('Location: /');
  exit();
}
if (isset($_POST['orgs'])) {
  $stmt = $db->prepare('DELETE FROM users_orgs_map WHERE user_id = ?');
  $stmt->execute(array($user_id));
  foreach ($_POST['orgs'] as $org) {
    $stmt = $db->prepare('INSERT INTO users_orgs_map (user_id, org_id) VALUES (?, ?)');
    $org_id = explode('/', $org);
    $org_id = $org_id[count($org_id)-1];
    $stmt->execute(array($user_id, $org_id));
  }
}
if (isset($_POST['apps'])) {
  $stmt = $db->prepare('UPDATE users SET gauges = 0, cwd = 0, timeseries = 0 WHERE id = ?');
  $stmt->execute(array($user_id));
  if (in_array('gauges', $_POST['apps'])) {
    $stmt = $db->prepare('UPDATE users SET gauges = 1 WHERE id = ?');
    $stmt->execute(array($user_id));
  }
  if (in_array('cwd', $_POST['apps'])) {
    $stmt = $db->prepare('UPDATE users SET cwd = 1 WHERE id = ?');
    $stmt->execute(array($user_id));
  }
  if (in_array('timeseries', $_POST['apps'])) {
    $stmt = $db->prepare('UPDATE users SET timeseries = 1 WHERE id = ?');
    $stmt->execute(array($user_id));
  }
}
$bos = new BuildingOS($db, $db->query("SELECT id FROM api WHERE user_id = {$user_id} LIMIT 1")->fetchColumn());
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Account</title>
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
      <div class="row" style="padding-top: 15px;padding-bottom: 15px">
        <div class="col-sm-12">
          <h2>Dashboard account settings</h2>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-8">
          <h4>Update synced organizations</h4>
          <hr>
          <form action="" method="POST">
            <?php foreach ($bos->getOrganizations() as $name => $url) {
              $stmt = $db->prepare('SELECT COUNT(*) FROM users_orgs_map WHERE org_id IN (SELECT id FROM orgs WHERE url = ?) AND user_id = ?');
              $stmt->execute(array($url, $user_id));
              if ($stmt->fetchColumn() === '0') {
                echo "<div class='form-check'>
                        <label class='form-check-label'>
                          <input name='orgs[]' class='form-check-input' type='checkbox' value='{$url}'>
                          {$name}
                        </label>
                      </div>";
              } else {
                echo "<div class='form-check'>
                        <label class='form-check-label'>
                          <input checked name='orgs[]' class='form-check-input' type='checkbox' value='{$url}'>
                          {$name}
                        </label>
                      </div>";
              }
            } ?>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </form>
          <div class="spacer" style="clear: both;height: 30px"></div>
          <h4>Installed web apps</h4>
          <hr>
          <form action="" method="POST">
            <div class='form-check'>
              <label class='form-check-label'>
                <input name='apps[]' class='form-check-input' type='checkbox' value='gauges' <?php echo ($user_prefs['gauges'] === '1') ? 'checked' : ''; ?>>
                Gauges
              </label>
            </div>
            <div class='form-check'>
              <label class='form-check-label'>
                <input name='apps[]' class='form-check-input' type='checkbox' value='cwd' <?php echo ($user_prefs['cwd'] === '1') ? 'checked' : ''; ?>>
                Citywide Dashboard
              </label>
            </div>
            <div class='form-check'>
              <label class='form-check-label'>
                <input name='apps[]' class='form-check-input' type='checkbox' value='timeseries' <?php echo ($user_prefs['timeseries'] === '1') ? 'checked' : ''; ?>>
                Time series
              </label>
            </div>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </form>
        </div>
        <div class="col-sm-4">
          <h4>Delete account</h4>
          <hr>
          <p>Deleting your account delete all the organizations, buildings, and meters in our database not associated with another dashboard account.</p>
          <p>
            <form action="" method="POST" id="delete-account-form">
              <input type="submit" value="Delete account" name="delete-account" id="delete-account-btn" class="btn btn-danger">
            </form>
          </p>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script>
      $('#delete-account-btn').on('click', function(e) {
        if (!confirm('Are you sure you want to delete your account?')) {
          e.preventDefault();
        }
      })
    </script>
  </body>
</html>