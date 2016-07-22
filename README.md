# Pokemon Go Web Maps
Show nearby pokemon with a google maps.
Search based on input field or current location from browser.
Own maps where you can click to move your location and get the new address.

<p align="center">
<img src="https://raw.githubusercontent.com/DjustinK/pokemon-go-web/master/screenshot.png">
</p>

# FEATURES
 * Load from current location
 * Enter a new address
 * NOTE: When loading a new destination this takes a while depending on how many steps you set at the python script.

# WARNING!!! ALERT!!!

 * USE AT YOUR OWN RISK ! DO NOT USE ON YOUR LIVE SERVER!!!
 * Unfinished!
 * Will not work if api is in heavy load.
 * If you don't see any new Pokemon appearing then the python script might have crashed, then you need to restart it.

## Install and Usage

 * https://github.com/AHAAAAAAA/PokemonGo-Map/wiki/Installation-and-requirements
 * https://github.com/AHAAAAAAA/PokemonGo-Map/wiki/Usage (Run it on port: -p 9999 and use the parameter: -ar 10)
 * Run the python script from the python map, example: python example.py -a "google" -u "gmail_account_here" -p "gmail_password_here" -l "900 North Point St, San Francisco, CA 94109" -st 5 -H 0.0.0.0 -P 9999 -ar 10 -i "Rattata,Pidgey,Weedle,Caterpie,Zubat"
 * Make sure that you run the python script from port 9999 and with a refresh of 10 sec, prefere to use google account, PTC seems to go down alot.
 * Using more then 5 steps in the python scripts makes the switching to new destination longer since it will first need to complete the old location stepping.
 * Get a webservice (php and, apache OR nginx).
 * Then load the website from your browser (http://127.0.0.1 or http://localhost or a configured vhost like http://pokemongomap.dev)

## Creating a config file
Create a config.php in the root with the following:

 * define('GOOGLE_MAPS_API_KEY', '');

## Todo:

 * Show error messages

## Credits
Thanks a lot to [AHAAAAAAA](https://github.com/AHAAAAAAA/PokemonGo-Map) for python code!

