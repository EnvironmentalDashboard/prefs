<?php
/**
 * Messages have "probability bins" associated with them that
 * indicates the probability a message will appear based on
 * which percentile the current reading falls into
 */
// error_reporting(-1);
// ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';
if (isset($_POST['gauge'])) {
  $column = $_POST['resource'] . '_messages'; // Possible SQL injection vulnerability
  // Update the meter ID in the cwd_bos table
  $stmt = $db->prepare('UPDATE cwd_bos SET ' . $column . ' = ? WHERE user_id = ? LIMIT 1');
  $stmt->execute(array($_POST['gauge'], $user_id));
}
if (isset($_POST['add-message'])) {
  $stmt = $db->prepare('INSERT INTO cwd_messages (user_id, resource, message, prob1, prob2, prob3, prob4, prob5) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
  $stmt->execute(array($user_id, $_POST['resource'], $_POST['new-message'], $_POST['new-prob1'], $_POST['new-prob2'], $_POST['new-prob3'], $_POST['new-prob4'], $_POST['new-prob5']));
}
if (isset($_POST['edit'])) {
  $stmt = $db->prepare('UPDATE cwd_messages
    SET message = ?, prob1 = ?, prob2 = ?, prob3 = ?, prob4 = ?, prob5 = ?
    WHERE id = ? LIMIT 1');
  $stmt->execute(array($_POST['edit-message'], $_POST['bin1'], $_POST['bin2'], $_POST['bin3'], $_POST['bin4'], $_POST['bin5'], $_POST['message-id']));
}
if (isset($_POST['delete-btn'])) {
  $stmt = $db->prepare("DELETE FROM cwd_messages WHERE id = ?");
  $stmt->execute(array($_POST['id']));
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px;padding-bottom:30px;">
    <div class="modal fade" id="edit-modal">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form action="" method="POST">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <h4 class="modal-title">Edit Message</h4>
            </div>
            <div class="modal-body">
              <input type="hidden" value="" name="message-id" id="message-id" />
              <div class="form-group">
                <textarea class="form-control" name="edit-message" id="edit-message" rows="6" placeholder="Message"></textarea>
              </div>
              <div class="form-group">
                <label for="bin1">Bin 1</label>
                <input type="text" class="form-control" placeholder="Bin 1" name="bin1" id="bin1" value="">
              </div>
              <div class="form-group">
                <label for="bin2">Bin 2</label>
                <input type="text" class="form-control" placeholder="Bin 2" name="bin2" id="bin2" value="">
              </div>
              <div class="form-group">
                <label for="bin3">Bin 3</label>
                <input type="text" class="form-control" placeholder="Bin 3" name="bin3" id="bin3" value="">
              </div>
              <div class="form-group">
                <label for="bin4">Bin 4</label>
                <input type="text" class="form-control" placeholder="Bin 4" name="bin4" id="bin4" value="">
              </div>
              <div class="form-group">
                <label for="bin5">Bin 5</label>
                <input type="text" class="form-control" placeholder="Bin 5" name="bin5" id="bin5" value="">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" name="edit" class="btn btn-primary">Save changes</button>
            </div>
          </form>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

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
          <ul class="nav nav-pills flex-column">
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
          <?php
          $resources = array('landing', 'electricity', 'gas', 'stream', 'water', 'weather');
          foreach ($resources as $resource) {
            echo "<form action='' method='POST'
                  id='{$resource}_dropdown_form'";
            echo ($resource === 'landing') ? " style='margin-bottom:10px'>" : " style='display:none;margin-bottom:10px'>";
            echo "<p>Select a gauge to be used as the resource for determining <attr style='cursor:help;text-decoration:underline' title='A message is displayed if it has a value greater than 0 in the appriopriate bin. The appriopriate bin is determined by the relative value of the selected gauge (bin 1 = lowest use, bin 5 = highest use). Additionally, messages with a higher bin will usually be shown before messages with a lower bin, but there is a degree of randomness.'>bin order</attr>. If there are no gauges available, you need to <a href='create-gauge.php'>create one</a>.</p>";
            echo "<div class='form-group'>
                    <input type='hidden' name='resource' value='{$resource}'>
                    <select name='gauge' class='custom-select' id='{$resource}_dropdown' style='margin-bottom:10px'>
                      {$gauges}
                    </select>
                  </div>
                  </form>";
          ?>
          <table class="table"
          <?php echo ($resource === 'landing') ? '' : " style='display:none'"; ?>
          id="<?php echo $resource . '_table'; ?>">
          <thead>
            <tr>
              <th>Message</th>
              <th>Bin&nbsp;1</th>
              <th>Bin&nbsp;2</th>
              <th>Bin&nbsp;3</th>
              <th>Bin&nbsp;4</th>
              <th>Bin&nbsp;5</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($db->query("SELECT * FROM cwd_messages WHERE resource = '{$resource}' AND user_id = {$user_id}") as $row) {
              echo '<tr>';
                echo "<td>{$row['message']}</td>
                <td>{$row['prob1']}</td>
                <td>{$row['prob2']}</td>
                <td>{$row['prob3']}</td>
                <td>{$row['prob4']}</td>
                <td>{$row['prob5']}</td>
                <td>
                  <form id='{$row['resource']}_edit_form' action='' method='POST'>
                    <input type='hidden' name='hidden-message-id' value='{$row['id']}' />
                    <input type='hidden' name='hidden-message' value='".htmlspecialchars($row['message'], ENT_QUOTES)."' />
                    <input type='hidden' name='hidden-prob1' value='{$row['prob1']}' />
                    <input type='hidden' name='hidden-prob2' value='{$row['prob2']}' />
                    <input type='hidden' name='hidden-prob3' value='{$row['prob3']}' />
                    <input type='hidden' name='hidden-prob4' value='{$row['prob4']}' />
                    <input type='hidden' name='hidden-prob5' value='{$row['prob5']}' />
                    <input type='submit' class='btn btn-primary' value='Edit' />
                  </form>
                </td>
                <td>
                  <form action='' method='POST'>
                    <input type='hidden' name='id' value='{$row['id']}' />
                    <input type='submit' name='delete-btn' class='btn btn-danger' value='Delete' />
                  </form>
                </td>";
              echo '</tr>';
            } ?>
          </tbody>
        </table>
        <form action="" id="<?php echo $resource . '_form'; ?>" method="POST"<?php echo ($resource === 'landing') ? '' : " style='display:none'"; ?>>
          <h5>New message</h5>
          <input type="hidden" value="<?php echo $resource ?>" name="resource">
          <div class="row">
            <div class="col-xs-2">
              <div class="form-group">
                <label for="<?php echo $resource . 'prob1'; ?>">Bin 1</label>
                <input type="text" name="new-prob1" class="form-control" id="<?php echo $resource . 'prob1'; ?>">
              </div>
            </div>
            <div class="col-xs-2">
              <div class="form-group">
                <label for="<?php echo $resource . 'prob2'; ?>">Bin 2</label>
                <input type="text" name="new-prob2" class="form-control" id="<?php echo $resource . 'prob2'; ?>">
              </div>
            </div>
            <div class="col-xs-2">
              <div class="form-group">
                <label for="<?php echo $resource . 'prob3'; ?>">Bin 3</label>
                <input type="text" name="new-prob3" class="form-control" id="<?php echo $resource . 'prob3'; ?>">
              </div>
            </div>
            <div class="col-xs-2">
              <div class="form-group">
                <label for="<?php echo $resource . 'prob4'; ?>">Bin 4</label>
                <input type="text" name="new-prob4" class="form-control" id="<?php echo $resource . 'prob4'; ?>">
              </div>
            </div>
            <div class="col-xs-2">
              <div class="form-group">
                <label for="<?php echo $resource . 'prob5'; ?>">Bin 5</label>
                <input type="text" name="new-prob5" class="form-control" id="<?php echo $resource . 'prob5'; ?>">
              </div>
            </div>
            <div class="col-xs-2"></div><!-- There's an extra one... -->
          </div>
          <div class="row">
            <div class="col-xs-10">
              <div class="form-group">
                <label for="<?php echo $resource . 'message'; ?>">Message</label>
                <textarea name="new-message" class="form-control" id="<?php echo $resource . 'message'; ?>" rows="3"></textarea>
              </div>
            </div>
            <div class="col-xs-1"></div>
          </div>
          <input type='submit' name="add-message" class="btn btn-primary">
        </form>
          <?php } ?>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script>
      var curr_dropdown = '#landing_dropdown_form',
          curr_table = '#landing_table',
          curr_form = '#landing_form';
      $('#landing, #electricity, #gas, #stream, #water, #weather').click(function(e) {
        e.preventDefault();
        $(curr_form.slice(0, -5)).removeClass('active');
        $(this).addClass('active')
        $(curr_dropdown).css('display', 'none');
        $(curr_table).css('display', 'none');
        $(curr_form).css('display', 'none');
        curr_dropdown = '#' + $(this).attr('id') + '_dropdown_form';
        curr_table = '#' + $(this).attr('id') + '_table';
        curr_form = '#' + $(this).attr('id') + '_form';
        $(curr_dropdown).css('display', 'initial');
        $(curr_table).css('display', 'initial');
        $(curr_form).css('display', 'initial');
      });
      $('#landing_edit_form, #electricity_edit_form, #gas_edit_form, #stream_edit_form, #water_edit_form, #weather_edit_form').submit(function(e) {
        e.preventDefault();
        $('#edit-modal').modal('show');
        var id = $(this).find('input[name="hidden-message-id"]').val();
        var message = $(this).find('input[name="hidden-message"]').val();
        var prob1 = $(this).find('input[name="hidden-prob1"]').val();
        var prob2 = $(this).find('input[name="hidden-prob2"]').val();
        var prob3 = $(this).find('input[name="hidden-prob3"]').val();
        var prob4 = $(this).find('input[name="hidden-prob4"]').val();
        var prob5 = $(this).find('input[name="hidden-prob5"]').val();
        console.log(message);
        $('#message-id').val(id);
        $('#edit-message').val(message);
        $('#bin1').val(prob1);
        $('#bin2').val(prob2);
        $('#bin3').val(prob3);
        $('#bin4').val(prob4);
        $('#bin5').val(prob5);
      });
      $('#landing_dropdown, #electricity_dropdown, #gas_dropdown, #stream_dropdown, #water_dropdown, #weather_dropdown').change(function(e) {
        $('#' + $(this).attr('id') + '_form').submit();
      });
      <?php $current_values = $db->query("SELECT landing_messages, electricity_messages, gas_messages, stream_messages, water_messages, weather_messages FROM cwd_bos WHERE user_id = {$user_id} LIMIT 1")->fetch(); ?>
      $(function() {
        $("#landing_dropdown").val('<?php echo $current_values['landing_messages'] ?>');
        $("#electricity_dropdown").val('<?php echo $current_values['electricity_messages'] ?>');
        $("#gas_dropdown").val('<?php echo $current_values['gas_messages'] ?>');
        $("#stream_dropdown").val('<?php echo $current_values['stream_messages'] ?>');
        $("#water_dropdown").val('<?php echo $current_values['water_messages'] ?>');
        $("#weather_dropdown").val('<?php echo $current_values['weather_messages'] ?>');
      });
    </script>
  </body>
</html>