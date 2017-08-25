<?php
error_reporting(-1);
ini_set('display_errors', 'On');
$symlink = explode('/', $_SERVER['REQUEST_URI'])[1];
require '../includes/db.php';
require 'includes/check-signed-in.php';
function timeseriesURL($meter_id, $dasharr1, $fill1, $meter_id2, $dasharr2, $fill2, $dasharr3, $fill3, $start, $ticks, $color1, $color2, $color3, $label) {
  $q = http_build_query(array(
    'meter_id' => $meter_id,
    'dasharr1' => $dasharr1,
    'fill1' => $fill1,
    'meter_id2' => $meter_id2,
    'dasharr2' => $dasharr2,
    'fill2' => $fill2,
    'dasharr3' => $dasharr3,
    'fill3' => $fill3,
    'start' => $start,
    'ticks' => $ticks,
    'color1' => $color1,
    'color2' => $color2,
    'color3' => $color3,
    'label' => $label
  ));
  return "../time-series/chart.php?" . $q;
}
function timeseries_qs($meter_id, $dasharr1, $fill1, $meter_id2, $dasharr2, $fill2, $dasharr3, $fill3, $start, $ticks, $color1, $color2, $color3, $label) {
  return http_build_query(array(
    'meter_id' => $meter_id,
    'dasharr1' => $dasharr1,
    'fill1' => $fill1,
    'meter_id2' => $meter_id2,
    'dasharr2' => $dasharr2,
    'fill2' => $fill2,
    'dasharr3' => $dasharr3,
    'fill3' => $fill3,
    'start' => $start,
    'ticks' => $ticks,
    'color1' => $color1,
    'color2' => $color2,
    'color3' => $color3,
    'label' => $label
  ));
}
if (isset($_POST['submit'])) {
  $stmt = $db->prepare('DELETE FROM time_series_configs WHERE id = ?');
  $stmt->execute(array($_POST['id']));
  $stmt = $db->prepare('UPDATE meters SET timeseries_using = timeseries_using - 1 WHERE id = ?');
  $stmt->execute(array($_POST['meter_id']));
  if ($_POST['meter_id'] !== $_POST['meter_id2']) {
    $stmt = $db->prepare('UPDATE meters SET timeseries_using = timeseries_using - 1 WHERE id = ?');
    $stmt->execute(array($_POST['meter_id2']));
  }
}
if (isset($_POST['refresh'])) {
  $api_id = $db->query("SELECT api_id FROM orgs WHERE id IN (SELECT org_id FROM users_orgs_map WHERE user_id = {$user_id})")->fetchColumn();
  $resolutions = array('live', 'quarterhour', 'hour', 'month');
  for ($i = 0; $i < count($resolutions); $i++) {
    $stmt = $db->prepare('SELECT recorded FROM meter_data
      WHERE meter_id = ? AND resolution = ? AND value IS NOT NULL
      ORDER BY recorded DESC LIMIT 1');
    $stmt->execute(array($_POST['meter_id'], $resolutions[$i]));
    $amount = $stmt->fetchColumn();
    // http://stackoverflow.com/a/3819422/2624391
    exec('bash -c "exec nohup setsid php /var/www/html/oberlin/scripts/update-meter.php --api_id=\''.$api_id.
      '\' --meter_id='.escapeshellarg($_POST['meter_id']).
      ' --res=\''.$resolutions[$i].'\' --amount=\''.$amount.'\' > /dev/null 2>&1 &"');
    if ($_POST['meter_id'] !== $_POST['meter_id2']) {
      $stmt = $db->prepare('SELECT recorded FROM meter_data
      WHERE meter_id = ? AND resolution = ? AND value IS NOT NULL
      ORDER BY recorded DESC LIMIT 1');
      $stmt->execute(array($_POST['meter_id2'], $resolutions[$i]));
      $amount = $stmt->fetchColumn();
      exec('bash -c "exec nohup setsid php /var/www/html/oberlin/scripts/update-meter.php --api_id=\''.$api_id.
      '\' --meter_id='.escapeshellarg($_POST['meter_id2']).
      ' --res=\''.$resolutions[$i].'\' --amount=\''.$amount.'\' > /dev/null 2>&1 &"');
    }
  }
}

if (isset($_POST['save-changes'])) {
  $q = array(
    ':dasharr1' => isset($_POST['dasharr1']) ? $_POST['dasharr1'] : null,
    ':fill1' => isset($_POST['fill1']) ? $_POST['fill1'] : null,
    ':dasharr2' => isset($_POST['dasharr2']) ? $_POST['dasharr2'] : null,
    ':fill2' => isset($_POST['fill2']) ? $_POST['fill2'] : null,
    ':dasharr3' => isset($_POST['dasharr3']) ? $_POST['dasharr3'] : null,
    ':fill3' => isset($_POST['fill3']) ? $_POST['fill3'] : null,
    ':start' => $_POST['start'] ? $_POST['start'] : 0,
    ':color1' => $_POST['color1'],
    ':color2' => $_POST['color2'],
    ':color3' => $_POST['color3'],
    ':label' => ($_POST['label']==null) ? null : $_POST['label']
  );
  $stmt = $db->prepare('UPDATE time_series_configs SET dasharr1 = :dasharr1, fill1 = :fill1, dasharr2 = :dasharr2, fill2 = :fill2, dasharr3 = :dasharr3, fill3 = :fill3, start = :start, color1 = :color1, color2 = :color2, color3 = :color3, label = :label');
  $stmt->execute($q);
}

if (isset($_POST['building-id']) && isset($_POST['building-image'])) {
  $stmt = $db->prepare('UPDATE buildings SET custom_img = ? WHERE id = ?');
  $stmt->execute(array($_POST['building-image'], $_POST['building-id']));
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Manage time series</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <!-- Modal -->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modal-title" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form action="" method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="modal-title">Edit time series</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="id" id="id" value="">
              <div class="form-group">
                <label for="label">Title</label>
                <input type="text" class="form-control" id="label" name="label" value="">
              </div>
              <div class="form-group">
                <label for="color1">Primary variable</label>
                <input type="color" class="form-control" id="color1" name="color1" value="" style="height:50px;padding:0px">
                <label class="custom-control custom-checkbox">
                  <input id="dasharr1" name="dasharr1" type="checkbox" class="custom-control-input">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Dashed</span>
                </label>
                <label class="custom-control custom-checkbox">
                  <input id="fill1" name="fill1" type="checkbox" class="custom-control-input" checked>
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Filled</span>
                </label>
              </div>
              <div class="form-group">
                <label for="color2">Historical chart</label>
                <input type="color" class="form-control" id="color2" name="color2" value="" style="height:50px;padding:0px">
                <label class="custom-control custom-checkbox">
                  <input id="dasharr2" name="dasharr2" type="checkbox" class="custom-control-input">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Dashed</span>
                </label>
                <label class="custom-control custom-checkbox">
                  <input id="fill2" name="fill2" type="checkbox" class="custom-control-input" checked>
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Filled</span>
                </label>
              </div>
              <div class="form-group">
                <label for="color3">Secondary variable</label>
                <input type="color" class="form-control" id="color3" name="color3" value="" style="height:50px;padding:0px">
                <label class="custom-control custom-checkbox">
                  <input id="dasharr3" name="dasharr3" type="checkbox" class="custom-control-input">
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Dashed</span>
                </label>
                <label class="custom-control custom-checkbox">
                  <input id="fill3" name="fill3" type="checkbox" class="custom-control-input" checked>
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">Filled</span>
                </label>
              </div>
              <div class="form-group">
                <label for="start">Start Y-axis scale from</label>
                <input type="text" class="form-control" id="start" name="start" value="">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary" name="save-changes">Save changes</button>
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
        <div class="col-xs-12">
          <table class="table table-responsive table-sm">
            <thead>
              <tr>
                <th>Raw chart</th>
                <th>URL</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $page = (empty($_GET['page'])) ? 0 : intval($_GET['page']) - 1;
              $count = $db->query("SELECT COUNT(*) FROM time_series_configs WHERE user_id = {$user_id}")->fetchColumn();
              $limit = 3;
              $offset = $limit * $page;
              $final_page = ceil($count / $limit);
              foreach ($db->query("SELECT * FROM time_series_configs WHERE user_id = {$user_id} ORDER BY id ASC LIMIT {$offset}, {$limit}") as $row) {
                echo "<tr><td>";
                  $query_string = timeseries_qs($row['meter_id'], $row['dasharr1'], $row['fill1'], $row['meter_id2'], $row['dasharr2'], $row['fill2'], $row['dasharr3'], $row['fill3'], $row['start'], $row['ticks'], $row['color1'], $row['color2'], $row['color3'], $row['label']);
                  $start_url = "{$_SERVER['HTTP_HOST']}/{$symlink}/time-series/index.php?{$query_string}";
                  $url = timeseriesURL($row['meter_id'], $row['dasharr1'], $row['fill1'], $row['meter_id2'], $row['dasharr2'], $row['fill2'], $row['dasharr3'], $row['fill3'], $row['start'], $row['ticks'], $row['color1'], $row['color2'], $row['color3'], $row['label']);

                  echo "<p id='label-{$row['id']}'>";
                  if ($row['label'] == null) {
                    echo $db->query("SELECT buildings.name FROM buildings WHERE org_id IN (SELECT org_id FROM users_orgs_map WHERE user_id = {$user_id}) AND buildings.id IN (SELECT meters.building_id FROM meters WHERE meters.id = {$row['meter_id']}) LIMIT 1")->fetchColumn() . ' ';
                    echo $db->query("SELECT name FROM meters WHERE id = {$row['meter_id']}")->fetchColumn();
                  } else { echo $row['label']; }
                  echo '</p>';
                  $id = $db->query("SELECT id FROM buildings WHERE org_id IN (SELECT org_id FROM users_orgs_map WHERE user_id = {$user_id}) AND custom_img IS NOT NULL AND buildings.id IN (SELECT meters.building_id FROM meters WHERE meters.id = {$row['meter_id']})")->fetchColumn();
                  if (!empty($id)) {
                    $rand = uniqid();
                    echo "<form action='' method='POST' class='form-inline'>
                          <input type='hidden' name='building-id' value='{$id}'>
                          <label class='sr-only' for='{$rand}'>Building image URL</label>
                          <input type='text' class='form-control form-control-sm mb-2 mr-sm-2 mb-sm-0' id='{$rand}' placeholder='Building image URL' name='building-image'>
                          <button type='submit' class='btn btn-sm btn-primary'>Submit</button>
                        </form>";
                  }

                  echo "<object style='max-width:400px' type='image/svg+xml' data='{$url}'></object></td>\n";
                  echo "<td>
                  <form>
                    <div class='form-check'>
                      <label class='form-check-label'>
                        <input type='checkbox' class='form-check-input' id='prettyurl-{$row['id']}'>
                        Pretty URL
                      </label>
                    </div>
                    <div class='form-check'>
                      <label class='form-check-label'>
                        <input type='checkbox' class='form-check-input' id='html-{$row['id']}' checked>
                        Include chart in HTML page
                      </label>
                    </div>
                    <div class='form-check form-check-inline' id='extraoption1{$row['id']}'>
                      <label class='form-check-label'>
                        <input type='checkbox' class='form-check-input' id='title-{$row['id']}' checked>
                        Include title
                      </label>
                      <input class='form-control form-control-sm title-font-size' data-timeseries_id='{$row['id']}' id='title_size-{$row['id']}' type='text' placeholder='Font size'>
                    </div>
                    <div class='form-check' id='extraoption2{$row['id']}'>
                      <label class='form-check-label'>
                        <input type='checkbox' class='form-check-input' id='img-{$row['id']}' checked>
                        Include building image
                      </label>
                    </div>
                  </form>
                  <p style='word-wrap: break-word;max-width:300px'><span id='displayurl-{$row['id']}' data-querystring='{$query_string}'>{$start_url}</span><span id='title_size_span-{$row['id']}'></span></p>
                  </td>";
                  echo "<td><p><button type='button' class='btn btn-primary' data-toggle='modal' data-target='#modal'
                    data-dasharr1='{$row['dasharr1']}'
                    data-fill1='{$row['fill1']}'
                    data-color1='{$row['color1']}'
                    data-dasharr2='{$row['dasharr2']}'
                    data-fill2='{$row['fill2']}'
                    data-color2='{$row['color2']}'
                    data-dasharr3='{$row['dasharr3']}'
                    data-fill3='{$row['fill3']}'
                    data-color3='{$row['color3']}'
                    data-label='{$row['label']}'
                    data-id='{$row['id']}'
                    >Edit</button></p></td>";
                  echo "<td>
                        <form action='' method='POST'>
                        <input type='hidden' name='id' value='{$row['id']}'>
                        <input type='hidden' name='meter_id' value='{$row['meter_id']}'>
                        <input type='hidden' name='meter_id2' value='{$row['meter_id2']}'>
                        <input type='submit' class='btn btn-danger' value='Delete' name='submit'>
                        </form>
                        </td>";
                echo "</tr>";
              } ?>
            </tbody>
          </table>
          <nav aria-label="Page navigation" class="text-center">
            <ul class="pagination pagination-lg" style="display: inline-flex">
              <?php if ($page > 0) { ?>
              <li class="page-item">
                <a class="page-link" href="?sort=<?php echo (isset($_GET['sort'])) ? $_GET['sort'] : ''; ?>&page=<?php echo $page ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                  <span class="sr-only">Previous</span>
                </a>
              </li>
              <?php }
              for ($i = 1; $i <= $final_page; $i++) {
                if ($page + 1 === $i) {
                  echo '<li class="page-item active"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                }
                else {
                  echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                }
              }
              if ($page + 1 < $final_page) { ?>
              <li class="page-item">
                <a class="page-link" href="?sort=<?php echo (isset($_GET['sort'])) ? $_GET['sort'] : ''; ?>&page=<?php echo $page + 2 ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                  <span class="sr-only">Next</span>
                </a>
              </li>
              <?php } ?>
            </ul>
          </nav>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
    var base_url = '<?php echo $_SERVER['HTTP_HOST'] . "/{$symlink}"; ?>/time-series/';
    var page = 'index.php?';
    $('.form-check-input').on('change', function() {
      var clicked = $(this).attr('id').split('-');
      var action = clicked[0];
      var timeseries_id = clicked[1];
      if (action === 'title' && !$(this).is(':checked')) {
        $('#img-'+timeseries_id).prop('disabled', true);
      } else if (action === 'title') {
        $('#img-'+timeseries_id).prop('disabled', false);
      }
      if (action === 'html' && !$(this).is(':checked')) {
        $('#extraoption1' + timeseries_id + ', #extraoption2' + timeseries_id).css('display', 'none');
      } else if (action === 'html') {
        $('#extraoption1' + timeseries_id + ', #extraoption2' + timeseries_id).css('display', '');
      }
      if ($('#html-' + timeseries_id).is(':checked')) {
        page = 'index.php?';
      } else {
        page = 'chart.php?';
      }
      var displayurl = $('#displayurl-' + timeseries_id);
      if ($('#prettyurl-' + timeseries_id).is(':checked')) {
        var qs = 'timeseriesconfig=' + timeseries_id;
      } else {
        var qs = displayurl.data('querystring');
      }
      var titlechecked = $('#title-' + timeseries_id).is(':checked');
      if (titlechecked && $('#img-' + timeseries_id).is(':checked')) {
        var extrabit = '';
      } else if (titlechecked) {
        var extrabit = '&webpage=title';
      } else {
        var extrabit = '&webpage=notitle';
      }
      displayurl.text(base_url + page + qs + extrabit);
    });

    $('#modal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      $.each(button.data(), function(i, v) {
        if (i !== 'target' && i !== 'toggle') {
          // console.log('"' + i + '":"' + v + '",');
          $('#' + i).val(v);
        }
      });
      // var modal = $(this)
      // modal.find('.modal-title').text('New message to ' + recipient)
      // modal.find('.modal-body input').val(recipient)
    });

    $('.title-font-size').on('input', function() {
      var timeseries_id = $(this).data('timeseries_id');
      var title_size = $(this).val();
      if (title_size.length > 0) {
        $('#title_size_span-' + timeseries_id).text('&title_size='+encodeURIComponent(title_size));
      } else {
        $('#title_size_span-' + timeseries_id).text('');
      }
    });

    </script>
  </body>
</html>