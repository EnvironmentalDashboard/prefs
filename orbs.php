<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';
require '../includes/class.Meter.php';
if (isset($_POST['submit'])) {
  $stmt = $db->prepare('UPDATE orbs SET disabled = ? WHERE name = ?');
  if ($_POST['submit'] === 'Turn off') {
    $stmt->execute(array(1, $_POST['orb']));
  } else {
    $stmt->execute(array(0, $_POST['orb']));
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
    <title>Orbs Backend</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="height:20px;clear:both"></div>
      <div class="row">
        <div class="col-sm-12">
          <h1>Oberlin orbs</h1>
          <table class="table">
            <thead class="thead-default">
              <tr>
                <th>Name</th>
                <th>IP</th>
                <th>Electricity meter</th>
                <th>Water meter</th>
                <th>Electricity bin</th>
                <th>Water bin</th>
                <th>Electricity <code>relative_value</code> id</th>
                <th>Water <code>relative_value</code> id</th>
                <th>Disable</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $meter = new Meter($db);
              $page = (empty($_GET['page'])) ? 0 : intval($_GET['page']) - 1;
              $count = $db->query('SELECT COUNT(*) FROM orbs')->fetchColumn();
              $limit = 15;
              $offset = $limit * $page;
              $final_page = ceil($count / $limit);
              foreach ($db->query("SELECT name, elec_uuid, water_uuid, elec_rvid, water_rvid, disabled, INET_NTOA(ip) AS ip FROM orbs ORDER BY name ASC LIMIT {$offset}, {$limit}") as $row) {
                $elec_last_updated = 0;
                if ($row['elec_uuid'] != null) {
                  $elec_meter = $db->query("SELECT name FROM meters WHERE bos_uuid = '{$row['elec_uuid']}'");
                  if ($elec_meter->rowCount() != 1) {
                    $elec_meter = 'Meter sync error';
                    $elec_outdated = true;
                  } else {
                    $elec_meter = $elec_meter->fetchColumn();
                    $stmt = $db->prepare('SELECT last_updated FROM relative_values WHERE meter_uuid = ?');
                    $stmt->execute(array($row['elec_uuid']));
                    $elec_last_updated = $stmt->fetchColumn();
                    $elec_outdated = ($elec_last_updated === false || (time()-60) > $elec_last_updated) ? true : false;
                  }
                } else {
                  $elec_outdated = false;
                }
                $water_last_updated = 0;
                if ($row['water_uuid'] != null) {
                  $water_meter = $db->query("SELECT name FROM meters WHERE bos_uuid = '{$row['water_uuid']}'");
                  if ($water_meter->rowCount() != 1) {
                    $water_meter = 'Meter sync error';
                    $water_outdated = true;
                  } else {
                    $water_meter = $water_meter->fetchColumn();
                    $stmt = $db->prepare('SELECT last_updated FROM relative_values WHERE meter_uuid = ?');
                    $stmt->execute(array($row['water_uuid']));
                    $water_last_updated = $stmt->fetchColumn();
                    $water_outdated = ($water_last_updated === false || (time()-60) > $water_last_updated) ? true : false;
                  }
                } else {
                  $water_outdated = false;
                }
                ?>
              <tr<?php echo ($elec_last_updated === false || $elec_outdated || $water_last_updated === false || $water_outdated) ? ' class="table-danger"' : ''; ?>>
                <td><?php echo $row['name']; ?></td>
                <td id="ip<?php echo $row['ip'] ?>"><?php echo $row['ip']; ?></td>
                <td><?php echo $elec_meter; ?></td>
                <td><?php echo $water_meter; ?></td>
                <?php
                if ($row['elec_uuid'] == null) {
                  $elec = '-';
                }
                else {
                  $stmt = $db->prepare('SELECT relative_value FROM relative_values WHERE id = ?');
                  $stmt->execute(array($row['elec_rvid']));
                  $elec = round(($stmt->fetchColumn() / 100) * 4); // must be integer 0-4
                }
                echo "<td>{$elec}</td>";
                if ($row['water_uuid'] == null) {
                  $water = '-';
                }
                else {
                  $stmt = $db->prepare('SELECT relative_value FROM relative_values WHERE id = ?');
                  $stmt->execute(array($row['water_rvid']));
                  $water = round(($stmt->fetchColumn() / 100) * 4); // must be integer 0-4
                }
                echo "<td>{$water}</td>";
                ?>
                <td><?php if ($row['elec_uuid'] != null) {
                  echo $row['elec_rvid'];
                  if ($elec_last_updated === false) {
                    // var_dump($row['elec_uuid']);
                    echo ' (No relative value record)';
                  } elseif ($elec_outdated) {
                    echo ' (Outdated)';
                  }
                }
                ?></td>
                <td><?php if ($row['water_uuid'] != null) {
                  echo $row['water_rvid'];
                  if ($water_last_updated === false) {
                    // var_dump($row['water_uuid']);
                    echo ' (No relative value record)';
                  } elseif ($water_outdated) {
                    echo ' (Outdated)';
                  }
                }
                ?></td>
                <td>
                  <?php if ($row['disabled'] === '1') { ?>
                  <form action="" method="POST">
                    <input type="hidden" name="orb" value="<?php echo $row['name'] ?>">
                    <input type="submit" class="btn btn-primary" name="submit" value="Turn on">
                  </form>
                  <?php } else { ?>
                  <form action="" method="POST">
                    <input type="hidden" name="orb" value="<?php echo $row['name'] ?>">
                    <input type="submit" class="btn btn-danger" name="submit" value="Turn off">
                  </form>
                  <?php } ?>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <nav aria-label="Page navigation" class="text-center">
            <ul class="pagination pagination-lg">
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
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script>
      // function ping(ip) {
      //   $.ajax({
      //     url: 'http://' + ip,
      //     success: function(result) {
      //       $("#ip" + ip).text(ip + ' responded')
      //     },     
      //     error: function(result){
      //       $("#ip" + ip).text(ip + ' timeout/error');
      //     }
      //   });
      // }
//       function ping(host, port, pong) {

//   var started = new Date().getTime();

//   var http = new XMLHttpRequest();

//   http.open("GET", "http://" + host + ":" + port, /*async*/true);
//   http.onreadystatechange = function() {
//     if (http.readyState == 4) {
//       var ended = new Date().getTime();

//       var milliseconds = ended - started;

//       if (pong != null) {
//         pong(milliseconds);
//       }
//     }
//   };
//   try {
//     http.send(null);
//   } catch(exception) {
//     // this is expected
//   }

// }
      // $.each(<?php //echo json_encode($to_ping) ?>, function( i, l ) {
//         ping(l, 80);
//         // alert( "Index #" + i + ": " + l );
//       });
    </script>
  </body>
</html>