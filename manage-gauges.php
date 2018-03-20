<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';
function gaugeURL($rv_id, $meter_id, $color, $bg, $height, $width, $font_family, $title, $title2, $border_radius, $rounding, $ver, $units) {
  $q = http_build_query(array(
    'rv_id' => $rv_id,
    'meter_id' => $meter_id,
    'color' => $color,
    'bg' => $bg,
    'height' => $height,
    'width' => $width,
    'font_family' => $font_family,
    'title' => $title,
    'title2' => $title2,
    'border_radius' => $border_radius,
    'rounding' => $rounding,
    'ver' => $ver,
    'units' => $units,
  ));
  return "https://oberlindashboard.org/oberlin/gauges/gauge.php?" . $q;
}
if (isset($_POST['edit'])) {
  $q = array(
    ':meter_id' => $_POST['edit-meter'],
    ':color' => $_POST['edit-color'],
    ':bg' => $_POST['edit-bg'],
    ':height' => $_POST['edit-height'],
    ':width' => $_POST['edit-width'],
    ':font_family' => $_POST['edit-fontfamily'],
    ':title' => $_POST['edit-title'],
    ':title2' => $_POST['edit-title2'],
    ':border_radius' => $_POST['edit-borderradius'],
    ':rounding' => intval($_POST['edit-rounding']),
    ':ver' => $_POST['edit-radio'],
    ':units' => $_POST['edit-units'],
    ':id' => $_POST['gauge-id']
  );
  $stmt = $db->prepare('UPDATE gauges SET meter_id = :meter_id, color = :color, bg = :bg, height = :height, width = :width, font_family = :font_family, title = :title, title2 = :title2, border_radius = :border_radius, rounding = :rounding, ver = :ver, units = :units WHERE id = :id');
  $stmt->execute($q);
  $stmt = $db->prepare('UPDATE meters SET gauges_using = gauges_using + 1 WHERE id = ?');
  $stmt->execute(array($_POST['edit-meter']));
  $stmt = $db->prepare('UPDATE meters SET gauges_using = gauges_using - 1 WHERE id = ? AND gauges_using != 0');
  $stmt->execute(array($_POST['meter-id']));
}

if (isset($_POST['delete'])) {
  $stmt = $db->prepare('UPDATE meters SET gauges_using = gauges_using - 1 WHERE id = ?');
  $stmt->execute(array($_POST['meterid']));
  $stmt = $db->prepare('DELETE FROM gauges WHERE id = ?');
  $stmt->execute(array($_POST['gaugeid']));
}

$dropdown_html = '';
$buildings = $db->query("SELECT * FROM buildings WHERE org_id IN (SELECT org_id FROM users_orgs_map WHERE user_id = {$user_id}) ORDER BY name ASC");
foreach ($buildings->fetchAll() as $building) {
  $dropdown_html .= "<optgroup label='{$building['name']}'>";
  $stmt = $db->prepare('SELECT id, name FROM meters WHERE building_id = ?');
  $stmt->execute(array($building['id']));
  foreach($stmt->fetchAll() as $meter) {
    $dropdown_html .= "<option value='{$meter['id']}'>{$meter['name']}</option>";
  }
  $dropdown_html .= '</optgroup>';
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Manage gauges</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <div class="modal fade" id="edit-modal">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form action="" method="POST">
            <div class="modal-header">
              <h4 class="modal-title">Edit gauge</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <input type="hidden" value="" id="gauge-id" name="gauge-id">
              <input type="hidden" value="" id="meter-id" name="meter-id">
              <input type="hidden" value="" id="rv-id" name="rv-id">
              <div class="form-group">
                <label for="edit-meter">Meter</label>
                <select style="width:100%" name="edit-meter" id="edit-meter" class="custom-select">
                  <?php echo $dropdown_html; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="edit-color">Color</label>
                <input type="color" class="form-control" id="edit-color" name="edit-color" value="" style="height:50px">
              </div>
              <div class="form-group">
                <label for="edit-bg">Background</label>
                <input type="color" class="form-control" id="edit-bg" name="edit-bg" value="" style="height:50px">
              </div>
              <div class="form-group">
                <label for="edit-height">Height</label>
                <input type="text" class="form-control" id="edit-height" name="edit-height" value="">
              </div>
              <div class="form-group">
                <label for="edit-width">Width</label>
                <input type="text" class="form-control" id="edit-width" name="edit-width" value="">
              </div>
              <div class="form-group">
                <label for="edit-fontfamily">Font family</label>
                <input type="text" class="form-control" id="edit-fontfamily" name="edit-fontfamily" value="">
              </div>
              <div class="form-group">
                <label for="edit-title">Title</label>
                <input type="text" class="form-control" id="edit-title" name="edit-title" value="">
              </div>
              <div class="form-group">
                <label for="edit-title2">Title line 2</label>
                <input type="text" class="form-control" id="edit-title2" name="edit-title2" value="">
              </div>
              <div class="form-group">
                <label for="edit-borderradius">Border radius</label>
                <input type="text" class="form-control" id="edit-borderradius" name="edit-borderradius" value="">
              </div>
              <div class="form-group">
                <label for="edit-rounding">Rounding precision</label>
                <input type="text" class="form-control" id="edit-rounding" name="edit-rounding" value="">
              </div>
              <div class="form-group">
                <label for="edit-units">Units</label>
                <input type="text" class="form-control" id="edit-units" name="edit-units" value="">
              </div>
              <label class="custom-control custom-radio">
                <input id="edit-html" value="html" name="edit-radio" type="radio" class="custom-control-input">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">HTML version</span>
              </label>
              <label class="custom-control custom-radio">
                <input id="edit-svg" value="svg" name="edit-radio" type="radio" class="custom-control-input">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">SVG version</span>
              </label>
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
        <div class="col-sm-12">
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6">
          <h1>Gauges</h1>
        </div>
        <div class="col-sm-6">
          <form action="" method="GET" class="form-inline" style="margin-top:10px; float: right;">
            <input type="hidden" name="sort" value="<?php echo (empty($_GET['page'])) ? '' : $_GET['sort'] ?>">
            <input type="hidden" name="page" value="1">
            <div class="form-group">
              <input type="text" class="form-control" name="search" value="<?php echo (empty($_GET['search'])) ? '' : $_GET['search'] ?>" placeholder="Search" value="Search">
            </div>
            <input type="submit" class="btn btn-primary" value="Search">
            <small style="display:block" class="text-muted" id="num-results"></small>
          </form>
          <form action="" method="GET" class="form-inline" style="margin-top: 10px;margin-right:10px;float: right;">
            <div class="form-group">
              <select class="custom-select" name="sort" id="sortform">
                <option value="newest" <?php echo (empty($_GET['sort']) || $_GET['sort'] === 'newest') ? 'selected' : ''; ?>>Newest first</option>
                <option value="oldest" <?php echo (!empty($_GET['sort']) && $_GET['sort'] === 'oldest') ? 'selected' : ''; ?>>Oldest first</option>
                <option value="title" <?php echo (!empty($_GET['sort']) && $_GET['sort'] === 'title') ? 'selected' : ''; ?>>Alphabetical</option>
                <option value="meterid" <?php echo (!empty($_GET['sort']) && $_GET['sort'] === 'meterid') ? 'selected' : ''; ?>>Sort by meter ID</option>
              </select>
            </div>
            <?php if (isset($_GET['search'])) { echo "<input type=\"hidden\" name=\"search\" value=\"{$_GET['search']}\">"; } ?>
          </form>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <?php
          $page = (empty($_GET['page'])) ? 0 : intval($_GET['page']) - 1;
          if (isset($_GET['search']) && trim($_GET['search']) !== '') {
            $count = $db->prepare('SELECT COUNT(*) FROM gauges WHERE user_id = ? AND CONCAT(title, " ", title2) LIKE ?');
            $count->execute(array($user_id, "%{$_GET['search']}%"));
            $count = $count->fetch()['COUNT(*)'];
          }
          else {
            $count = $db->query("SELECT COUNT(*) FROM gauges WHERE user_id = {$user_id}")->fetch()['COUNT(*)'];
          }
          $limit = 5;
          $offset = $limit * $page;
          $final_page = ceil($count / $limit);
          $_GET['sort'] = (empty($_GET['sort'])) ? 'newest' : $_GET['sort'];
          switch ($_GET['sort']) {
            case 'newest':
              $orderby = 'id DESC';
              break;
            case 'oldest':
              $orderby = 'id ASC';
              break;
            case 'meterid':
              $orderby = 'meter_id ASC, id DESC';
              break;
            case 'title':
              $orderby = 'title ASC, title2 ASC, id DESC';
              break;
            default:
              $orderby = 'id DESC';
              break;
          }
          $search = (empty($_GET['search'])) ? 'WHERE user_id = ?' : 'WHERE user_id = ? AND CONCAT(title, " ", title2) LIKE ? '; // Use parameters for sql injection!
          $stmt = $db->prepare("SELECT * FROM gauges {$search} ORDER BY {$orderby} LIMIT {$offset}, {$limit}");
          if (empty($_GET['search'])) {
            $stmt->execute(array($user_id));
          }
          else {
            $stmt->execute(array($user_id, "%{$_GET['search']}%"));
          }
          if ($stmt->rowCount() === 0) {
            echo '<h1 class="text-muted" style="margin-top:20px;margin-bottom:50px">No Results</h1>';
          }
          else {
            ?>
            <table class="table" style="width: 100%;margin-top: 20px">
              <thead>
                <tr>
                  <th>Meter</th>
                  <th>URL</th>
                  <th>Color</th>
                  <th>Background</th>
                  <th>Font</th>
                  <th>Edit</th>
                  <th>Delete</th>
                </tr>
              </thead>
              <tbody>
            <?php
              foreach ($stmt->fetchAll() as $gauge) {
                $url = gaugeURL($gauge['rv_id'], $gauge['meter_id'], $gauge['color'], $gauge['bg'], $gauge['height'], $gauge['width'], $gauge['font_family'], $gauge['title'], $gauge['title2'], $gauge['border_radius'], $gauge['rounding'], $gauge['ver'], $gauge['units']);
              ?>
              <tr>
                <td><iframe style="min-height:190px" src="<?php echo $url; ?>" frameborder="0"></iframe></td>
                <td><a href="<?php echo $url; ?>" target="_blank">Link</a></td>
                <td><?php echo $gauge['color']; ?></td>
                <td><?php echo $gauge['bg']; ?></td>
                <td><?php echo $gauge['font_family']; ?></td>
                <td><a class="btn btn-primary edit-gauge" data-url="<?php echo $url ?>" data-gaugeid="<?php echo $gauge['id'] ?>" href="#">Edit</a></td>
                <td>
                  <form action="" method="POST">
                    <input type="hidden" name="gaugeid" value="<?php echo $gauge['id']; ?>">
                    <input type="hidden" name="meterid" value="<?php echo $gauge['meter_id']; ?>">
                    <input type="submit" name="delete" value="Delete" class="btn btn-danger">
                  </form>
                </td>
              </tr>
              <?php }  ?>
            </tbody>
          </table>
          <nav aria-label="Page navigation" class="text-xs-center">
            <ul class="pagination pagination-lg">
              <?php if ($page > 0) { ?>
              <li class="page-item">
                <a class="page-link" href="?sort=<?php echo $_GET['sort'] ?>&page=<?php echo $page ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                  <span class="sr-only">Previous</span>
                </a>
              </li>
              <?php }
              $urlencoded = (isset($_GET['search'])) ? urlencode($_GET['search']) : '';
              for ($i = 1; $i <= $final_page; $i++) {
                if ($page + 1 === $i) {
                  echo '<li class="page-item active"><a class="page-link" href="?sort='.$_GET['sort'].'&page=' . $i . '&search=' .$urlencoded. '">' . $i . '</a></li>';
                }
                else {
                  echo '<li class="page-item"><a class="page-link" href="?sort='.$_GET['sort'].'&page=' . $i . '&search=' .$urlencoded. '">' . $i . '</a></li>';
                }
              }
              if ($page + 1 < $final_page) { ?>
              <li class="page-item">
                <a class="page-link" href="?sort=<?php echo $_GET['sort'] ?>&page=<?php echo $page + 2 ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                  <span class="sr-only">Next</span>
                </a>
              </li>
              <?php } ?>
            </ul>
          </nav>
          <?php } // End else rowcount == 0 ?>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script>

$('.edit-gauge').click(function(e) {
  e.preventDefault();
  $('#edit-modal').modal('show');
  var gaugeid = $(this).data('gaugeid'),
      url = $(this).data('url');
  var rvid = getParameterByName('rv_id', url);
  var meterid = getParameterByName('meter_id', url);
  var color = getParameterByName('color', url);
  var bg = getParameterByName('bg', url);
  var height = getParameterByName('height', url);
  var width = getParameterByName('width', url);
  var fontfamily = getParameterByName('font_family', url);
  var title = getParameterByName('title', url);
  var title2 = getParameterByName('title2', url);
  var borderradius = getParameterByName('border_radius', url);
  var rounding = getParameterByName('rounding', url);
  var ver = getParameterByName('ver', url);
  var units = getParameterByName('units', url);
  var db = '1';
  $('#rv-id').val(rvid);
  $('#meter-id').val(meterid);
  $('#edit-meter').val(meterid)
  $('#gauge-id').val(gaugeid);
  $('#edit-color').val(color);
  $('#edit-bg').val(bg);
  $('#edit-height').val(height);
  $('#edit-width').val(width);
  $('#edit-fontfamily').val(fontfamily);
  $('#edit-title').val(title);
  $('#edit-title2').val(title2);
  $('#edit-borderradius').val(borderradius);
  $('#edit-rounding').val(rounding);
  if (ver === 'html') {
    $('#edit-html').prop('checked', true);
  }
  else {
    $('#edit-svg').prop('checked', true);
  }
});
// http://stackoverflow.com/a/901144/2624391
function getParameterByName(name, url) {
  if (!url) url = window.location.href;
  name = name.replace(/[\[\]]/g, "\\$&");
  var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
    results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return '';
  return decodeURIComponent(results[2].replace(/\+/g, " "));
}
$('#sortform').change(function() {
  this.form.submit();
});
$('#num-results').text(
  '<?php echo (empty($_GET['search'])) ? '' : $count . ' Results for "' . $_GET['search'] . '"'?>');
</script>
  </body>
</html>