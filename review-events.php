<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';
date_default_timezone_set("America/New_York");
if (!empty($_POST)) {
  $handle = fopen('/var/www/html/oberlin/calendar/email_buffer.txt', 'a');
  foreach ($_POST as $key => $value) {
    $approved = ($value === 'approve') ? 1 : 0;
    $stmt = $db->prepare('UPDATE calendar SET approved = ? WHERE id = ? LIMIT 1');
    $stmt->execute(array($approved, $key));
    $stmt = $db->prepare('SELECT contact_email FROM calendar WHERE id = ?');
    $stmt->execute(array($key));
    $contact_email = $stmt->fetchColumn();
    if ($contact_email != '') {
      if ($approved) {
        $message = "Your event was approved, and can be viewed <a href='https://oberlindashboard.org/oberlin/calendar/slide.php?id={$key}'>here</a>.";
      } else {
        $message = "Your event was rejected.";
      }
      if ($handle) {
        fwrite($handle, "{$contact_email}\$SEP\$Environmental Dashboard Calendar Submission\$SEP\${$message}\n");
      } else {
        die('Error opening email_buffer.txt');
      }
    }
  }
  fclose($handle);
}
function convert_to_day($d) {
  switch ($d) {
    case 0:
      return 'Sunday';
    case 1:
      return 'Monday';
    case 1:
      return 'Tuesday';
    case 1:
      return 'Wednesday';
    case 1:
      return 'Thursday';
    case 1:
      return 'Friday';
    case 1:
      return 'Saturday';
    default:
      return null;
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
    <title>Calendar Backend</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">

    <!-- Modal -->
    <div class="modal fade" id="edit-modal" tabindex="-1" role="dialog" aria-labelledby="modal-title" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form action="includes/edit-event.php" method="POST" id="edit-event-form" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="modal-title">Edit event</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="event">Event title</label>
                <input type="text" class="form-control" id="event" name="event" value="<?php echo (!empty($_POST['event'])) ? $_POST['event'] : ''; ?>">
              </div>
              <div class="form-group">
                <label for="date">Date and time event begins</label>
                <input type="text" class="form-control" id="date" name="date" value="<?php echo (!empty($_POST['date'])) ? $_POST['date'] : ''; ?>">
              </div>
              <div class="form-group">
                <label for="date2">Date and time event ends</label>
                <input type="text" class="form-control" id="date2" name="date2" value="<?php echo (!empty($_POST['date2'])) ? $_POST['date2'] : ''; ?>">
              </div>
              <div class="form-group">
                <label for="loc">Event location</label>
                <select class="form-control" id="loc" name="loc">
                  <?php foreach ($db->query('SELECT id, location FROM calendar_locs ORDER BY location ASC') as $row) { ?>
                  <option value="<?php echo $row['id']; ?>"><?php echo $row['location']; ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="form-group">
                <label for="description">Event description</label>
                <textarea name="description" id="description" class="form-control"><?php echo (!empty($_POST['description'])) ? $_POST['description'] : ''; ?></textarea>
                <small class="text-muted">2,000 charachter maximum</small>
              </div>
              <div class="form-group">
                <label for="edit-image">Upload new image (max size 16MB)</label>
                <input type="file" class="form-control-file" id="edit-image" name="edit-image" value="">
                <small class="text-muted">Upload a new image to replace to current image, or skip to leave as-is</small>
              </div>
              <div class="form-group">
                <p class="m-b-0">Repeat weekly on</p>
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="0">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">S</span>
                </label>
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="1">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">M</span>
                </label>
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="2">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">T</span>
                </label>
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="3">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">W</span>
                </label>
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="4">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">T</span>
                </label>
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="5">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">F</span>
                </label>
                <label class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" name="repeat_on[]" value="6">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">S</span>
                </label>
              </div>
              <div class="form-group">
                <label for="repeat_end">Repeat end</label>
                <input type="text" class="form-control" id="repeat_end" name="repeat_end" value="<?php echo (!empty($_POST['repeat_end'])) ? $_POST['repeat_end'] : ''; ?>" placeholder="mm/dd/yyyy">
              </div>
              <input type="hidden" name="id" id="id">
              <div class="custom-controls-stacked">
              <p class="m-b-0">Select the screens the poster will be shown on</p>
              <?php foreach ($db->query('SELECT id, name FROM calendar_screens ORDER BY name ASC') as $row) {
                  echo "<label class=\"custom-control custom-checkbox\">
                        <input type=\"checkbox\" class=\"custom-control-input\" name=\"screen_loc[]\" value=\"{$row['id']}\" id='screen{$row['id']}' checked='true'>
                        <span class=\"custom-control-indicator\"></span>
                        <span class=\"custom-control-description\">{$row['name']}</span>
                        </label>\n";
                } ?>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" id="save-changes" class="btn btn-primary">Save changes</button>
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
      <div style="height:20px;clear:both"></div>
      <div class="row">
        <div class="col-xs-12">
          <form action="" method="POST">
            <?php
            $i = 0;
            foreach ($db->query('SELECT id, event, start, `end`, description, loc_id, screen_ids, repeat_on, repeat_end FROM calendar WHERE approved IS NULL ORDER BY id ASC') as $event) {
              $i++;
            ?>
              <div class="form-group row">
                <div class="col-sm-9">
                  <iframe style="border: 0;min-height: 700px;width: 100%;" src="https://oberlindashboard.org/oberlin/calendar/slide.php?id=<?php echo $event['id'] ?>" id="iframe<?php echo $i ?>"></iframe>
                </div>
                <div class="col-sm-3">
                  <div class="custom-controls-stacked">
                    <label class="custom-control custom-radio">
                      <input value="approve" id="approve<?php echo $event['id']; ?>" name="<?php echo $event['id']; ?>" type="radio" class="custom-control-input">
                      <span class="custom-control-indicator"></span>
                      <span class="custom-control-description">Approve event</span>
                    </label>
                    <label class="custom-control custom-radio">
                      <input value="reject" id="reject<?php echo $event['id']; ?>" name="<?php echo $event['id']; ?>" type="radio" class="custom-control-input">
                      <span class="custom-control-indicator"></span>
                      <span class="custom-control-description">Reject event</span>
                    </label>
                  </div>
                  <p>Starts at <?php echo date("F j, Y, g:i a", $event['start']) ?>, ends at <?php echo date("F j, Y, g:i a", $event['end']) ?></p>
                  <p>
                    <?php if (($json = json_decode($event['repeat_on'], true)) != null) {
                      $json = array_map('convert_to_day', $json);
                      echo "Repeats every ".implode(', ', $json)." ending on " . date("F j\, Y", $event['repeat_end']);
                    } else {
                      echo "Event does not recur.";
                    } ?>
                  </p>
                  <p>
                    Event location: 
                    <?php
                    $stmt = $db->prepare('SELECT location FROM calendar_locs WHERE id = ?');
                    $stmt->execute(array($event['loc_id']));
                    $loc = $stmt->fetchColumn();
                    echo $loc;
                    ?>
                  </p>
                  <p>Description: <?php echo $event['description']; ?></p>
                  <p>
                    <a href="#"
                    data-iframe="iframe<?php echo $i ?>"
                    data-id="<?php echo $event['id']; ?>"
                    data-event="<?php echo htmlspecialchars($event['event']); ?>"
                    data-start="<?php echo $event['start']; ?>"
                    data-end="<?php echo $event['end']; ?>"
                    data-description="<?php echo htmlspecialchars($event['description']); ?>"
                    data-loc_id="<?php echo $event['loc_id']; ?>"
                    data-screen_ids="<?php echo $event['screen_ids']; ?>"
                    data-repeat_on="<?php echo $event['repeat_on']; ?>"
                    data-repeat_end="<?php echo $event['repeat_end']; ?>"
                    class="edit-event">Edit event</a>
                  </p>
                </div>
              </div>
            <?php } ?>
              <?php if ($i !== 0) { ?><input type="submit" class="btn btn-primary"><?php } ?>
          </form>
        </div>
      </div>
      <div style="clear: both;height: 50px"></div>
      <?php if ($i === 0) { echo '<h1 class="text-center text-muted">No new events to review</h1>'; } ?>
    </div>
    <script
    src="https://code.jquery.com/jquery-3.2.1.min.js"
    integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
    crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      function timeConverter(UNIX_timestamp) { // http://stackoverflow.com/a/6078873/2624391
        var a = new Date(UNIX_timestamp * 1000);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var year = a.getFullYear();
        var month = months[a.getMonth()];
        var date = a.getDate();
        var hour = a.getHours();
        var min = a.getMinutes() < 10 ? '0' + a.getMinutes() : a.getMinutes();
        var sec = a.getSeconds() < 10 ? '0' + a.getSeconds() : a.getSeconds();
        var time = month + ' ' + date + ' ' + year + ', ' + hour + ':' + min + ':' + sec ;
        return time;
      }
      var reload_iframe = 'not set yet';
      $('.edit-event').on('click', function(e) {
        e.preventDefault();
        $('#edit-modal').modal('show');
        reload_iframe = $(this).data('iframe');
        $('#id').val($(this).data('id'));
        $('#event').val($(this).data('event'));
        $('#date').val(timeConverter($(this).data('start')));
        $('#date2').val(timeConverter($(this).data('end')));
        $('#loc').val($(this).data('loc_id'));
        $('#description').val($(this).data('description'));
        $('#repeat_on').val($(this).data('repeat_on'));
        $('#repeat_end').val($(this).data('repeat_end'));
        $('input:checkbox').removeAttr('checked'); // uncheck all boxes first
        $.each($(this).data('screen_ids').split(','), function(k, v) { // check all boxes that are saved
          $('#screen' + v).attr('checked','checked');
        });
      });
      $('#save-changes').on('click', function() {
        var form = $('#edit-event-form');
        $.ajax( {
          type: "POST",
          url: form.attr( 'action' ),
          data: form.serialize(),
          contentType: false,
          processData: false,
          success: function( response ) {
            console.log( response );
            $('#' + reload_iframe).attr('src', $('#' + reload_iframe).attr('src'));
            $('#edit-modal').modal('hide');
          }
        } );
      });
    </script>
  </body>
</html>