<?php

if ($_POST) {
    set_time_limit(0);

    if(isset($_POST['get'])){
        $outputNot = true;
        while ($outputNot) {
            $output = shell_exec('cd python; python main.py -u "'.USERNAME.'" -p "'.PASSWORD.'" -l "' . $_POST['address'] . '" > data/results_$(date +"%Y%m%d_%H%M%S").txt &');
            if (strpos($output, 'Traceback') === false) {
                $outputNot = false;
                echo "<pre>";
                print_r($output);
                echo "</pre>";
            }
            sleep(5);
        }
    }

    if(isset($_POST['load'])){

        $data = array();

        $files = array();

        foreach (glob(dirname(__FILE__)."/python/data/*.*") as $filename) {
            $files[] = $filename;
        }

        $files = array_slice($files, -1);

        foreach($files as $file){
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
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <script src="http://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY;?>"></script>
    <script>
        var oldMarker;
        var map;
        var infowindow;
        var pokemons = <?php echo !empty($data) ? json_encode($data) : "''";?>;
        var myLocation = <?php echo !empty($_POST['address']) ? "'".$_POST['address']."'" : "''";?>;

        function initialize() {

            if(myLocation){
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

            if(!myLocation){
                placeMarker(pos, 'You are here!');
                currentLocation();
            }

            google.maps.event.addListener(map, 'click', function(event) {
                placeMarker(event.latLng, 'You are here!');
                getAddressLatLng(event.latLng.lat(), event.latLng.lng());
            });

            if(pokemons){
                var infowindow =  new google.maps.InfoWindow({
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
                        icon : 'http://pokeapi.co//media//sprites//pokemon//'+cleanNumber+'.png'
                    });

                    bindInfoWindow(marker, map, infowindow, 'Nr: '+pokemon.number+ ' | Name: '+pokemon.name+ ' | Seconds: '+pokemon.seconds);
                }
            }

        }

        google.maps.event.addDomListener(window, 'load', initialize);

        function currentLocation(){
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

            if (oldMarker !== undefined){
                oldMarker.setMap(null);
            }
            oldMarker = marker;

            google.maps.event.addListener(oldMarker, 'click', function(){
                infowindow.setContent(title);
                infowindow.open(map, oldMarker);
            });

            map.setCenter(location);
        }

        function bindInfoWindow(marker, map, infowindow, description) {
            marker.addListener('click', function() {
                infowindow.setContent(description);
                infowindow.open(map, this);
            });
        }

        function setAddressMarker(address) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode( { 'address' : address }, function( results, status ) {
                if( status == google.maps.GeocoderStatus.OK ) {
                    placeMarker(results[0].geometry.location, 'You are here!');
                }
            } );
        }

        function getAddressLatLng(lat, lng){
            var xmlhttp = new XMLHttpRequest();

            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState == XMLHttpRequest.DONE) {
                    if (xmlhttp.status == 200) {
                        var result = xmlhttp.responseText;
                        var parsed = JSON.parse(result);
                        document.getElementById("address").value = parsed.results[0].formatted_address;
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
    </script>

</head>

<body>

<form method="POST">
    <textarea id="address" name="address" rows="2" cols="50"><?php echo !empty($_POST['address']) ? $_POST['address'] : '';?></textarea></br></br>
    <input type="submit" name="get" value="Get pokemons"> | <input type="button" value="Get Current Location" onclick="currentLocation(); return false;"> | <input type="submit" name="load" value="Load Pokemons">
</form>
</br>
<div id="googleMap" style="width:500px;height:380px;"></div>

<?php if(!empty($data)){
    echo '<br/>';
    foreach($data as $pokemon){
        echo '<p style="margin-top: 0px; margin-bottom: 0px;">Nr: '.$pokemon['number'].' | Name: '.$pokemon['name'].' | Seconds: '.$pokemon['seconds'].'</p>';
    }
}?>

</body>

</html>
