<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';
if (isset($_POST['submit'])) {
  $image_meta = [];
  foreach ($_POST as $value) {
    $key = substr($value, 0, 3);
    if ($key === 'cat') {
      $index = substr($value, 3);
      $image_meta[$index] = $value;
    } elseif ($key === 'val') {
      $index = substr($value, 3);
      $image_meta[$index] = $value;
    }
  }
  $Y = date('Y');
  $m = date('m');
  if (!file_exists('/var/www/uploads/'.$Y)) {
    mkdir('/var/www/uploads/'.$Y, 0755);
  }
  if (!file_exists('/var/www/uploads/'.$Y.'/'.$m)) {
    mkdir('/var/www/uploads/'.$Y.'/'.$m, 0755);
  }
  $slug = slug($_POST['title']);
  while (file_exists('/var/www/uploads/'.$Y.'/'.$m.'/'.$slug.'.pdf')) {
    $slug = uniqid();
  }
  if (move_uploaded_file($_FILES['file']['tmp_name'], '/var/www/uploads/'.$Y.'/'.$m.'/'.$slug.'.pdf')) {
    $stmt = $db->prepare("INSERT INTO cv_lessons (title, pdf, published) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['title'], "https://environmentaldashboard.org/images/uploads/{$Y}/{$m}/{$slug}.pdf", time()]);
    $lesson_id = $db->lastInsertId();
    foreach ($image_meta as $key => $value) {
      $stmt = $db->prepare("INSERT INTO cv_lesson_meta (lesson_id, key, value) VALUES (?, ?, ?)");
      $stmt->execute([$lesson_id, $key, $value]);
    }
  }
}
function slug($str) {
  $ret = [];
  $a = ord('a');
  $z = ord('z');
  foreach (str_split(strtolower($str)) as $char) {
    $ord = ord($char);
    if ($ord >= $a || $ord <= $z) {
      $ret[] = $char;
    } elseif ($char === ' ') {
      $ret[] = '-';
    }
  }
  return implode('', $ret);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Create time series</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="clear:both;height:20px"></div>
      <div class="row">
        <div class="col-xs-12 col-sm-12">
          <h1>Add lesson</h1>
          <hr>
          <form action="" method="POST" enctype='multipart/form-data'>
            <div class="form-group">
              <label for="file">PDF File</label>
              <input type="file" class="form-control-file" id="file" name="file" value="">
            </div>
            <div class="form-group">
              <label for="title">Lesson name</label>
              <input type="text" class="form-control" id="title" name="title" value="">
            </div>
            <div class="form-group">
              <label for="cat0">Meta category</label>
              <select class="form-control" id="cat0" name="cat0">
                <option value="Dashboard feature employed">Dashboard feature employed</option>
                <option value="Select grade level(s)">Select grade level(s)</option>
                <option value="Select student level(s)">Select student level(s)</option>
                <option value="Select subject(s)">Select subject(s)</option>
                <option value="Select topic(s)">Select topic(s)</option>
                <option value="View units or lessons">View units or lessons</option>
                <option value="Standards addressed">Standards addressed</option>
                <option value="Author">Author</option>
                <option value="Keywords">Keywords</option>
              </select>
            </div>
            <div class="form-group">
              <label for="val0">Meta value</label>
              <input type="text" class="form-control" id="val0" name="val0" value="">
            </div>
            <div id="more-tags"></div>
            <p><a href="#" id="new-meta">Add another field</a></p>
            <input type="submit" class="btn btn-primary" value="Add">
          </form>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
      var count = 1;
      $('#new-meta').on('click', function(e) {
        e.preventDefault();
        var html = '<div class="form-group"> <label for="cat'+count+'">Meta category</label> <select class="form-control" id="cat'+count+'" name="cat'+count+'"> <option value="Dashboard feature employed">Dashboard feature employed</option> <option value="Select grade level(s)">Select grade level(s)</option> <option value="Select student level(s)">Select student level(s)</option> <option value="Select subject(s)">Select subject(s)</option> <option value="Select topic(s)">Select topic(s)</option> <option value="View units or lessons">View units or lessons</option> <option value="Standards addressed">Standards addressed</option> <option value="Author">Author</option> <option value="Keywords">Keywords</option> </select> </div><div class="form-group"> <label for="val'+count+'">Meta value</label> <input type="text" class="form-control" id="val'+count+'" name="val'+count+'" value=""> </div>';
        $('#more-tags').append(html);
        count++;
      })
    </script>
  </body>
</html>