<?php
require '../includes/db.php';
if (isset($_POST['refreshdb'])) {
  require '../includes/class.BuildingOS.php';
  $bos = new BuildingOS($db);
  $buildings = $bos->getBuildings();
  $db->exec("TRUNCATE TABLE buildings");
  $db->exec("TRUNCATE TABLE meters");
  $db->exec("TRUNCATE TABLE meter_data");
  foreach ($buildings as $building) {
    $stmt = $db->prepare("INSERT INTO buildings (name) VALUES (?)");
    $stmt->execute(array($building['name']));
    $id = $db->lastInsertId();
    foreach ($building['meters'] as $meter) {
      $stmt = $db->prepare("INSERT INTO meters (building_id, source, name, url) VALUES (?, ?, ?, ?)");
      $stmt->execute(array($id, 'buildingos', $meter['name'], $meter['url']));
    }
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
    <title>Documentation</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
  </head>
  <body style="padding-top:5px">
    <div class="container">
      <div class="row">
        <div class="col-xs-12">
          <img src="images/env_logo.png" class="img-fluid" style="margin-bottom:15px">
          <?php include 'includes/navbar.php'; ?>
        </div>
      </div>
      <div style="clear: both;height: 20px"></div>
      <div class="container">
        <div class="row">
          <div class="col-xs-12">
            <h1>Documentation <small class="text-muted">for <?php echo ucwords(basename(dirname(__DIR__))); //echo ucwords(implode(' ', array_slice($domain, 0, count($domain) - 2))); ?></small></h1>
            <hr>

            <h3>Meters</h3>
            <p>Rather than querying the BuildingOS API directly, a scheduled task on the server periodically fetches new data from the API and stores it in a database for quicker access. Data is stored by different lengths and resolutions:</p>
            <ul>
              <li><a href="<?php echo "http://{$_SERVER[HTTP_HOST]}/scripts/jobs/minute.php"; ?>" target="_blank">1 minute resolution data is stored for 2 hours</a> <small>(where available; some meters only have 2 or 3 minute resolution)</small></li>
              <li><a  href="<?php echo "http://{$_SERVER[HTTP_HOST]}/scripts/jobs/quarterhour.php"; ?>" target="_blank">15 minute resolution data is stored for 2 weeks</a></li>
              <!-- Might want to expand 15 min res if many gauges go back more than 2 weeks -->
              <li><a href="<?php echo "http://{$_SERVER[HTTP_HOST]}/scripts/jobs/hour.php"; ?>" target="_blank">Hourly resolution data is stored for 2 months</a></li>
              <li><a href="<?php echo "http://{$_SERVER[HTTP_HOST]}/scripts/jobs/month.php"; ?>" target="_blank">Monthly resolution data is stored for 2 years</a></li>
              <small class="text-muted">The above links will open up a blank page in a new tab and manually run the described job</small>
            </ul>
            <p>When creating gauges keep the above limitations in mind as gauges that look back further than 2 weeks will only recieve one data point per hour.</p>
            <h5 id="manage-meters"><a href="manage-cron.php">Manage meters</a></h5>
            <p>To not waste server resources updating data for meters not being used each meter has a <code>num_using</code> number associated with it denoting the number of gauges and other content using its data. Meters whose <code>num_using</code> is 0 will be ignored by the scheduled tasks. The <code>num_using</code> a meter has is automatically updated when a gauge is created, edited, or deleted. However, any meters <code>num_using</code> number can be reset to 0 (meaning the meters data will not be collected by the scheduled task) if unchecked or 1 (meaning the data will be collected) if checked on the manage meters page. Note that resetting a meter to 0 will break anything using the data until it is checked (and the <a href="manage-cron.php">scheduled task has run</a>) again. However, gauges will continue to work regardless as they fallback to using the API when data can not be found in the database.</p>
            <p>Meters using external data can also be added to buildings by uploading a PHP script that updates the <code>meter</code> and <code>meter_data</code> tables in the database. Each script is pre-connected to the database and the PDO connection is stored in a variable named <code>$db</code> e.g. <code>$db->query('SELECT * FROM meters');</code>.</p>

            <hr style="margin-top:20px;margin-bottom:20px">

            <h3>Gauges</h3>
            <h5><a href="create-gauge.php">Create gauge</a></h5>
            <p>In the create gauge page gauges can be created for Citywide Dashboard and elsewhere. Gauges can be customized to ignore data <a href="create-gauge.php#start">before a certain date</a> as well as data <a href="create-gauge.php#data_interval">recorded on a day in a different group than the current day</a>. While only a few already defined groupings can be used when creating a guage, a custom grouping can be entered when <a href="manage-gauges.php">editing a gauge</a>. Groups are defined by square brackets and seperated by commas. Days in a group are written as the number that day is in the week (e.g. Sunday would be 1 because it's the first day of the week and Saturday would be 7) and seperated by commas. For example, to group weekdays and weekends in two seperate groups, one would write <code>[1,7], [2,3,4,5,6]</code>.</p>
            <h5>Relative value</h5>
            <p>A gauges relative value is used to position the circular indicator at the bottom of the gauge. The calculation of the relative value is where the current value falls in an ordered list of historical data from the current hour. For example, if the ordered list of historical data is <code>62.5, 63, 65, 66, 66.5, 70</code> and the current reading is 64, the relative value would be 2/7 (where the current value falls in the 0-based sorted list divided by the number of items in the list) or 28%</p>
            <h5>HTML vs SVG</h5>
            <p>There are both HTML and SVG implementations of the gauges available for different contexts. Whereas the HTML (webpage) versions need an <code>iframe</code> to be embedded, the SVG version can be embedded as an image. Additionally, the SVG gauges are much more performant compared to the HTML version. However, the <a href="http://github.hubspot.com/odometer/docs/welcome/">odometer.js</a> plugin does not work with SVGs, meaning the animation in the HTML version does not work in the SVG version.</p>

            <hr style="margin-top:20px;margin-bottom:20px">
            
            <h3>Citywide Dashboard</h3>
            <h5><a href="messages.php">Messages</a></h5>
            <p>The messages shown on Citywide Dashboard depend on the relative value of the gauge selected from the dropdown at the top of each page. Messages have five probability "bins" that each represent a quintile where the first bin is the first quintile. The bin that represents the quinile the relative value currently falls in is used as the "priority" a message has. A lower priority means the message will usually be shown after messages with a higher priority, but there is a degree of randomness.</p>
            <h5><a href="landscape-components.php">Landscape components</a></h5>
            <p>New landscape components can be created for Citywide Dashboard through the form on the left. X and Y coordinates are used to position components and have a maximium value of 1584,893 (the height and width of Citywide Dashboard).</p>

            <hr style="margin-top:20px;margin-bottom:20px">

            <h3>Other Notes</h3>
            <h5>Refreshing buildings</h5>
            <p>If buildings or meters are added to BuildingOS the database has to be updated to reflect the changes by clicking the button below.
            <form action="" method="POST" class="form-inline">
              <input type="submit" name="refreshdb" value="Update database" class="btn btn-secondary btn-sm">
            </form>
            </p>
            <h5>Date and time parsing</h5>
            <p>Forms asking for any kind of date or time e.g. the <a href="create-gauge.php#start">length of data field</a> use the PHP function <code>strtotime</code> so any English should be able to be parsed but refer to <a href="http://php.net/manual/en/function.strtotime.php">PHPs documentation</a> for more.</p>

            <div style="height:50px;clear:both"></div>

          </div>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    
    
  </body>
</html>