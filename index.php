<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
$stmt = $db->prepare('SELECT token FROM users WHERE slug = ?');
$stmt->execute(array($symlink));
$user_token = $stmt->fetchColumn();
if (isset($_COOKIE['token']) && $user_token !== null && $user_token === $_COOKIE['token']) {
  header("Location: https://environmentaldashboard.org/{$symlink}/prefs/docs");
}
if (isset($_POST['pass']) && isset($_POST['org'])) {
  $stmt = $db->prepare('SELECT password, token FROM users WHERE slug = ?');
  $stmt->execute(array($_POST['org']));
  $users = $stmt->fetch();
  $hash = $users['password'];
  if ($hash == null) { // Entered password becomes new password
    $token = bin2hex(random_bytes(127)); // Will be used to verify a user is logged in
    $stmt = $db->prepare('UPDATE users SET password = ?, token = ? WHERE slug = ?');
    $stmt->execute(array(password_hash($_POST['pass'], PASSWORD_DEFAULT), $token, $_POST['org']));
  } else {
    $token = $users['token'];
  }
  if ($hash == null || password_verify($_POST['pass'], $hash)) { // Log in
    if ($token !== $users['token']) { // if $token !== the token in the database (only true if $hash==null)
      $stmt = $db->prepare('UPDATE users SET token = ? WHERE slug = ?');
      $stmt->execute(array($token, $_POST['org']));
    }
    setcookie('token', $token, time()+60*60*24*30, "/");
    header("Location: https://environmentaldashboard.org/{$_POST['org']}/prefs/docs");
  }
}

$stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE password IS NULL AND slug = ?');
$stmt->execute(array($symlink));
if ($stmt->fetchColumn() === '0') {
  $msg1 = 'Please sign in';
  $msg2 = 'Sign in';
} else {
  $msg1 = 'Enter a new password';
  $msg2 = 'Create account';
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Sign in</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
body {
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #eee;
}

.form-signin {
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .form-signin-heading,
.form-signin .checkbox {
  margin-bottom: 10px;
}
.form-signin .checkbox {
  font-weight: normal;
}
.form-signin .form-control {
  position: relative;
  height: auto;
  -webkit-box-sizing: border-box;
          box-sizing: border-box;
  padding: 10px;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  /*border-bottom-right-radius: 0;*/
  /*border-bottom-left-radius: 0;*/
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  /*border-top-left-radius: 0;*/
  /*border-top-right-radius: 0;*/
}
select {
  margin-bottom: 10px;
}

    </style>
  </head>
  <body>
    <div class="container">
      <form class="form-signin" action="" method="POST">
        <h2 class="form-signin-heading"><?php echo $msg1; ?></h2>
        <label for="inputOrg" class="sr-only"></label>
        <select id="inputOrg" name="org" class="form-control">
          <?php foreach ($db->query('SELECT name, slug FROM users ORDER BY name ASC') as $row) {
            if ($symlink === $row['slug']) {
              echo "<option value='{$row['slug']}' selected>{$row['name']}</option>";
            } else {
              echo "<option value='{$row['slug']}'>{$row['name']}</option>";
            }
          }
          ?>
        </select>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" class="form-control" placeholder="Password" required="" name="pass">
        <button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $msg2; ?></button>
      </form>

    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>