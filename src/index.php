<?php 
namespace KartLaps;

require_once __DIR__ . '/_bootstrap.php';

// Respond with 400 code and die unless request method is GET
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
	http_response_code(400);
	exit; 
}

$location = filter_input(INPUT_GET, 'l', FILTER_SANITIZE_STRING);
$object = filter_input(INPUT_GET, 'o', FILTER_SANITIZE_STRING);
$query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);


$response = new Response($location, $object, $query);

// Set some response headers
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

// Respond with an http code and a response body
http_response_code($response->properHTTPResponseCode());
echo $response->responseJson();
