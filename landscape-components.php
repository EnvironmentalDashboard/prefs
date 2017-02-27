<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
if (isset($_POST['submit'])) {
  if (!file_exists($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
    $stmt = $db->prepare("UPDATE cwd_landscape_components
      SET pos = ?, widthxheight = ?, title = ?, link = ?, `text` = ?, `text_pos` = ?, `order` = ?
      WHERE component = ? LIMIT 1");
    $stmt->execute(array(
      $_POST['pos'],
      $_POST['widthxheight'],
      $_POST['title'],
      $_POST['link'],
      $_POST['text'],
      $_POST['text_pos'],
      $_POST['order'],
      $_POST['component']
    ));
  }
  else {
    $dir = __dir__; // Right now is /home/admin060606/public_html/lucid/prefs
    $uploaddir = dirname($dir) . '/cwd/img/';
    $filename = $_FILES['file']['name'];
    $uploadfile = $uploaddir . basename($filename);
    move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
    $stmt = $db->prepare("UPDATE cwd_landscape_components
      SET pos = ?, widthxheight = ?, title = ?, link = ?, img = ?, `text` = ?, `text_pos` = ?, `order` = ?
      WHERE component = ? LIMIT 1");
    $stmt->execute(array(
      $_POST['pos'],
      $_POST['widthxheight'],
      $_POST['title'],
      $_POST['link'],
      'http://'.$_SERVER['HTTP_HOST'].'/cwd/img/' . $filename,
      $_POST['text'],
      $_POST['text_pos'],
      $_POST['order'],
      $_POST['component']
    ));
  }
}
if (isset($_POST['add-landscape-component'])) {
  $dir = __dir__; // Right now is /home/admin060606/public_html/lucid/prefs
  $uploaddir = dirname($dir) . '/cwd/img/';
  $uploadfile = $uploaddir . basename($filename);
  move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
  $stmt = $db->prepare("INSERT INTO cwd_landscape_components
    (component, pos, widthxheight, title, link, img, `text`, text_pos, `order`)
    VALUES (:c, :p, :wh, :t, :l, :i, :txt, :txtp, :o)");
  $stmt->execute(array(
    ':c' => substr(str_shuffle(MD5(microtime())), 0, 10), // Generate random string for the component id
    ':p' => $_POST['pos'],
    ':wh' => str_replace(' ', '', $_POST['wxh']),
    ':t' => $_POST['title'],
    ':l' => $_POST['link'],
    ':i' => 'http://'.$_SERVER['HTTP_HOST'].'/cwd/img/' . $filename,
    ':txt' => $_POST['text'],
    ':txtp' => $_POST['text_pos'],
    ':o' => $_POST['order']
  ));

}
if (isset($_GET['delete'])) {
  $stmt = $db->prepare('DELETE FROM cwd_landscape_components WHERE component = ?');
  $stmt->execute(array($_GET['delete']));
}

if (isset($_GET['disable'])) {
  $stmt = $db->prepare('UPDATE cwd_landscape_components SET hidden = 1 WHERE component = ?');
  $stmt->execute(array($_GET['disable']));
}
if (isset($_GET['enable'])) {
  $stmt = $db->prepare('UPDATE cwd_landscape_components SET hidden = 0 WHERE component = ?');
  $stmt->execute(array($_GET['enable']));
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
    <div class="modal fade" id="edit-modal">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form enctype="multipart/form-data" action="" method="POST">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <h4 class="modal-title">Edit Component</h4>
            </div>
            <div class="modal-body">
              <input type="hidden" value="" id="id" name="component">
              <div class="form-group">
                <label for="edit-pos">x,y coordinates of component</label>
                <input type="text" class="form-control" id="edit-pos" name="pos" value="">
              </div>
              <div class="form-group">
                <label for="edit-widthxheight">Width x Height</label>
                <input type="text" class="form-control" id="edit-widthxheight" name="widthxheight" value="">
              </div>
              <div class="form-group">
                <label for="edit-title">Title</label>
                <input type="text" class="form-control" id="edit-title" name="title" value="">
              </div>
              <div class="form-group">
                <label for="edit-link">Link</label>
                <input type="text" class="form-control" id="edit-link" name="link" value="">
              </div>
              <div class="form-group">
                <label for="edit-text_pos">x,y coordinates of text box</label>
                <input type="text" class="form-control" id="edit-text_pos" name="text_pos" value="">
              </div>
              <div class="form-group">
              <label for="edit-order">Z-order</label>
              <select class="c-select" name="order" id="edit-order">
                <?php for ($i = 1; $i < 255; $i++) { 
                  echo "<option value=\"{$i}\">{$i}</option>";
                } ?>
              </select>
            </div>
              <div class="form-group">
                <label for="edit-text">Text</label>
                <textarea class="form-control" name="text" id="edit-text" rows="6"></textarea>
              </div>
              <div class="form-group" id="hide-file">
                <label for="edit-file">Upload image</label>
                <input type="file" class="form-control-file" id="edit-file" name="file" value="">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" name="submit" class="btn btn-primary">Save changes</button>
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
          <h4>New component</h4>
          <form enctype="multipart/form-data" action="" method="POST">
            <div class="form-group">
              <label for="pos">x,y coordinates of component</label>
              <input type="text" class="form-control" name="pos" id="pos">
              <small class="text-muted">Coordinates seperated by a comma</small>
            </div>
            <div class="form-group">
              <label for="wxh">Width x Height</label>
              <input type="text" class="form-control" name="wxh" id="wxh">
              <small class="text-muted">Height and width seperated by an "x"</small>
            </div>
            <div class="form-group">
              <label for="title">Title</label>
              <input type="text" class="form-control" name="title" id="title">
            </div>
            <div class="form-group">
              <label for="link">Link</label>
              <input type="text" class="form-control" name="link" id="link">
              <small class="text-muted">The "Read more" link</small>
            </div>
            <div class="form-group">
              <label for="text">Description</label>
              <textarea name="text" class="form-control" id="text" cols="30" rows="10"></textarea>
            </div>
            <div class="form-group">
              <label for="text_pos">x,y coordinates of text box</label>
              <input type="text" class="form-control" name="text_pos" id="text_pos">
            </div>
            <div class="form-group">
              <label for="order">Z-order</label>
              <select class="c-select" name="order" id="order">
                <?php for ($i = 1; $i < 255; $i++) { 
                  echo "<option value=\"{$i}\">{$i}</option>";
                } ?>
              </select>
              <p><small class="text-muted">A larger <a target="_blank" href="https://en.wikipedia.org/wiki/Z-order">z-order</a> means that component will be placed on top of components with a smaller z-order.</small></p>
            </div>
            <div class="form-group">
              <label for="file">Upload image</label>
              <input type="file" class="form-control-file" id="file" name="file" value="">
            </div>
            <input type="submit" name="add-landscape-component" value="Add component" class="btn btn-primary">
          </form>
        </div>
        <div class="col-sm-9">
          <h4>Landscape components</h4>
          <table class="table table-responsive">
            <thead>
              <tr>
                <th>&nbsp;</th>
                <th>Title</th>
                <th>Link</th>
                <th>Description</th>
                <th>Edit</th>
                <th>Visibility</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($db->query('SELECT * FROM cwd_landscape_components ORDER BY hidden DESC, removable, component') as $row) { ?>
              <tr>
                <td><?php echo "<img class='img-fluid' src='{$row['img']}' />"; ?></td>
                <td><?php echo $row['title'] ?></td>
                <td style="width:15%;"><a href="<?php echo $row['link'] ?>" target="_blank">Open link</a></td>
                <td><?php echo $row['text'] ?></td>
                <td><a class="btn btn-primary edit"
                data-component="<?php echo $row['component'] ?>"
                data-pos="<?php echo $row['pos'] ?>"
                data-wxh="<?php echo $row['widthxheight'] ?>"
                data-title="<?php echo $row['title'] ?>"
                data-link="<?php echo $row['link'] ?>"
                data-text="<?php echo $row['text'] ?>"
                data-text_pos="<?php echo $row['text_pos'] ?>"
                data-removable="<?php echo $row['removable']; ?>"
                href="#">Edit</a></td>
                <?php if ($row['removable']) { ?>
                <td><a class="btn btn-danger" href="?delete=<?php echo $row['component'] ?>">Delete</a></td>
                <?php } else if (!$row['hidden']) { ?>
                <td><a class="btn btn-warning" href="?disable=<?php echo $row['component'] ?>">Hide</a></td>
                <?php } else {?>
                <td><a class="btn btn-primary" href="?enable=<?php echo $row['component'] ?>">Show</a></td>
                <?php } ?>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script>
      $('.edit').click(function(e) {
        e.preventDefault();
        $('#edit-modal').modal('show');
        var c = $(this).data('component'),
            p = $(this).data('pos'),
            wxh = $(this).data('wxh'),
            t = $(this).data('title'),
            l = $(this).data('link'),
            txt = $(this).data('text'),
            txtp = $(this).data('text_pos'),
            removable = $(this).data('removable');
        $('#id').val(c);
        $('#edit-pos').val(p);
        $('#edit-widthxheight').val(wxh);
        $('#edit-title').val(t);
        $('#edit-link').val(l);
        $('#edit-text').val(txt);
        $('#edit-text_pos').val(txtp);
        if (!removable) {
          $('#hide-file').css('display', 'none');
        }
        else {
          $('#hide-file').css('display', 'block'); 
        }
      });
    </script>
  </body>
</html>