<?php
require '../includes/db.php';
require 'includes/check-signed-in.php';
// if (isset($_POST['refreshdb'])) {
//   require '../includes/class.BuildingOS.php';
//   $bos = new BuildingOS($db);
//   $buildings = $bos->getBuildings();
//   $db->exec("TRUNCATE TABLE buildings");
//   $db->exec("TRUNCATE TABLE meters");
//   $db->exec("TRUNCATE TABLE meter_data");
//   foreach ($buildings as $building) {
//     $stmt = $db->prepare("INSERT INTO buildings (name) VALUES (?)");
//     $stmt->execute(array($building['name']));
//     $id = $db->lastInsertId();
//     foreach ($building['meters'] as $meter) {
//       $stmt = $db->prepare("INSERT INTO meters (building_id, source, name, url) VALUES (?, ?, ?, ?)");
//       $stmt->execute(array($id, 'buildingos', $meter['name'], $meter['url']));
//     }
//   }
// }
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
            <h1>Documentation <small class="text-muted">for <?php echo ucwords(explode('/', $_SERVER['REQUEST_URI'])[1]); ?></small></h1>
            <hr>

            <h3>Meters</h3>
            <p>Rather than querying the BuildingOS API directly, a scheduled task (cron job) on the server periodically fetches new meter data from the API and stores it in a database for quicker access. Data is stored by different lengths and resolutions:</p>
            <h5>Relative value</h5>
            <p>In addition to caching raw data, the cron jobs calculate the relative value for each record in the relative_values table so that a meters relative value may be quickly accessed. The calculation of the relative value is where the current value falls in an ordered list of historical data from the current hour. For example, if the ordered list of historical data is <code>62.5, 63, 65, 66, 66.5, 70</code> and the current reading is 64, the relative value would be 2/7 (where the current value falls in the 0-based sorted list divided by the number of items in the list) or 28%.</p>
            <p>The historical data used in the calculation can be customized by grouping days together. Only data recorded on a day in the same group as the current day is included in the calculation. Furthermore, each group of days can look back a number of data points or a string that will be parsed by <a href="http://php.net/manual/en/function.strtotime.php">PHPs strtotime</a> function. Thus, valid inputs could be a fixed date e.g. "August 12, 2017" or a relative amount of time e.g. "-2 weeks".</p>

            <hr style="margin-top:20px;margin-bottom:20px">

            <h3>Gauges</h3>
            <h5>HTML vs SVG</h5>
            <p>There are both HTML and SVG implementations of the gauges available for different contexts. Whereas the HTML (webpage) versions need an <code>iframe</code> to be embedded, the SVG version can be embedded as an image. Additionally, the SVG gauges are much more performant compared to the HTML version. However, the <a href="http://github.hubspot.com/odometer/docs/welcome/">odometer.js</a> plugin does not work with SVGs, meaning the animation in the HTML version does not work in the SVG version.</p>

            <hr style="margin-top:20px;margin-bottom:20px">
            
            <h3>Citywide Dashboard</h3>
            <h5><a href="messages.php">Messages</a></h5>
            <p>The messages shown on Citywide Dashboard depend on the relative value of the gauge selected from the dropdown at the top of each page. Messages have five probability "bins" that each represent a quintile where the first bin is the first quintile. The bin that represents the quinile the relative value currently falls in is used as the "priority" a message has. A lower priority means the message will usually be shown after messages with a higher priority, but there is a degree of randomness.</p>
            <h5><a href="landscape-components.php">Landscape components</a></h5>
            <p>New landscape components can be created for Citywide Dashboard through the form on the left. X and Y coordinates are used to position components and have a maximium value of 1584,893 (the height and width of Citywide Dashboard).</p>

            <hr style="margin-top:20px;margin-bottom:20px">

            <h3>Time Series</h3>
            <h5>List of variables</h5>
            <p>These variables can be added/modified at the end of a URL to change a time series.</p>
            <ul>
              <li>timeseriesconfig: If present, the value of this variable will be used to match a saved time series, which will be used to display a time series with the saved settings. If other variables are present in the URL, they will overwrite what is saved in the database.</li>
              <li>webpage: If set to "notitle", the time series is placed on a blank webpage. If set to "title", the building image is hidden and only the title is shown. If not present or set to another value, both the title and building image are shown. (Only available on index version of time series)</li>
              <li>meter_id: The id of the primary variable.</li>
              <li>meter_id2: If present and different from the meter_id, this will be the secondary variable.</li>
              <li>fill1: Controls whether the chart of the primary variable is filled (on/off).</li>
              <li>fill2: Controls whether the chart of the previous time scale is filled.</li>
              <li>fill3: Controls whether the chart of the secondary variable is filled.</li>
              <li>dasharr1: Controls whether the chart of the primary variable is dashed (on/off).</li>
              <li>dasharr2: Controls whether the chart of the previous time scale is dashed.</li>
              <li>dasharr3: Controls whether the chart of the secondary variable is dashed.</li>
              <li>color1: Controls whether the color of the primary variable is dashed.</li>
              <li>color2: Controls whether the color of the previous time scale.</li>
              <li>color3: Controls whether the color of the secondary variable.</li>
              <li>start: Where to start the Y-axis from.</li>
              <li>ticks: If present and set to "on", will draw baseload and peak ticks.</li>
            </ul>

            <hr style="margin-top:20px;margin-bottom:20px">

            <h3>Other Notes</h3>
            <h5>Refreshing buildings</h5>
            <p>If buildings or meters are added to BuildingOS the database has to be updated to reflect the changes by clicking the button below.
            <form action="" method="POST" class="form-inline">
              <input type="submit" name="refreshdb" value="Update database" class="btn btn-secondary btn-sm">
            </form>
            </p>

            <div style="height:50px;clear:both"></div>

          </div>
        </div>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    
    
  </body>
</html>