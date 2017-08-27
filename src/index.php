<?php 
namespace KartLaps;

require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
	// Accept only GET requests
	http_response_code(400);
	exit; 
}

$location = filter_input(INPUT_GET, 'l', FILTER_SANITIZE_STRING);
$object = filter_input(INPUT_GET, 'o', FILTER_SANITIZE_STRING);
$query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);

if (strlen($location) == 0) {
    // Without a location name, we can't do anything

    http_response_code(400);
    exit; 
}
// @TODO Maybe move this validation above to the Location object?


$loc = new Location($location);

switch ($object) {
    case 'pointsleaderboard':
        $obj = new PointsLeaderboard($loc);
        break;
    case 'laptimeleaderboard':
        $obj = new LaptimeLeaderboard($loc, $query);  
        break;
    case 'racer':
        $obj = new Racer($loc, $query);
        $obj->fetchDetails();
        break;
    case 'heat':
        $obj = new Heat($loc, $query);
        $obj->fetchDetails(); 
        break;
    case 'search':
        $obj = new Search($loc, $query);
        break;
    default:
        // If there is a location but no object,
        // default to leaderboard 
        $obj = new PointsLeaderboard($loc);
}

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
http_response_code(Response::properHTTPResponseCode($obj));
echo Response::respondWithJson($obj);
