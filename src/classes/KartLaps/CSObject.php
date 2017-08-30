<?php 
namespace KartLaps;

abstract class CSObject {
	private $location;
	private $id;
}

interface iCSObject {
	public function getProperties(array $excludeFields);
}
