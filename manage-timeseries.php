<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require '../includes/db.php';
require 'includes/check-signed-in.php';
if (isset($_POST['delete-chart']) && isset($_POST['chart-id']) && $_POST['delete-chart'] === 'Delete') {
  $stmt = $db->prepare('DELETE FROM saved_charts WHERE id = ?');
  $stmt->execute([$_POST['chart-id']]);
  $stmt = $db->prepare('DELETE FROM saved_chart_meters WHERE chart_id = ?');
  $stmt->execute([$_POST['chart-id']]);
}

$stmt = $db->prepare('SELECT id FROM orgs WHERE api_id IN (SELECT id FROM api WHERE user_id = ?)');
$stmt->execute([$user_id]);
$org_ids = implode(',', array_column($stmt->fetchAll(), 'id'));
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Manage time series</title>
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
        <div class="col-xs-12" style="width: 100%">
          <table class="table table-sm table-responsive">
            <thead>
              <tr>
                <th>Preview</th>
                <th>Info</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $page = (empty($_GET['page'])) ? 0 : intval($_GET['page']) - 1;
              $count = $db->query("SELECT COUNT(*) FROM saved_charts")->fetchColumn();
              $limit = 5;
              $offset = $limit * $page;
              $final_page = ceil($count / $limit);
              foreach ($db->query("SELECT DISTINCT chart_id, GROUP_CONCAT(meter_id) AS meter_csv FROM saved_chart_meters WHERE meter_id IN (SELECT id FROM meters WHERE org_id IN ({$org_ids})) GROUP BY chart_id ORDER BY chart_id DESC LIMIT {$offset}, {$limit}") as $row) {
                $i = 0;
                $http_query = [];
                $stmt = $db->prepare("SELECT label FROM saved_charts WHERE id = ? AND label != ''");
                $stmt->execute([$row['chart_id']]);
                $info = ($stmt->rowCount() > 0) ? "<p>Label: ".($stmt->fetchColumn())."</p><p>Meters:<br>" : "<p>Meters:<br>";
                $meters = explode(',', $row['meter_csv']);
                foreach ($meters as $meter) {
                  $http_query["meter".($i++)] = $meter;
                  $stmt = $db->prepare('SELECT name, building_id FROM meters WHERE id = ?');
                  $stmt->execute([$meter]);
                  $meter_info = $stmt->fetch();
                  $stmt = $db->prepare('SELECT name FROM buildings WHERE id = ?');
                  $stmt->execute([$meter_info['building_id']]);
                  $building = $stmt->fetchColumn();
                  $info .= "{$building} {$meter_info['name']}<br>";
                }
                $url = "https://environmentaldashboard.org/{$symlink}/chart/?".http_build_query($http_query);
                echo "<tr><td>";
                echo "<iframe frameborder='0' style='max-width:500px' src='{$url}'></iframe></td>";
                echo "<td>{$info}</p><p>{$url}&title_img=on&title_txt=on</p><p><a href='{$url}&title_img=on&title_txt=on' target='_blank'>Open in new tab</a></p></td>";
                echo '<td><form action="" method="POST">
                        <input type="hidden" name="chart-id" value="'.$row['chart_id'].'">
                        <input type="submit" name="delete-chart" value="Delete" class="btn btn-danger">
                      </form></td></tr>';
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>