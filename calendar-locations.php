<?php
require '../includes/db.php';
require 'includes/check-signed-in.php';
if (!empty($_POST['loc']) &&
  file_exists($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
  $fp = fopen($_FILES['file']['tmp_name'], 'rb'); // read binary
  $stmt = $db->prepare('INSERT INTO calendar_locs (location, img) VALUES (?, ?)');
  $stmt->bindParam(1, $_POST['loc']);
  $stmt->bindParam(2, $fp, PDO::PARAM_LOB);
  $stmt->execute();
}
if (isset($_POST['delete-submit'])) {
  $stmt = $db->prepare('DELETE FROM calendar_locs WHERE id = ?');
  $stmt->execute(array($_POST['delete-loc']));
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Calendar Backend</title>
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
      <div style="height:20px;clear:both"></div>
      <div class="row">
        <div class="col-sm-3">
          <h2>Add location</h2>
          <p>Use this form to add locations and corresponding images that people can select as the location of their event.</p>
          <hr>
          <form enctype="multipart/form-data" action="" method="POST">
            <div class="form-group">
              <label for="loc">Location name</label>
              <input type="text" name="loc" id="loc" class="form-control">
            </div>
            <div class="form-group">
              <label for="edit-file">Location image</label>
              <input type="file" class="form-control-file" id="edit-file" name="file" value="">
            </div>
            <input type="submit" name="submit" class="btn btn-primary">
          </form>
        </div>
        <div class="col-sm-9">
          <h2>Calendar Locations</h2>
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Image</th>
                <th>Location</th>
                <th>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach ($db->query('SELECT id, location, img FROM calendar_locs ORDER BY id ASC') as $loc) {
                echo "<tr>";
                echo "<th scope='row'>{$loc['id']}</th>";
                if ($loc['img'] == '') {
                  echo "<td><p>No image for this location</p></td>";
                } else {
                  echo "<td style='max-width:200px'><img class='img-fluid' src='data:image/jpeg;base64,".base64_encode($loc['img'])."' /></td>";
                }
                echo "<td><p>{$loc['location']}</p></td>";
                echo "<td><form action='' method='POST'>
                <input type='hidden' name='delete-loc' value='{$loc['id']}'>
                <input type='submit' class='btn btn-danger' value='Delete' name='delete-submit'></form></td>";
                echo "</tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
  </body>
</html>