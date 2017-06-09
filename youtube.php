<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';
if (isset($_POST['new_video'])) {
  $calendar_screen_ids = implode(',', $_POST['screens']);
  parse_str( parse_url( $_POST['new_video'], PHP_URL_QUERY ), $arr );
  $stmt = $db->prepare('INSERT INTO youtube (video_id) VALUES (?)');
  $stmt->execute(array($arr['v']));
}
if (isset($_POST['action']) && $_POST['action'] === 'Save') {
  foreach ($_POST as $key => $value) {
    $explode = explode(',', $key);
    if (count($explode) < 2) {
      continue;
    }
    $youtube_id = $explode[0];
    $screen_id = $explode[1];
    $stmt = $db->prepare('SELECT COUNT(*) FROM youtube_screens WHERE youtube_id = ? AND screen_id = ?');
    $stmt->execute(array($youtube_id, $screen_id));
    if ($stmt->fetchColumn() === '0') {
      $stmt = $db->prepare('INSERT INTO youtube_screens (youtube_id, screen_id, probability) VALUES (?, ?, ?)');
      $stmt->execute(array($youtube_id, $screen_id, $value));
    } else {
      $stmt = $db->prepare('UPDATE youtube_screens SET probability = ? WHERE youtube_id = ? AND screen_id = ?');
      $stmt->execute(array($value, $youtube_id, $screen_id));
    }
  }
}
if (isset($_POST['action']) && $_POST['action'] === 'Delete') {
  $stmt = $db->prepare('DELETE FROM youtube WHERE id = ?');
  $stmt->execute(array($_POST['id']));
  $stmt = $db->prepare('DELETE FROM youtube_screens WHERE youtube_id = ?');
  $stmt->execute(array($_POST['id']));
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
    <!-- Modal -->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form action="" method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="modalLabel">Add video</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="new_video">YouTube URL</label>
                <input type="text" id="new_video" class="form-control" name="new_video">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Add video</button>
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
      <div style="clear: both;height: 20px"></div>
      <div class="container">
        <div class="row">
          <div class="col-sm-12">
            <h1>YouTube videos <a href="#" class="btn btn-primary" style="float: right;" data-toggle="modal" data-target="#modal">Add video</a></h1>
            <table class="table table-responsive table-sm">
              <thead>
                <tr>
                  <th>Video ID</th>
                  <th>Title</th>
                  <?php
                  $screen_count = 0;
                  $screens = array();
                  foreach ($db->query('SELECT id, name FROM calendar_screens ORDER BY name ASC') as $loc) {
                    echo "<th><small>".explode(' - ', $loc['name'])[0]."</small></th>";
                    $screens[] = $loc['id'];
                    $screen_count++;
                  } ?>
                  <th>&nbsp;</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($db->query('SELECT id, video_id FROM youtube ORDER BY id DESC') as $row) { ?>
                <tr><form action="" method="POST">
                  <td><?php echo $row['video_id'] ?></td>
                  <td id="<?php echo $row['video_id'] ?>" class="youtube-title">Loading</td>
                  <?php for ($screen = 0; $screen < $screen_count; $screen++) { 
                    echo "<td>";
                    $prob = $db->query('SELECT probability FROM youtube_screens WHERE youtube_id = '.intval($row['id']).' AND screen_id = '.intval($screens[$screen]))->fetchColumn();
                    $prob = ($prob === false) ? 0 : $prob;
                    echo "<input type='text' class='form-control form-control-sm' placeholder='Probability' name='{$row['id']},{$screens[$screen]}' value='{$prob}' />";
                    echo "</td>";
                  } ?>
                  <td><input type="submit" class="btn btn-secondary btn-sm" name="action" value="Save" /></td>
                  <td><input type="submit" class="btn btn-danger btn-sm" name="action" value="Delete" /></td>
                  <input type="hidden" name="id" value="<?php echo $row['id'] ?>">
                </form></tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <script
    src="https://code.jquery.com/jquery-3.2.0.min.js"
    integrity="sha256-JAW99MJVpJBGcbzEuXk4Az05s/XyDdBomFqNlM3ic+I="
    crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script>
      $('.youtube-title').each(function() {
        var videoId = $(this).attr('id');
        $.ajax({
          url: "https://www.googleapis.com/youtube/v3/videos?id=" + videoId + "&key=AIzaSyCDAZRPbbNS4w_kBz3bZ4Q5B8RFS46FyhM&fields=items(snippet(title))&part=snippet", 
          dataType: "jsonp",
          success: function(data) {
            if (data.items.length > 0) {
              $('#' + videoId).text(data.items[0].snippet.title);
            } else {
              $('#' + videoId).text('No title found for video');
            }
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, + ' | ' + errorThrown);
          }
        });
      });
    </script>
  </body>
</html>