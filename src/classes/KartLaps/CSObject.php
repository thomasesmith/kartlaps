<?php 
namespace KartLaps;

abstract class CSObject {
	private $location;
	private $id;
	private $pageRequestObject;
}

interface iCSObject {
	public function getProperties(array $excludeFields);

	public function getPageRequestObject();
}
