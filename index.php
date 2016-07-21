<?php

include_once 'config.php';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on" ? "https://" : "http://";
$domain = (isset($_SERVER) && is_array($_SERVER) && isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : '';

$address = isset($_POST['address']) ? $_POST['address'] : '';
$address = isset($_GET['address']) ? urldecode($_GET['address']) : $address;
$addressEncode = urlencode($address);

set_time_limit(0);

if (isset($_POST['get']) || isset($_GET['get'])) {

    $latLngResults = getData('https://maps.googleapis.com/maps/api/geocode/json?address=' . $addressEncode);
    $latLngResults = json_decode($latLngResults, true);
    if (isset($latLngResults['status']) && $latLngResults['status'] == 'OK' && (isset($latLngResults['results'][0]['geometry']['location']) || isset($latLngResults['results']['geometry']['location']))) {

        $lat = '';
        $lng = '';
        if(isset($latLngResults['results']['geometry']['location'])){
            $lat = $latLngResults['results']['geometry']['location']['lat'];
            $lng = $latLngResults['results']['geometry']['location']['lng'];
        }elseif($latLngResults['results'][0]['geometry']['location']){
            $lat = $latLngResults['results'][0]['geometry']['location']['lat'];
            $lng = $latLngResults['results'][0]['geometry']['location']['lng'];
        }

        if(!empty($lat) && !empty($lng)){
            $ok = getData($protocol . $domain . ':9999/next_loc?lat=' . $lat . '&lon=' . $lng);

            if ($ok == 'ok') {
                sleep(10);
            }
        }
    }

    header('Location: ?address=' . $addressEncode);
    exit;
}

function getData($url)
{
    return file_get_contents($url);
}

function debug($data)
{
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

            google.maps.event.addListener(marker, 'dragend', function(event)
            {
                getAddressLatLng(event.latLng.lat(), event.latLng.lng());
            });
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
                draggable: true,
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

        function setAddressMarker(address) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({'address': address}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    placeMarker(results[0].geometry.location, 'You are here!');
                }
            });
        }

        function bindInfoWindow(marker, map, infowindow, description) {
            marker.addListener('click', function () {
                infowindow.setContent(description);
                infowindow.open(map, this);
            });
        }

        function getAddressLatLng(lat, lng) {
            var xmlhttp = new XMLHttpRequest();

            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState == XMLHttpRequest.DONE) {
                    if (xmlhttp.status == 200) {
                        var result = xmlhttp.responseText;
                        var parsed = JSON.parse(result);

                        if (parsed.results[0].formatted_address) {
                            document.getElementById("address").value = parsed.results[0].formatted_address;
                        } else if (parsed.results.formatted_address) {
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

    </script>

</head>

<body>

<form action="<?php echo $protocol . $domain; ?>" method="POST" style="margin-bottom: 20px">
    <textarea id="address" name="address" rows="2"
              cols="50"><?php echo !empty($address) ? $address : ''; ?></textarea></br></br>
    <input type="submit" name="get" value="Get pokemon"> | <input type="button" value="Get Current Location"
                                                                  onclick="currentLocation(); return false;">
</form>

<div id="googleMap" style="width:500px;height:380px;margin-bottom: 20px;"></div>

<?php if (!empty($address)) { ?>
    <iframe src="<?php echo $protocol . $domain . ':9999'; ?>" style="border: none; width: 100%; height: 600px;">
        Your browser doesn't support iframes
    </iframe>
<?php } else { ?>

<?php } ?>

</br>


</body>

</html>
