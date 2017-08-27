<?php 
namespace KartLaps;

class Response {

	static function properHTTPResponseCode($object)
	{
		if (isset($object->getProperties()['error'])) {
			return 404;
		} else {
			return 200;
		}
	}

	static function respondWithJson($object)
	{
		$objectName = strtolower(str_replace(__NAMESPACE__. '\\', "", get_class($object)));
		$assoc[$objectName] = $object->getProperties();
		$assoc["about"] = array("data_by" => "Club Speed Inc. (http://www.clubspeed.com)"); 
		return json_encode($assoc, JSON_PRETTY_PRINT);
	}

}
