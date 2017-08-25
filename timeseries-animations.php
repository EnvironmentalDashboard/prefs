<?php
require '../includes/db.php';
require 'includes/check-signed-in.php';
if (!empty($_POST['name'])) {
  if (!empty($_POST['old_name'])) {
    $stmt = $db->prepare('UPDATE time_series SET name = ?, bin1 = ?, bin2 = ?, bin3 = ?, bin4 = ?, bin5 = ? WHERE name = ?');
    $stmt->execute(array($_POST['name'], $_POST['bin1'], $_POST['bin2'], $_POST['bin3'], $_POST['bin4'], $_POST['bin5'], $_POST['old_name']));
  }
  else if (file_exists($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    $dir = __dir__;
    $uploaddir = dirname($dir) . '/time-series/images/';
    $filename = $_POST['name'];
    $uploadfile = $uploaddir . basename($filename);
    move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
    $new_len = `python /var/www/html/oberlin/time-series/gifduration/gifduration.py /var/www/html/oberlin/time-series/images/{$filename}.gif`;
    $stmt = $db->prepare('INSERT INTO time_series (name, user_id, bin1, bin2, bin3, bin4, bin5, length) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(array($filename, $user_id, $_POST['bin1'], $_POST['bin2'], $_POST['bin3'], $_POST['bin4'], $_POST['bin5'], $new_len));
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
    <title>Edit Time Series Animations</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <style>
      select {padding:10px;}
      .modal-title {display: inline !important;}
    </style>
  </head>
  <body style="padding-top:5px">
    <!-- Modal -->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form accept="" method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="title"></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group" id="img">
                <label for="file">Image</label>
                <input type="file" class="form-control-file" id="file" name="file">
              </div>
              <div class="form-group">
                <label for="name">Filename</label>
                <input type="text" class="form-control" id="name" placeholder="Filename">
              </div>
              <div class="form-group">
                <label for="bin1">Bin 1</label>
                <select id="bin1" name="bin1">
                  <option value="0">0</option>
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="4">4</option>
                  <option value="5">5</option>
                  <option value="6">6</option>
                  <option value="7">7</option>
                  <option value="8">8</option>
                  <option value="9">9</option>
                  <option value="10">10</option>
                </select>
              </div>
              <div class="form-group">
                <label for="bin2">Bin 2</label>
                <select id="bin2" name="bin2">
                  <option value="0">0</option>
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="4">4</option>
                  <option value="5">5</option>
                  <option value="6">6</option>
                  <option value="7">7</option>
                  <option value="8">8</option>
                  <option value="9">9</option>
                  <option value="10">10</option>
                </select>
              </div>
              <div class="form-group">
                <label for="bin3">Bin 3</label>
                <select id="bin3" name="bin3">
                  <option value="0">0</option>
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="4">4</option>
                  <option value="5">5</option>
                  <option value="6">6</option>
                  <option value="7">7</option>
                  <option value="8">8</option>
                  <option value="9">9</option>
                  <option value="10">10</option>
                </select>
              </div>
              <div class="form-group">
                <label for="bin4">Bin 4</label>
                <select id="bin4" name="bin4">
                  <option value="0">0</option>
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="4">4</option>
                  <option value="5">5</option>
                  <option value="6">6</option>
                  <option value="7">7</option>
                  <option value="8">8</option>
                  <option value="9">9</option>
                  <option value="10">10</option>
                </select>
              </div>
              <div class="form-group">
                <label for="bin5">Bin 5</label>
                <select id="bin5" name="bin5">
                  <option value="0">0</option>
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="4">4</option>
                  <option value="5">5</option>
                  <option value="6">6</option>
                  <option value="7">7</option>
                  <option value="8">8</option>
                  <option value="9">9</option>
                  <option value="10">10</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <input type="hidden" name="old_name" id="old_name">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Submit</button>
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
      <div class="row">
        <div class="col-xs-12">
          <div style="clear: both;height: 20px"></div>
          <p><a class="btn btn-secondary" href="#" data-toggle="modal" data-target="#modal" data-btn="new">Add new gif</a></p>
          <table class="table">
            <thead>
              <tr>
                <th>&nbsp;</th>
                <th>Filename</th>
                <th>Length</th>
                <th>Bin 1</th>
                <th>Bin 2</th>
                <th>Bin 3</th>
                <th>Bin 4</th>
                <th>Bin 5</th>
                <th>Edit</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $page = (empty($_GET['page'])) ? 0 : intval($_GET['page']) - 1;
              $count = $db->query("SELECT COUNT(*) FROM time_series WHERE length > 0 AND user_id = {$user_id}")->fetch()['COUNT(*)'];
              $limit = 20;
              $offset = $limit * $page;
              $final_page = ceil($count / $limit);
              foreach ($db->query("SELECT * FROM time_series WHERE length > 0 AND user_id = {$user_id} ORDER BY name LIMIT {$offset}, {$limit}") as $row) {
                echo '<tr>';
                  echo "<td><img class=\"img-fluid\" src=\"../time-series/images/{$row['name']}.gif\"></td>";
                  echo "<td><a href='../time-series/images/{$row['name']}.gif' target='_blank'>{$row['name']}</a></td>";
                  echo "<td>".round($row['length']/1000,1)."s</td>";
                  echo "<td>{$row['bin1']}</td>";
                  echo "<td>{$row['bin2']}</td>";
                  echo "<td>{$row['bin3']}</td>";
                  echo "<td>{$row['bin4']}</td>";
                  echo "<td>{$row['bin5']}</td>";
                  echo "<td><a href='#' data-toggle='modal' data-target='#modal' data-btn='{$row['name']}'>Edit</a></td>";
                echo '</tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="row">
        <nav aria-label="Page navigation" class="text-center">
            <ul class="pagination pagination-lg">
              <?php if ($page > 0) { ?>
              <li class="page-item">
                <a class="page-link" href="?sort=<?php echo $_GET['sort'] ?>&page=<?php echo $page ?>" aria-label="Previous">
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
                <a class="page-link" href="?page=<?php echo $page + 2 ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                  <span class="sr-only">Next</span>
                </a>
              </li>
              <?php } ?>
            </ul>
          </nav>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
    $('#modal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var btn = button.data('btn');
      var modal = $(this)
      if (btn == 'new') {
        modal.find('.modal-title').text('New gif');
        $('#old_name').val('');
        $('#img').css('display', 'initial');
      }
      else {
        modal.find('.modal-title').text('Edit ' + btn);
        $('#old_name').val(btn);
        $('#img').css('display', 'none');
      }
    })
    </script>
  </body>
</html>