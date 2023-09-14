<?php
session_start();
date_default_timezone_set('Europe/Paris');

$correct_id = "";
$id_device = "";

$device_lat = 0;
$device_lon = 0;
$t = time();
$date_doday = date("Y-m-d", $t);

// LOGIN
if (isset($_POST['submit_form']) && $_POST['id_device']) {
  $nom_device = $_POST['nom_device'];
  $id_device = $_POST['id_device'];

  $dbconn = pg_connect("host=localhost dbname=capteurs user=**** password=****")
  or die('Could not connect: ' . pg_last_error());

  $query = "SELECT * FROM capteurs.capteurs WHERE nom = '$nom_device' LIMIT 1";
  $result = pg_query($query) or die('Query failed: ' . pg_last_error());
  $rows = pg_fetch_all($result);
  pg_close($dbconn);

  foreach ($rows as $value) {
    //echo $value['id'];
    $correct_id = $value['token'];
  }

  if ($id_device == $correct_id) {
    $_SESSION['nom_device'] = $nom_device;
    $_SESSION['id_device'] = $id_device;

    //echo "OK";
  } else {
    $error = "Incorrect Pssword";
    echo "Incorrect email or password";
  }
}

//update localisation
if (isset($_POST['update_localisation'])) {

  $newLat = $_POST['new_lat'];
  $newLong = $_POST['new_lon'];
  $id_device = $_SESSION['id_device'];

  $dbconn = pg_connect("host=localhost dbname=capteurs user=**** password=****")
  or die('Could not connect: ' . pg_last_error());
  $query2 = "UPDATE capteurs.capteurs SET lat = '$newLat', long = '$newLong' WHERE token = '$id_device'";
  $result2 = pg_query($query2) or die('Query failed: ' . pg_last_error());
  $rows2 = pg_fetch_all($result2);
  pg_close($dbconn);

  //echo $query2;

}

// LOGOUT
if (isset($_POST['logout'])) {
  $_SESSION['nom_device'] = "";
  $_SESSION['id_device'] = "";
}

?>

<html>

<head>
  <title>Mon Nebule Air</title>
  <meta charset="utf-8">
  <!-- Required meta tags -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- favicon -->
  <link rel="icon" type="image/x-icon" href="https://nebuleair.fr/img/LogoNebuleAirFavIcon.png">
  <!-- Bootstrap CSS ATTENTION WE USE V4.1 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"></script>
  <!-- Fontawesome -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">

  <style type="text/css">
    #map {
      width: 100%;
      height: 50%;
      padding: 0;
      margin: 0;
    }

    .address {
      cursor: pointer;
      border: 3px solid #1C6EA4;
      border-radius: 9px;
      padding: 5px;
      margin: 4px;
      margin-bottom: 8px;
    }

    .address:hover {
      background-color: #92A4A2
    }

    body {
      background-color: #EDF2F9 !important;
    }

    /* Adding !important forces the browser to overwrite the default style applied by Bootstrap */
  </style>

</head>

<body>

  <!-- CONNECTED -->

  <?php
  if (!empty($_SESSION['id_device']) && !empty($_SESSION['nom_device'])) {
  ?>

    <div class="container-fluid">

      <!-- LOGOS TOP -->
      <div class="row" style="margin-top:20px;">
        <div class="col-sm-8">
          <a href="http://nebuleair.fr">
            <img src="/img/LogoNebuleAir.png" height="50" alt="">
          </a>
        </div>

        <div class="col-sm-4" >
          <a href="https://www.atmosud.org/" style="display: none;">
            <img src="/img/Logo_Atmosud.png" class="float-left" height="50" alt="">
          </a>

          <a href="https://aircarto.fr/">
            <img src="/img/logoAirCarto_blue.png" class="float-right" height="50" alt="">
          </a>
        </div>
      </div>


      <?php
      //$statement = $pdo->query("SELECT * FROM myDevices where id = '" . $_SESSION['id_device'] . "'");
      //$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

      $dbconn = pg_connect("host=localhost dbname=capteurs user=**** password=****")
      or die('Could not connect: ' . pg_last_error());

      $token = $_SESSION['id_device'];
      
      $query = "SELECT * FROM capteurs.capteurs WHERE token = '$token' LIMIT 1";
      $result = pg_query($query) or die('Query failed: ' . pg_last_error());
      $rows = pg_fetch_all($result);
      pg_close($dbconn);

      ?>

      <?php
      foreach ($rows as $value) {
        $device_id = $value['id'];
        $device_lat = $value['lat'];
        $device_lon = $value['long'];
      }
      ?>

      <div class="row" style="margin-top: 20px;">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-header text-center">
              <?= $_SESSION['nom_device'] ?>
            </div>
            <div class="card-body">

              <h3 class="card-title">Mes données</h3>

                <p>Dernières mesures effectuées par le capteur NebuleAir.  <button type="button" onclick="refresh_data()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></button>
                </p>

                <div class="row">

                 
                  <div class="col-sm">
                    <!-- PM1 -->
                    <div class="embed-responsive embed-responsive-4by3">
                      <iframe class="embed-responsive-item" id="pm1_gauge" src="https://grafana.aircarto.fr/d-solo/Hcs4NEb4z/nebuleair?orgId=1&var-compound=pm1&var-device=<?= $_SESSION['nom_device'] ?>&from=now-1h&to=now&theme=light&panelId=8" height="200" frameborder="0"></iframe>
                    </div>
                  </div>

                  <div class="col-sm">
                    <!-- PM2.5 -->
                    <div class="embed-responsive embed-responsive-4by3">
                      <iframe class="embed-responsive-item" id="pm25_gauge" src="https://grafana.aircarto.fr/d-solo/Hcs4NEb4z/nebuleair?orgId=1&var-compound=pm25&var-device=<?= $_SESSION['nom_device'] ?>&from=now-1h&to=now&theme=light&panelId=6" height="200" frameborder="0"></iframe>
                    </div>
                  </div>

                  <div class="col-sm">
                    <div class="embed-responsive embed-responsive-4by3">
                      <!-- PM10 -->
                      <iframe class="embed-responsive-item" id="pm10_gauge" src="https://grafana.aircarto.fr/d-solo/Hcs4NEb4z/nebuleair?orgId=1&var-compound=pm10&var-device=<?= $_SESSION['nom_device'] ?>&from=now-1h&to=now&theme=light&panelId=4" height="200" frameborder="0"></iframe>
                    </div>
                  </div>

                </div>


                <br></br>

               
            </div>
          </div>
        </div>
      </div>



      <!-- HISTORIQUE -->
      <div class="row" style="margin-top: 20px;">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-body">
              <h3 class="card-title">Historique</h3>
              <button type="button" onclick="chooseTimeSpan(1)" class="btn btn-secondary btn-sm">1h</button>
              <button type="button" onclick="chooseTimeSpan(3)" class="btn btn-secondary btn-sm">3h</button>
              <button type="button" onclick="chooseTimeSpan(24)" class="btn btn-secondary btn-sm">24h</button>
              <button type="button" onclick="chooseTimeSpan(48)" class="btn btn-secondary btn-sm">48h</button>
              <button type="button" onclick="chooseTimeSpan(168)" class="btn btn-secondary btn-sm">1 semaine</button>
              <button type="button" onclick="chooseTimeSpan(720)" class="btn btn-secondary btn-sm">1 mois</button>


              <div id="chart_PM">
                <iframe src="https://grafana.aircarto.fr/d-solo/Hcs4NEb4z/nebuleair?orgId=1&var-compound=multi_mod&var-device=<?= $_SESSION['nom_device'] ?>&from=now-1h&to=now&panelId=10&theme=light" width="100%" height="600" frameborder="0"></iframe>
              </div>

              <div id="chart_COV">
                <iframe src="https://grafana.aircarto.fr/d-solo/Hcs4NEb4z/nebuleair?orgId=1&var-compound=multi_mod&var-device=<?= $_SESSION['nom_device'] ?>&from=now-1h&to=now&panelId=11&theme=light" width="100%" height="600" frameborder="0"></iframe>
              </div>

              <div id="chart_temp_hum">
                <iframe src="https://grafana.aircarto.fr/d-solo/Hcs4NEb4z/nebuleair?orgId=1&var-compound=multi_mod&var-device=<?= $_SESSION['nom_device'] ?>&from=now-1h&to=now&panelId=12&theme=light" width="100%" height="600" frameborder="0"></iframe>
              </div>
              
              
            </div>
          </div>
        </div>
      </div>


      <!-- Téléchanger mes données -->
      <div class="row" style="margin-top: 20px;">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-body">
            <h5 class="card-title">Télécharger mes données</h5>
            <h6 class="card-subtitle mb-2 text-body-secondary">Format CSV</h6>

            <a class="btn btn-primary" href="download/data_csv.php?device=<?= $_SESSION['nom_device'] ?>&from=-24h&to=now" role="button">Dernière 24h</a>
            <a class="btn btn-primary" href="download/data_csv.php?device=<?= $_SESSION['nom_device'] ?>&from=-168&to=now" role="button">Dernière semaine</a>
            <a class="btn btn-primary" href="download/data_csv.php?device=<?= $_SESSION['nom_device'] ?>&from=-720h&to=now" role="button">Dernier mois</a>
            <a class="btn btn-primary" href="download/data_csv.php?device=<?= $_SESSION['nom_device'] ?>&from=-8760h&to=now" role="button">Dernière année</a>
            <a class="btn btn-primary" href="download/data_csv.php?device=<?= $_SESSION['nom_device'] ?>&from=-87600h&to=now" role="button">Depuis le début</a>

            </div>
          </div>
        </div>
      </div>


      <!-- GEO_LOCALISATION -->
      <div class="row" style="margin-top: 20px;">
        <div class="col-sm-12">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Géolocaliser mon capteur</h5>

              <div id="search">
                <div class="mb-3 row">
                  <div class="col-8">
                    <input type="text" name="addr" value="" id="addr" class="form-control" placeholder="Entrez ici l'adresse ou se situe le NebuleAir ou déplacez le curseur sur la carte" />
                  </div>
                  <div class="col-4">
                    <button type="button" class="btn" onclick="addr_search();">Rechercher</button>
                  </div>
                </div>

                <div id="results">
                  <!-- Affichage des résultats de la recherche -->
                </div>

                <!-- Coordonnées -->
                <form action="" method="POST" enctype="multipart/form-data">
                  <div class="mb-3 row">
                    <div class="col-4">
                      <input type="text" name="new_lat" id="lat" size=12 value="" class="form-control">
                    </div>
                    <div class="col-4">
                      <input type="text" name="new_lon" id="lon" size=12 value="" class="form-control">
                    </div>
                    <div class="col-4">
                      <input type="submit" name="update_localisation" value="valider" class="btn btn-success" />
                    </div>

                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CARTE (Voir javascript) -->
      <div style="display: none; margin-top: 20px">
        <button type="button" onclick="map_multi()" class="btn btn-primary btn-sm">Multi Polluants (indice Atmo)</button>
        <button type="button" onclick="map_pm25()" class="btn btn-secondary btn-sm">PM2.5</button>
        <button type="button" onclick="map_pm10()" class="btn btn-secondary btn-sm">PM10</button>
        <button type="button" onclick="map_NO2()" class="btn btn-secondary btn-sm">NO2</button>
        <button type="button" onclick="map_O3()" class="btn btn-secondary btn-sm">O3</button>

      </div>

      <div id="map" style="margin-top: 20px;"></div>

      <!-- LOGOUT BUTTON -->
      <form action="" method="post">
        <button class="btn btn-lg px-5" type="submit" name="logout" style="margin-top:20px">Logout</button>
      </form>

    </div>

  <?php
  } else {
  ?>


    <!-- LOGIN MODULE -->
    <section class="vh-100 gradient-custom">
      <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card bg-dark text-white" style="border-radius: 1rem;">
              <div class="card-body p-5 text-center">

                <div class="mb-md-5 mt-md-4 pb-2">

                  <h2 class="fw-bold mb-2">Mon Nebule air</h2>
                  <p class="text-white-50 mb-5">Veuillez entrer le nom du NebuleAir ainsi que son token (indiqué au dos de l'appareil)</p>

                  <!-- Formulaire -->
                  <form method="post" action="" id="login_form">
                    <div class="form-outline form-white mb-4">
                      <input type="text" name="nom_device" id="typeEmailX" class="form-control form-control-lg" />
                      <label class="form-label" for="typeEmailX">Nom (device ID)</label>
                    </div>

                    <div class="form-outline form-white mb-4">
                      <input type="password" name="id_device" id="typePasswordX" class="form-control form-control-lg" />
                      <label class="form-label" for="typePasswordX">Token</label>
                    </div>

                    <!-- <p class="small mb-5 pb-lg-2"><a class="text-white-50" href="#!">Forgot password?</a></p> -->

                    <button class="btn btn-outline-light btn-lg px-5" type="submit" name="submit_form">Se connecter</button>
                  </form>

                </div>


              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- END LOGIN MODULE -->

  <?php
  }
  ?>




  <!-- JAVASCRIPT -->

  <script>
    // Change time lapse for charts
    function chooseTimeSpan(time) {
      document.getElementById("chart_PM").innerHTML = "<iframe src=\"https://grafana.aircarto.fr/d-solo/Hcs4NEb4z/nebuleair?orgId=1&var-compound=multi_mod&var-device=<?= $_SESSION['nom_device'] ?>&from=now-"+time+"h&to=now&panelId=10&theme=light\" width=\"100%\" height=\"600\" frameborder=\"0\"></iframe>";
      document.getElementById("chart_COV").innerHTML = "<iframe src=\"https://grafana.aircarto.fr/d-solo/Hcs4NEb4z/nebuleair?orgId=1&var-compound=multi_mod&var-device=<?= $_SESSION['nom_device'] ?>&from=now-"+time+"h&to=now&panelId=11&theme=light\" width=\"100%\" height=\"600\" frameborder=\"0\"></iframe>";
      document.getElementById("chart_temp_hum").innerHTML = "<iframe src=\"https://grafana.aircarto.fr/d-solo/Hcs4NEb4z/nebuleair2?orgId=1&var-compound=multi_mod&var-device=<?= $_SESSION['nom_device'] ?>&from=now-"+time+"h&to=now&panelId=12&theme=light\" width=\"100%\" height=\"600\" frameborder=\"0\"></iframe>";
      
      }

    // Refresh gauge
    function refresh_data() {
      //document.getElementById('co2_gauge').contentWindow.location.reload();
      document.getElementById('pm1_gauge').src = document.getElementById('pm1_gauge').src;
      document.getElementById('pm25_gauge').src = document.getElementById('pm25_gauge').src;
      document.getElementById('pm10_gauge').src = document.getElementById('pm10_gauge').src;
    }

    function map_pm25() {
      coumpound_map = "pm2_5";
    }

    function map_multi() {
      coumpound_map = "multi";
    }
  </script>


  <!-- Carte -->
  <script type="text/javascript">
    // Marseille
    var marseille_lat = 43.29765889;
    var marseille_lon = 5.38110077;

    //Device localisation
    var startlat = <?= $device_lat ?>;
    var startlon = <?= $device_lon ?>;

    var options = {
      center: [startlat, startlon],
      zoom: 14
    }

    document.getElementById('lat').value = startlat;
    document.getElementById('lon').value = startlon;

    var map = L.map('map', options);
    var nzoom = 12;

    // pour récupérer d'autres fonds de carte OSM
    // https://leaflet-extras.github.io/leaflet-providers/preview/
    // TODO: trouver un fond de carte transparent!

    //L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {attribution: 'OSM'}).addTo(map);

    //layer black and white
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
      attribution: 'OSM'
    }).addTo(map);

    var coumpound_map = "multi";
    //layer pollution
   

    var myMarker = L.marker([startlat, startlon], {
      title: "Coordinates",
      alt: "Coordinates",
      draggable: true
    }).addTo(map).on('dragend', function() {
      var lat = myMarker.getLatLng().lat.toFixed(8);
      var lon = myMarker.getLatLng().lng.toFixed(8);
      var czoom = map.getZoom();
      if (czoom < 18) {
        nzoom = czoom + 2;
      }
      if (nzoom > 18) {
        nzoom = 18;
      }
      if (czoom != 18) {
        map.setView([lat, lon], nzoom);
      } else {
        map.setView([lat, lon]);
      }
      document.getElementById('lat').value = lat;
      document.getElementById('lon').value = lon;
      myMarker.bindPopup("Lat " + lat + "<br />Lon " + lon).openPopup();
    });

    function chooseAddr(lat1, lng1) {
      myMarker.closePopup();
      map.setView([lat1, lng1], 18);
      myMarker.setLatLng([lat1, lng1]);
      lat = lat1.toFixed(8);
      lon = lng1.toFixed(8);
      document.getElementById('lat').value = lat;
      document.getElementById('lon').value = lon;
      myMarker.bindPopup("Lat " + lat + "<br />Lon " + lon).openPopup();
    }

    function myFunction(arr) {
      var out = "";
      var i;

      if (arr.length > 0) {
        for (i = 0; i < arr.length; i++) {
          out += "<div class='address' title='Show Location and Coordinates' onclick='chooseAddr(" + arr[i].lat + ", " + arr[i].lon + ");return false;'>" + arr[i].display_name + "</div>";
        }
        document.getElementById('results').innerHTML = out;
      } else {
        document.getElementById('results').innerHTML = "Sorry, no results...";
      }

    }

    function addr_search() {
      var inp = document.getElementById("addr");
      var xmlhttp = new XMLHttpRequest();
      var url = "https://nominatim.openstreetmap.org/search?format=json&limit=3&q=" + inp.value;
      xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          var myArr = JSON.parse(this.responseText);
          myFunction(myArr);
        }
      };
      xmlhttp.open("GET", url, true);
      xmlhttp.send();
    }
  </script>

  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.6.1.js" integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
</body>

</html>