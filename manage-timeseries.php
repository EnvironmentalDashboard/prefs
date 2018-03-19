<?php
error_reporting(-1);
ini_set('display_errors', 'On');
$symlink = explode('/', $_SERVER['REQUEST_URI'])[1];
require '../includes/db.php';
require 'includes/check-signed-in.php';
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
    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="clear:both;height:20px"></div>
      <div class="row">
        <div class="col-xs-12" style="width: 100%">
          <table class="table table-sm table-responsive">
            <thead>
              <tr>
                <th>Preview</th>
                <th>URL</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $page = (empty($_GET['page'])) ? 0 : intval($_GET['page']) - 1;
              $count = $db->query("SELECT COUNT(*) FROM meters WHERE source = 'buildingos' AND ((gauges_using > 0 OR for_orb > 0 OR timeseries_using > 0) OR bos_uuid IN (SELECT DISTINCT meter_uuid FROM relative_values WHERE permission = 'orb_server' AND meter_uuid != '')) AND org_id IN (SELECT org_id FROM users_orgs_map WHERE user_id = {$user_id})")->fetchColumn();
              $limit = 5;
              $offset = $limit * $page;
              $final_page = ceil($count / $limit);
              foreach ($db->query("SELECT id, building_id, name FROM meters WHERE source = 'buildingos' AND ((gauges_using > 0 OR for_orb > 0 OR timeseries_using > 0) OR bos_uuid IN (SELECT DISTINCT meter_uuid FROM relative_values WHERE permission = 'orb_server' AND meter_uuid != '')) AND org_id IN (SELECT org_id FROM users_orgs_map WHERE user_id = {$user_id}) ORDER BY building_id ASC, id ASC LIMIT {$offset}, {$limit}") as $row) {
                $building = $db->query('SELECT name FROM buildings WHERE id = '.intval($row['building_id']))->fetchColumn();
                $url = "https://environmentaldashboard.org/{$symlink}/chart/?meter0={$row['id']}";
                echo "<tr><td>";
                  echo "<iframe frameborder='0' style='max-width:500px' src='{$url}'></iframe></td>";
                  echo "<td><p>{$building} {$row['name']}</p><p>{$url}&title_img=on&title_txt=on</p><p><a href='{$url}&title_img=on&title_txt=on' target='_blank'>Open in new tab</a></p></td>";
                echo "</tr>";
              } ?>
            </tbody>
          </table>
          <nav aria-label="Page navigation" class="text-center">
            <ul class="pagination justify-content-center">
              <?php if ($page > 0) { ?>
              <li class="page-item">
                <a class="page-link" href="?sort=<?php echo (isset($_GET['sort'])) ? $_GET['sort'] : ''; ?>&page=<?php echo $page ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                  <span class="sr-only">Previous</span>
                </a>
              </li>
              <?php }
              $ok = true;
              for ($i = 1; $i <= $final_page; $i++) {
                if ($page > 20 && $i > 3 && $ok) {
                  $i = $page - 2;
                  $ok = false;
                  echo "<li class='page-item'><span class='page-link'>...</span></li>";
                }
                if ($i >= 20 && $final_page > ($i+3) && $page+3 < $i) {
                  $i = $final_page - 3;
                  echo "<li class='page-item'><span class='page-link'>...</span></li>";
                }
                // if ($i > 3 && $final_page > 20 && ($page+1)-$final_page < 3) {
                // }
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
  </body>
</html>