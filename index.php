<?php

include_once 'config.php';



$address = isset($_POST['address']) ? $_POST['address'] : '';
$address = isset($_GET['address']) ? urldecode($_GET['address']) : $address;
$addressEncode = urlencode($address);

set_time_limit(0);

if (isset($_POST['get']) || isset($_GET['get'])) {

    $folderId = time();

    if (!file_exists(dirname(__FILE__) . "/python/data/".$folderId)) {
        mkdir(dirname(__FILE__) . "/python/data/".$folderId, 0777, true);
    }

    shell_exec('cd python; python main.py -u "' . USERNAME . '" -p "' . PASSWORD . '" -l "' . $address . '" > data/'.$folderId.'/results_$(date +"%Y%m%d_%H%M%S").txt &');

    if(defined('RANGE_KM')){

        $latLngResults = getData('https://maps.googleapis.com/maps/api/geocode/json?address='.$addressEncode);
        $latLngResults = json_decode($latLngResults, true);



        if(isset($latLngResults['status']) && $latLngResults['status'] == 'OK' && isset($latLngResults['results'][0]['geometry']['location'])){
            $lat = $latLngResults['results'][0]['geometry']['location']['lat'];
            $lng = $latLngResults['results'][0]['geometry']['location']['lng'];



            $radius = (RANGE_KM * 2) / 6371.01; //6371.01 is the earth radius in KM
            $minLat = $lat - $radius;
            $maxLat = $lat + $radius;
            $deltaLon = asin(sin($radius) / cos($lat));
            $minLng = $lng - $deltaLon;
            $maxLng = $lng + $deltaLon;


            $address1 = getData("http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $maxLat . "," . $maxLng . "&sensor=true");
            $address1 = json_decode($address1, true);
            if(isset($address1['results'])){
                $address1['results'] = isset($address1['results'][0]) ? $address1['results'][0] : $address1['results'];
            }

            if(isset($address1['status']) && $address1['status'] == 'OK' && isset($address1['results']['formatted_address'])){
                $address1 = $address1['results']['formatted_address'];
            }else{
                $address1 = '';
            }

            $address2 = getData("http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $maxLat . "," . $minLng . "&sensor=true");
            $address2 = json_decode($address2, true);
            if(isset($address2['results'])){
                $address2['results'] = isset($address2['results'][0]) ? $address2['results'][0] : $address2['results'];
            }

            if(isset($address2['status']) && $address2['status'] == 'OK' && isset($address2['results']['formatted_address'])){
                $address2 = $address2['results']['formatted_address'];
            }else{
                $address2 = '';
            }

            $address3 = getData("http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $minLat . "," . $maxLng . "&sensor=true");
            $address3 = json_decode($address3, true);
            if(isset($address3['results'])){
                $address3['results'] = isset($address3['results'][0]) ? $address3['results'][0] : $address3['results'];
            }

            if(isset($address3['status']) && $address3['status'] == 'OK' && isset($address3['results']['formatted_address'])){
                $address3 = $address3['results']['formatted_address'];
            }else{
                $address3 = '';
            }

            $address4 = getData("http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $minLat . "," . $minLng . "&sensor=true");
            $address4 = json_decode($address4, true);
            if(isset($address4['results'])){
                $address4['results'] = isset($address4['results'][0]) ? $address4['results'][0] : $address4['results'];
            }

            if(isset($address4['status']) && $address4['status'] == 'OK' && isset($address4['results']['formatted_address'])){
                $address4 = $address4['results']['formatted_address'];
            }else{
                $address4 = '';
            }

            if($address1){
                sleep(5);
                shell_exec('cd python; python main.py -a "'.AUTH_SERVICE.'" -u "' . USERNAME . '" -p "' . PASSWORD . '" -l "' . $address1 . '" > data/'.$folderId.'/results_$(date +"%Y%m%d_%H%M%S").txt &');
            }

            if($address2){
                sleep(5);
                shell_exec('cd python; python main.py -a "'.AUTH_SERVICE.'" -u "' . USERNAME . '" -p "' . PASSWORD . '" -l "' . $address2 . '" > data/'.$folderId.'/results_$(date +"%Y%m%d_%H%M%S").txt &');
            }

            if($address3){
                sleep(5);
                shell_exec('cd python; python main.py -a "'.AUTH_SERVICE.'" -u "' . USERNAME . '" -p "' . PASSWORD . '" -l "' . $address3 . '" > data/'.$folderId.'/results_$(date +"%Y%m%d_%H%M%S").txt &');
            }

            if($address4){
                sleep(5);
                shell_exec('cd python; python main.py -a "'.AUTH_SERVICE.'" -u "' . USERNAME . '" -p "' . PASSWORD . '" -l "' . $address4 . '" > data/'.$folderId.'/results_$(date +"%Y%m%d_%H%M%S").txt &');
            }
        }
    }else{
        sleep(20);
    }

    header('Location: ?load=true&address=' . $addressEncode);
    exit;
}

if (isset($_POST['load']) || isset($_GET['load'])) {

    $dirs = array_filter(glob(dirname(__FILE__) . "/python/data/*"), 'is_dir');
    $dir = end($dirs);

    $data = array();
    $files = array();

    foreach (glob($dir."/*.*") as $filename) {
        $files[] = $filename;
    }



    foreach ($files as $filename) {
        $handle = fopen($filename, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {

                if (strpos($line, 'is visible at (') !== false) {
                    $found = $line;

                    $explodeSpace = explode(' ', $found);

                    $explode = explode('is visible at (', $found);
                    $explode2 = explode(') for ', $explode[1]);
                    $explode3 = explode(' seconds', $explode2[1]);

                    $explodeLatLng = explode(', ', $explode2[0]);

                    $latlng = $explode2[0];

                    $seconds = $explode3[0];

                    $number = $explodeSpace[0];
                    $name = $explodeSpace[1];

                    $data[] = array(
                        'number' => $number,
                        'name' => $name,
                        'lat' => $explodeLatLng[0],
                        'lng' => $explodeLatLng[1],
                        'seconds' => $seconds,
                    );

                }

            }

            fclose($handle);
        }
    }

    if (empty($data)) {
        header('Location: ?get=true&address=' . $addressEncode);
        exit;
    }
}

function getData($url) {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function debug($data){
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

?>

<!DOCTYPE html>
<html>
<head>
    <script src="http://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>"></script>
    <script>
        var oldMarker;
        var map;
        var infowindow;
        var pokemons = <?php echo !empty($data) ? json_encode($data) : "''";?>;
        var myLocation = <?php echo !empty($address) ? "'" . $address . "'" : "''";?>;

        function initialize() {

            if (myLocation) {
                setAddressMarker(myLocation);
            }

            var pos = new google.maps.LatLng(51.508742, -0.120850);
            var mapProp = {
                center: pos,
                zoom: 16,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById("googleMap"), mapProp);

            infowindow = new google.maps.InfoWindow({
                content: ''
            });

            if (!myLocation) {
                placeMarker(pos, 'You are here!');
                currentLocation();
            }

            google.maps.event.addListener(map, 'click', function (event) {
                placeMarker(event.latLng, 'You are here!');
                getAddressLatLng(event.latLng.lat(), event.latLng.lng());
            });

            if (pokemons) {
                var infowindow = new google.maps.InfoWindow({
                    content: ""
                });

                for (var i = 0; i < pokemons.length; i++) {
                    var pokemon = pokemons[i];

                    var cleanNumber = pokemon.number.replace('(', '');
                    cleanNumber = cleanNumber.replace(')', '');

                    var latLng = new google.maps.LatLng(pokemon.lat, pokemon.lng);
                    var marker = new google.maps.Marker({
                        position: latLng,
                        map: map,
                        title: pokemon.name,
                        icon: 'http://pokeapi.co//media//sprites//pokemon//' + cleanNumber + '.png'
                    });

                    bindInfoWindow(marker, map, infowindow, 'Nr: ' + pokemon.number + ' | Name: ' + pokemon.name + ' | Seconds: ' + pokemon.seconds);
                }
            }

        }

        google.maps.event.addDomListener(window, 'load', initialize);

        function currentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                    map.setCenter(pos);

                    placeMarker(pos, 'You are here!');
                    getAddressLatLng(position.coords.latitude, position.coords.longitude);
                });
            }
        }

        function placeMarker(location, title) {

            marker = new google.maps.Marker({
                position: location,
                map: map,
                animation: google.maps.Animation.DROP,
                title: title
            });

            if (oldMarker !== undefined) {
                oldMarker.setMap(null);
            }
            oldMarker = marker;

            google.maps.event.addListener(oldMarker, 'click', function () {
                infowindow.setContent(title);
                infowindow.open(map, oldMarker);
            });

            map.setCenter(location);
        }

        function bindInfoWindow(marker, map, infowindow, description) {
            marker.addListener('click', function () {
                infowindow.setContent(description);
                infowindow.open(map, this);
            });
        }

        function setAddressMarker(address) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({'address': address}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    placeMarker(results[0].geometry.location, 'You are here!');
                }
            });
        }

        function getAddressLatLng(lat, lng) {
            var xmlhttp = new XMLHttpRequest();

            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState == XMLHttpRequest.DONE) {
                    if (xmlhttp.status == 200) {
                        var result = xmlhttp.responseText;
                        var parsed = JSON.parse(result);

                        if(parsed.results[0].formatted_address){
                            document.getElementById("address").value = parsed.results[0].formatted_address;
                        }else if(parsed.results.formatted_address){
                            document.getElementById("address").value = parsed.results.formatted_address;
                        }
                    }
                    else if (xmlhttp.status == 400) {
                        alert('There was an error 400');
                    }
                    else {
                        alert('something else other than 200 was returned');
                    }
                }
            };

            xmlhttp.open("GET", "http://maps.googleapis.com/maps/api/geocode/json?latlng=" + lat + "," + lng + "&sensor=true", true);
            xmlhttp.send();
        }

        Number.prototype.toRad = function() {
            return this * Math.PI / 180;
        }

        Number.prototype.toDeg = function() {
            return this * 180 / Math.PI;
        }

        google.maps.LatLng.prototype.destinationPoint = function(brng, dist) {
            dist = dist / 6371;
            brng = brng.toRad();

            var lat1 = this.lat().toRad(), lon1 = this.lng().toRad();

            var lat2 = Math.asin(Math.sin(lat1) * Math.cos(dist) +
                Math.cos(lat1) * Math.sin(dist) * Math.cos(brng));

            var lon2 = lon1 + Math.atan2(Math.sin(brng) * Math.sin(dist) *
                    Math.cos(lat1),
                    Math.cos(dist) - Math.sin(lat1) *
                    Math.sin(lat2));

            if (isNaN(lat2) || isNaN(lon2)) return null;

            return new google.maps.LatLng(lat2.toDeg(), lon2.toDeg());
        }
    </script>

</head>

<body>

<form method="POST">
    <textarea id="address" name="address" rows="2"
              cols="50"><?php echo !empty($address) ? $address : ''; ?></textarea></br></br>
    <input type="submit" name="get" value="Get pokemon"> | <input type="button" value="Get Current Location"
                                                                   onclick="currentLocation(); return false;"> | <input
        type="submit" name="load" value="Load last Pokemon">
</form>
</br>
<div id="googleMap" style="width:500px;height:380px;"></div>

<?php if (!empty($data)) {
    echo '<br/>';
    foreach ($data as $pokemon) {
        echo '<p style="margin-top: 0px; margin-bottom: 0px;">Nr: ' . $pokemon['number'] . ' | Name: ' . $pokemon['name'] . ' | Seconds: ' . $pokemon['seconds'] . '</p>';
    }
} ?>

</body>

</html>
