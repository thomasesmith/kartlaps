<?php 
namespace KartLaps;

class Response {
    
    private $errorMessage;
    private $location;
    private $query;
    private $responseObject;

    function __construct($locationId, $objectName = "", $query = "")
    {  
        $this->query = $query;

        try {
            $location = new Location($locationId);
            $this->location = $location;
        } catch (KartLapsException $e) {
            $this->errorMessage = $e->getMessage();
            return;
        }
        
        try {
            switch ($objectName) {
                case 'pointsleaderboard':
                    $this->responseObject = new PointsLeaderboard($this->location);
                    break;
                case 'laptimeleaderboard':
                    $this->responseObject = new LaptimeLeaderboard($this->location, $query);  
                    break;
                case 'racer':
                    $this->responseObject = new Racer($this->location, $query);
                    $this->responseObject->fetchDetails();
                    break;
                case 'heat':
                    $this->responseObject = new Heat($this->location, $query);
                    $this->responseObject->fetchDetails(); 
                    break;
                case 'search':
                    $this->responseObject = new Search($this->location, $query);
                    break;
                case '':
                    // If there is no object, default to the pointsleaderboard 
                    $this->responseObject = new PointsLeaderboard($this->location);
                    break;
                default:
                    throw new KartLapsException("Invalid object name submitted. Try 'pointsleaderboard'.");
            }
        } catch (KartLapsException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }


    public function properHTTPResponseCode()
    {
        if (strlen($this->errorMessage) > 0) {
            return 400;
        }
        
        return 200;
    }


    public function responseJson()
    {
        $responseArray = array();
        
        if (strlen($this->errorMessage) == 0) {
            if (!is_null($this->responseObject)) {
        		$objectName = strtolower(str_replace(__NAMESPACE__ . '\\', "", get_class($this->responseObject)));
                
                if (method_exists($this->responseObject, 'getProperties')) {
                    $responseArray[$objectName] = $this->responseObject->getProperties(['crawledFromUrl']);

                    $about = array();

                    if ($this->responseObject->getPageRequestObject() !== null) {
                        $about['recoveredFromCache'] = $this->responseObject->getPageRequestObject()->getRecoveredFromCacheStatus();
                        $about['dataFromUrl'] = 'http://' . $this->responseObject->getPageRequestObject()->getUrl();
                    }

                    $about['dataBy'] = 'Club Speed Inc. (http://www.clubspeed.com)';

                    $responseArray["about"] = $about; 
                }
            }
        } else {
            $responseArray['error'] = $this->errorMessage;
        }

        return json_encode($responseArray, JSON_PRETTY_PRINT);
    }
}
