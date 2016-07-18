# Pokemon Go Web Maps
Show nearby pokemon with a google maps.

# WARNING!!! ALERT!!!

 * USE AT YOUR OWN RISK ! DO NOT USE ON YOUR LIVE SERVER!!!
 * Unfinished!
 * Will not work if api is in heavy load.
 * Tutorial: fill in field with full address (even country) OR use get current location -> Press 'Get Pokemons' then wait 20 sec -> Press 'Load Pokemons'

## Install for MAC

 * sudo easy_install pip 
 * sudo pip install requests 
 * sudo pip install protobuf
 * sudo pip install geopy 
 * sudo pip install gpsoauth 
 * sudo pip install pycryptodomex
 * sudo pip install s2sphere

## Creating a config file
Create a config.php in the root with the following:

 * define('USERNAME', '');
 * define('PASSWORD', '');
 * define('GOOGLE_MAPS_API_KEY', '');
 * define('RANGE_KM', 10);

## Todo:

 * Show error messages
 * Show profile data
 * Show something we want

## Credits
Thanks a lot to [tejado](https://github.com/tejado/pokemongo-api-demo) for python code! (And ofcourse everyone else!)

