<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';
if (isset($_POST['submit'])) { // edit record
  if (!file_exists($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
    $stmt = $db->prepare("UPDATE cwd_landscape_components
      SET pos = ?, widthxheight = ?, title = ?, link = ?, `text` = ?, `order` = ?
      WHERE component = ? AND user_id = ? LIMIT 1");
    $stmt->execute(array(
      $_POST['pos'],
      $_POST['widthxheight'],
      $_POST['title'],
      $_POST['link'],
      $_POST['text'],
      $_POST['order'],
      $_POST['component'],
      $user_id
    ));
  }
  else { // have to upload new image as well
    $dir = __dir__;
    $uploaddir = dirname($dir) . '/cwd/img/';
    $filename = $_FILES['file']['name'];
    $uploadfile = $uploaddir . basename($filename);
    if (file_exists($uploadfile)) {
      $uploadfile = $uploaddir . uniqid();
    }
    move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
    $stmt = $db->prepare("UPDATE cwd_landscape_components
      SET pos = ?, widthxheight = ?, title = ?, link = ?, img = ?, `text` = ?, `order` = ?
      WHERE component = ? AND user_id = ? LIMIT 1");
    $stmt->execute(array(
      $_POST['pos'],
      $_POST['widthxheight'],
      $_POST['title'],
      $_POST['link'],
      'http://'.$_SERVER['HTTP_HOST'].'/cwd/img/' . $filename,
      $_POST['text'],
      $_POST['order'],
      $_POST['component'],
      $user_id
    ));
  }
}
if (isset($_POST['add-landscape-component'])) { // add record
  $dir = __dir__;
  $uploaddir = dirname($dir) . '/cwd/img/';
  $uploadfile = $uploaddir . basename($filename);
  if (file_exists($uploadfile)) {
    $uploadfile = $uploaddir . uniqid();
  }
  move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile);
  $stmt = $db->prepare("INSERT INTO cwd_landscape_components
    (user_id, component, pos, widthxheight, title, link, img, `text`, `order`)
    VALUES (:id, :c, :p, :wh, :t, :l, :i, :txt, :txtp, :o)");
  $stmt->execute(array(
    ':id' => $user_id,
    ':c' => uniqid(),
    ':p' => $_POST['pos'],
    ':wh' => str_replace(' ', '', $_POST['wxh']),
    ':t' => $_POST['title'],
    ':l' => $_POST['link'],
    ':i' => 'http://'.$_SERVER['HTTP_HOST'].'/cwd/img/' . $filename,
    ':txt' => $_POST['text'],
    ':o' => $_POST['order']
  ));

}
if (isset($_POST['delete'])) {
  $stmt = $db->prepare('DELETE FROM cwd_landscape_components WHERE component = ? AND user_id = ?');
  $stmt->execute(array($_POST['delete'], $user_id));
}

if (isset($_POST['disable'])) {
  $stmt = $db->prepare('UPDATE cwd_landscape_components SET hidden = 1 WHERE component = ? AND user_id = ?');
  $stmt->execute(array($_POST['disable'], $user_id));
}
if (isset($_POST['enable'])) {
  $stmt = $db->prepare('UPDATE cwd_landscape_components SET hidden = 0 WHERE component = ? AND user_id = ?');
  $stmt->execute(array($_POST['enable'], $user_id));
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
                <p><small class="text-muted" id="default-wxh"></small></p>
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
        <div class="col-sm-9">
          <h4>Landscape components</h4>
          <p>The icons on Citywide Dashboard dragged around when you're signed in. Double-click an icon to save its position.</p>
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
              <?php foreach ($db->query("SELECT * FROM cwd_landscape_components WHERE user_id = {$user_id} ORDER BY component ASC") as $row) { ?>
              <tr>
                <td><?php echo "<img id='image-{$row['component']}' class='img-fluid' src='{$row['img']}' />"; ?></td>
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
                data-removable="<?php echo $row['removable']; ?>"
                href="#">Edit</a></td>
                <?php if ($row['removable']) { ?>
                <td>
                  <form action="" method="POST">
                    <input type="hidden" name="delete" value="<?php echo $row['component'] ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                  </form>
                </td>
                <?php } else if (!$row['hidden']) { ?>
                <td>
                  <form action="" method="POST">
                    <input type="hidden" name="disable" value="<?php echo $row['component'] ?>">
                    <button type="submit" class="btn btn-warning">Hide</button>
                  </form>
                </td>
                <?php } else {?>
                <td>
                  <form action="" method="POST">
                    <input type="hidden" name="enable" value="<?php echo $row['component'] ?>">
                    <button type="submit" class="btn btn-primary">Show</button>
                  </form>
                </td>
                <?php } ?>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>

        <div class="col-sm-3">
          <h4>New component</h4>
          <form enctype="multipart/form-data" action="" method="POST">
            <div class="form-group">
              <label for="pos">x,y coordinates of component</label>
              <input type="text" class="form-control" name="pos" id="pos" value="0,0">
              <small class="text-muted">Coordinates seperated by a comma</small>
            </div>
            <div class="form-group">
              <label for="wxh">Width x Height</label>
              <input type="text" class="form-control" name="wxh" id="wxh" value="0x0">
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
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
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
            removable = $(this).data('removable');
        // See https://stackoverflow.com/a/1093364/2624391
        var img = $('#image-' + c);
        var theImage = new Image();
        theImage.src = img.attr('src');
        if (wxh == '' || wxh == '0x0') {
          wxh = theImage.width + 'x' + theImage.height;
        }
        $('#default-wxh').text('The default size for this image is ' + theImage.width + 'x' + theImage.height);
        $('#id').val(c);
        $('#edit-pos').val(p);
        $('#edit-widthxheight').val(wxh);
        $('#edit-title').val(t);
        $('#edit-link').val(l);
        $('#edit-text').val(txt);
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