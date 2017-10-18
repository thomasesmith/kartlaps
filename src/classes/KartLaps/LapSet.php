<?php 
namespace KartLaps;

class LapSet {

	private $racerId; 
	private $racerLaps; 

	function __construct($racerId)
	{
		$this->racerId = $racerId;
	}


	public function getProperties(array $excludeFields = [])
	{
		$properties = array();

		if ($this->racerId) {
			$properties['racerId'] = $this->racerId; 
		}

		if ($this->racerLaps) {
			$properties['racerLaps'] = $this->racerLaps; 
		}

        // If called with an exclusion list, remove those keys
        foreach ($excludeFields as $exclusion) {
            unset($properties[$exclusion]);
        }

		return $properties;
	}


	public function addLap($lapNumber, $seconds, $position)
	{
		$lap = array("lapNumber" => $lapNumber, "seconds" => $seconds, "position" => $position);
		$this->racerLaps[] = $lap;
	}

}