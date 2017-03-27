<ul class="nav nav-tabs">
  <?php $fn = basename($_SERVER['PHP_SELF'], '.php'); ?>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Citywide Dashboard</a>
    <div class="dropdown-menu">
      <a class="dropdown-item <?php echo ($fn === 'messages') ? 'active' : ''; ?>" href="messages.php">Messages</a>
      <a class="dropdown-item <?php echo ($fn === 'gauges') ? 'active' : ''; ?>" href="gauges.php">Gauges</a>
      <a class="dropdown-item <?php echo ($fn === 'timing') ? 'active' : ''; ?>" href="timing.php">Timing</a>
      <a class="dropdown-item <?php echo ($fn === 'landscape-components') ? 'active' : ''; ?>" href="landscape-components.php">Landscape components</a>
      <a class="dropdown-item <?php echo ($fn === 'buildingos-integration') ? 'active' : ''; ?>" href="buildingos-integration.php">Gauge integration</a>
      <a class="dropdown-item <?php echo ($fn === 'youtube') ? 'active' : ''; ?>" href="youtube.php">YouTube</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="../cwd/" target="_blank">Preview</a>
    </div>
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Calendar</a>
    <div class="dropdown-menu">
      <a class="dropdown-item <?php echo ($fn === 'review-events') ? 'active' : ''; ?>" href="review-events.php">Review events</a>
      <a class="dropdown-item <?php echo ($fn === 'add-location') ? 'active' : ''; ?>" href="add-location.php">Add locations</a>
      <a class="dropdown-item <?php echo ($fn === 'add-screen') ? 'active' : ''; ?>" href="add-screen.php">Add screen locations</a>
      <a class="dropdown-item <?php echo ($fn === 'screen-locations') ? 'active' : ''; ?>" href="screen-locations.php">Screen locations</a>
      <div class="dropdown-divider"></div>
      <a class="dropdown-item" href="../calendar/" target="_blank">View calendar</a>
    </div>
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Gauges</a>
    <div class="dropdown-menu">
      <a class="dropdown-item <?php echo ($fn === 'create-gauge') ? 'active' : ''; ?>" href="create-gauge.php">Create gauge</a>
      <a class="dropdown-item <?php echo ($fn === 'manage-gauges') ? 'active' : ''; ?>" href="manage-gauges.php">Manage gauges</a>
    </div>
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Time series</a>
    <div class="dropdown-menu">
      <a class="dropdown-item <?php echo ($fn === 'create-timeseries') ? 'active' : ''; ?>" href="create-timeseries.php">Create time series</a>
      <a class="dropdown-item <?php echo ($fn === 'manage-timeseries') ? 'active' : ''; ?>" href="manage-timeseries.php">Manage time series</a>
      <a class="dropdown-item <?php echo ($fn === 'timeseries-animations') ? 'active' : ''; ?>" href="timeseries-animations.php">Customize animations</a>
    </div>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo ($fn === 'orbs') ? 'active' : ''; ?>" href="orbs.php">Orbs</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo ($fn === 'meters') ? 'active' : ''; ?>" href="meters.php">Meters</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo ($fn === 'docs') ? 'active' : ''; ?>" href="docs.php">Help</a>
  </li>
</ul>