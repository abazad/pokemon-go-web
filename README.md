# Pokemon Go Web Maps
Show nearby pokemon with a google maps.
Search based on input field or current location from browser.
Own maps where you can click to move your location and get the new address.

<p align="center">
<img src="https://raw.githubusercontent.com/DjustinK/pokemon-go-web/master/screenshot.png">
</p>

# WARNING!!! ALERT!!!

 * USE AT YOUR OWN RISK ! DO NOT USE ON YOUR LIVE SERVER!!!
 * Unfinished!
 * Will not work if api is in heavy load.
 * Tutorial: fill in field with full address (even country) OR use get current location -> Press 'Get Pokemons'

## Install and Usage

 * https://github.com/AHAAAAAAA/PokemonGo-Map/wiki/Installation-and-requirements
 * https://github.com/AHAAAAAAA/PokemonGo-Map/wiki/Usage (Run it on port: -p 9999 and use the parameter: -ar 10)
 * Run the python script from the python map.
 * Get a webservice (php and, apache OR nginx).
 * Then load the website from your browser (http://127.0.0.1 or http://localhost or a configured vhost like http://pokemongomap.dev)

## Creating a config file
Create a config.php in the root with the following:

 * define('GOOGLE_MAPS_API_KEY', '');

## Todo:

 * Show error messages
 * Reponsive styling

## Credits
Thanks a lot to [AHAAAAAAA](https://github.com/AHAAAAAAA/PokemonGo-Map) for python code!

