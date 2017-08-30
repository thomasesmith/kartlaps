<?php 
namespace KartLaps;

class KartLapsException extends \Exception {

	/*	@TODO Consider whether or not all my error ouput texts should 
	 	be in here, with code numbers, instead of inside the 
		individual objects that throw the exceptions.
	*/

	public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
	}

	public function __toString() {
		return $this->message;
	}

}
