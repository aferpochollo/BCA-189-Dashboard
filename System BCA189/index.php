<!DOCTYPE html>
<html>
<head>
  <title>Soil Sensor Data</title>
  <link rel="stylesheet" href="indexstyle.css">
  <script defer src="indexjava.js"></script>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="profile">
    <img src="soil.png" alt="Profile Picture" />
    <p class="username">John Doe</p>
  </div>
  <ul>
    <li><a href="index.php">Dashboard</a></li>
    <li><a href="analytics.php">Analytics</a></li>
    <li><a href="settings.php">Settings</a></li>
    <li><a href="help.php">Help</a></li>
  </ul>
</div>

<!-- Top Bar -->
<div class="topbar">
  <div class="topbar-left">
    <button class="sidebar-toggle-btn" onclick="toggleSidebar()">☰</button>
    <a href="index.php" class="page-title">Dashboard</a>
  </div>
  <div class="search-container">
    <input type="text" placeholder="Search..." />
  </div>
</div>

<!-- Main Content -->
<div class="container" id="mainContent">
  <h2 class="section-title">Soil Sensor Readings</h2>

  <div class="data-cards">
    <?php
      // Database connection
      $conn = new mysqli("localhost", "root", "", "spi");
      if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
      }

      // Fetch most recent data
      $sql = "SELECT * FROM sensordata ORDER BY timestamp DESC LIMIT 1";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $temp = $row['temperature'];
          $hum = $row['humidity'];
          $ec = $row['ec'];
          $ph = $row['ph'];
          $n = $row['nitrogen'];
          $p = $row['phosphorus'];
          $k = $row['potassium'];
          $moisture = $row['moisture'];
      } else {
          $temp = $hum = $ec = $ph = $n = $p = $k = $moisture = 'N/A';
      }
      $conn->close();

      // Helper function to determine soil type based on ESP32 logic
      function identifySoilType($ec, $ph, $n, $p, $k) {
        $zeroCount = 0;
        if ($ec == 0) $zeroCount++;
        if ($ph == 0) $zeroCount++;
        if ($n == 0) $zeroCount++;
        if ($p == 0) $zeroCount++;
        if ($k == 0) $zeroCount++;

        if ($zeroCount > 3) return "Insufficient data to determine soil";

        if ($ec < 500 && $ph >= 6.0 && $ph <= 6.8 && $n < 20 && $p < 15 && $k < 15)
          return "Sandy Soil";
        elseif ($ec >= 800 && $ec <= 1500 && $ph >= 6.2 && $ph <= 7.0 && $n >= 20 && $p >= 20 && $k >= 20)
          return "Loamy Soil";
        elseif ($ec > 1500 && $ph >= 6.5 && $ph <= 7.5 && $k >= 30)
          return "Clay Soil";
        elseif ($ec < 700 && $ph >= 6.0 && $ph <= 7.0 && $n > 25 && $p > 25)
          return "Silty Soil";
        elseif ($ph < 6.0 && $n > 30 && $ec < 1000)
          return "Peaty Soil";
        elseif ($ec > 2000 && $ph > 7.5)
          return "Saline Soil";
        else
          return "Unknown Soil";
      }

      $ecVal = is_numeric($ec) ? floatval($ec) : 0;
      $phVal = is_numeric($ph) ? floatval($ph) : 0;
      $nVal = is_numeric($n) ? floatval($n) : 0;
      $pVal = is_numeric($p) ? floatval($p) : 0;
      $kVal = is_numeric($k) ? floatval($k) : 0;

      $soilType = identifySoilType($ecVal, $phVal, $nVal, $pVal, $kVal);
      $descClass = ($soilType === "Unknown Soil" || $soilType === "Insufficient data to determine soil") ? 'unknown-desc' : 'known-desc';

      // Output soil type
      echo "<div class='card wide-card'>
              <h3>Soil Type: {$soilType}</h3>
              <p class='{$descClass}'>This is an automated classification based on sensor values.</p>
            </div>";

      // Output other sensor data
      echo "<div class='card medium-card data-card'>
              <div class='card-content'>
                <div>
                  <h4>Temperature</h4>
                  <p>{$temp} °C</p>
                </div>
                <img src='thermometer.png' alt='Temperature' class='card-img' />
              </div>
            </div>";

      echo "<div class='card small-card data-card'>
              <div class='card-content'>
                <div>
                  <h4>Humidity</h4>
                  <p>{$hum} %</p>
                </div>
                <img src='humidity.png' alt='Humidity' class='card-img' />
              </div>
            </div>";

      echo "<div class='card small-card data-card'>
              <div class='card-content'>
                <div>
                  <h4>Moisture</h4>
                  <p>{$moisture}</p>
                </div>
                <img src='pure-water.png' alt='Moisture' class='card-img' />
              </div>
            </div>";

      echo "<div class='card small-card data-card'>
              <div class='card-content'>
                <div>
                  <h4>pH</h4>
                  <p>{$ph}</p>
                </div>
                <img src='ph-balance.png' alt='pH' class='card-img' />
              </div>
            </div>";

      echo "<div class='card medium-card data-card'>
              <div class='card-content'>
                <div>
                  <h4>Electrical Conductivity</h4>
                  <p>{$ec}</p>
                </div>
                <img src='flash.png' alt='EC' class='card-img' />
              </div>
            </div>";

      echo "<div class='npk-group'>
              <div class='card npk-card data-card'>
                <div class='card-content'>
                  <div>
                    <h4>N (Nitrogen)</h4>
                    <p>{$n} ppm</p>
                  </div>
                  <img src='nitrogen.png' alt='Nitrogen' class='card-img' />
                </div>
              </div>
              <div class='card npk-card data-card'>
                <div class='card-content'>
                  <div>
                    <h4>P (Phosphorus)</h4>
                    <p>{$p} ppm</p>
                  </div>
                  <img src='crops-nourishment.png' alt='Phosphorus' class='card-img' />
                </div>
              </div>
              <div class='card npk-card data-card'>
                <div class='card-content'>
                  <div>
                    <h4>K (Potassium)</h4>
                    <p>{$k} ppm</p>
                  </div>
                  <img src='soil-supplement.png' alt='Potassium' class='card-img' />
                </div>
              </div>
            </div>";
    ?>
  </div>
</div>

</body>
</html>
