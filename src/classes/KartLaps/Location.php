<?php
namespace KartLaps;

class Location extends CSObject implements iCSObject {
	
	private $id;

	function __construct($location_id)
	{
		$this->id = $location_id;
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

	function __toString()
	{
		return $this->id;
	}

}
