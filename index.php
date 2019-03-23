<?php

ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "asr410";

	if(!isset($_GET["measure_id"])){
		$_GET["measure_id"] = "ACCESS2";
	}


	$conn = mysqli_connect($servername, $username, $password, "hackathon");

	if(!$conn){
		die("Connection failed: " . mysqli_connect_error());
	}
	
	$sql = "SELECT geolocation, measure_id, data_value FROM 500Cities;";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_assoc($result)) {
		
		if(!isset($_GET["measure_id"]) || $_GET["measure_id"] == $row["measure_id"]){
	
		    $longitude = substr($row["geolocation"], 1, strpos($row["geolocation"], ",")-1); 
		    $latitude = substr($row["geolocation"], strpos($row["geolocation"], ",") + 2, 20);
		    $ref_data = array(
					"lat" => (float)$longitude,
					"lng" => (float)$latitude,
				     );
		    $data_value = array(
					"percent" => (float)$row["data_value"],
		    );	    
                    $data[] = $ref_data;
		    $labels[] = $data_value;
		}
        }
	$db = NULL;
                $data_json = json_encode($data);
		$array_final = preg_replace('/"([a-zA-Z]+[a-zA-Z0-9_]*)":/','$1:',$data_json);

	
?>

<!DOCTYPE html>
<html>
  <head>
  <style>
	#map{

		width: 75%;
		display: inline-block;

	}
	
	#search_settings{
		
		width: 24%;
		height: 50%;
		display: inline-block;
		vertical-align: top;
		padding: 0px;

	}

	#twitter_pic{
		
		margin-left:auto;
		margin-right: auto;

	}

  </style>


    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>hackathonCLT 2019</title>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>
  <body>
    <div id="selection">
	
    </div>

    <div id="map"></div>
    <script>

      function initMap() {

	document.getElementById("loc").innerHTML = 35.2271.toString() + ", " + -80.8431.toString(); 
	
	<?php

	$js_array = json_encode($labels);
	echo "var label_array = ". $js_array . ";\n";
	?>

        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 11,
          center: {lat: 35.2271, lng: -80.8431}
        });

	map.addListener('center_changed', function(){
		var c = map.getCenter();
		document.getElementById("loc").innerHTML = c.lat().toFixed(6).toString() + ", " + c.lng().toFixed(6).toString();

		document.getElementById("twitter_querry").setAttribute('href', "https://twitter.com/search?f=tweets&vertical=default&q=geocode%3A" + c.lat().toFixed(6).toString() + "%2C" + c.lng().toFixed(6).toString() + "%2C5mi%2C%22health%22&src=typd");
	});




        // Add some markers to the map.
        // Note: The code uses the JavaScript Array.prototype.map() method to
        // create an array of markers based on a given "locations" array.
        // The map() method here has nothing to do with the Google Maps API.
        var markers = locations.map(function(location, i) {
          return new google.maps.Marker({
            position: location,
	    label: label_array[i].percent.toString() + "%"
          });
        });

        // Add a marker clusterer to manage the markers.
        var markerCluster = new MarkerClusterer(map, markers,
            {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});

      }
      var locations = <?php echo $array_final; ?>
    </script>
    <script src="/markerclusterer.js">
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCSNhFOli0xe3HJZINEJkPqZPEzQy1ntbI&callback=initMap">
    </script>
    
    <div id="search_settings">
	<h3>Search Settings</h3>
	<hr />
	Current Location:
	<span id="loc"></span>
	
	<form action="index.php" method="get">
		Measurement:		
		<select name="measure_id">
		<?php
		    
		    $sql = "SELECT DISTINCT measure_id FROM 500Cities;";
		    $result = mysqli_query($conn, $sql);

		    while ($row = mysqli_fetch_assoc($result)) {
		
			echo "<option value='{$row["measure_id"]}'";

			if(isset($_GET["measure_id"]) && $row["measure_id"] == $_GET["measure_id"]){
		
			    echo "selected='selected'";

			}

			echo ">{$row["measure_id"]}</option>";
		    }
		?>
		</select>
		<input type="submit">
	</form>
	<br />

    <h4>What's Happening Nearby?</h4>
    <a id="twitter_querry" href="https://twitter.com/search?f=tweets&vertical=default&q=geocode%3A35.351374973%2C-80.8635067345%2C5km%2C%22health%22&src=typd"><img id="twitter_pic" align="center" width="20%" src="/Twitter_Social_Icon_Square_Color.png" /></a>

    </div>
  </body>
</html>
