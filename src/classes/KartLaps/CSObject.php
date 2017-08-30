<?php 
namespace KartLaps;

abstract class CSObject {
	private $location;
	private $id;
    private $error;
}

interface iCSObject {
	public function getProperties(array $excludeFields);
}
