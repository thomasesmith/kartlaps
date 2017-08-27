<?php 

define("USER_AGENT", 'Kart Laps - Club Speed JSON-ifier (http://kartlaps.info)'); 
// This is the user-agent that will be included in each of the app's requests to CS servers.

define("APP_PROTOCOL", 'http://'); 
// Change to https:// if you're hosting this app with an SSL certificate

define("APP_URL", 'localhost:8080');
// Where the index.php is location. Omit any trailing slashes. 

define("MEMCACHE_ENABLED", 'false'); 
//define("MEMCACHE_HOST", '127.0.0.1');
//define("MEMCACHE_PORT", '11211');
//define("MEMCACHE_TTL", '480'); // How long a cache of a request lives, in seconds

// Change the MEMCACHE_ENABLED value to 'true' to enable the memcache support.
// Also be sure to un-comment the lines relating to memcache in the PageRequest.php class file.
