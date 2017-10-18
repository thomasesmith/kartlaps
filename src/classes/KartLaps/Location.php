<?php
namespace KartLaps;

class Location {
	
	private $id;

	function __construct($locationId)
	{
		if (trim($locationId) == "") {
			throw new KartLapsException("You must include a location name in your request.");
		} else {
			$this->id = trim($locationId);
		}
	}


	function __toString()
	{
		return $this->id;
	}


	public function getProperties(array $excludeFields = [])
	{
		$properties = array();

		$properties['id'] = $this->id;

        // If called with an exclusion list, remove those keys
        foreach ($excludeFields as $exclusion) {
            unset($properties[$exclusion]);
        }
        
		return $properties;
	}
}
