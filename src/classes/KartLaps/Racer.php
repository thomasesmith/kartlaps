<?php 
namespace KartLaps;

class Racer extends CSObject implements iCSObject {

    private $id = 0;
    private $location;
    private $racerName = "";
    private $realFirstName = "";
    private $realLastName = "";
    private $points = 0;
    private $city = "";
    private $url = "";
    private $heats = array();
    private $error = "";

    function __construct(Location $location, $racerId, $racerName = "", $points = 0, $city = "", $realFirstName = "", $realLastName = "")
    {
        $this->id = intval($racerId);
        $this->location = $location;
        $this->racerName = $racerName;
        $this->realFirstName = $realFirstName;
        $this->realLastName = $realLastName;
        $this->points = $points;
        $this->city = $city;
        $this->url = APP_PROTOCOL . APP_URL . "/" . $this->location->getProperties()['id'] . "/racer/" . $this->id;
    }


    public function getProperties($excludeFields = []) {
        $properties = array();
        
        $properties['url'] = $this->url;
        $properties['id'] = intval($this->id);
        $properties['location'] = $this->location->getProperties();

        if (strlen($this->racerName) > 0) {
            $properties['racerName'] = $this->racerName;
        } 

        if (strlen($this->realFirstName) > 0) {
            $properties['realFirstName'] = $this->realFirstName;
        } 

        if (strlen($this->realLastName) > 0) {
            $properties['realLastName'] = $this->realLastName;
        } 

        if ($this->points != 0) {
            $properties['points'] = $this->points;
        }    

        if (strlen($this->city) > 0) {
            $properties['city'] = $this->city;
        } 

        if (count($this->heats) > 0) {
            $properties['heats'] = $this->heats;
        } 

        if (strlen($this->error) > 0) {
            $properties['error'] = $this->error;
        } 

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


    private function fetchHTML()
    {
        $clubSpeedUrl = $this->location->getProperties()['id'] . ".clubspeedtiming.com/sp_center/RacerHistory.aspx?CustID=" . $this->id;

        try {
            $request = new PageRequest($clubSpeedUrl, "GET");
            $responseHTML = $request->getHTML();
            return $responseHTML;
        } catch (\Exception $e) {
            $this->error = "No racer was found by that ID and location. Please double check both and try again. If they are correct, this could be because the location turned off publicly available lap times.";
        }
    }


    private function parseHTML($html)
    { 
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        // Use a @ here to squash the PHP warnings caused by Club Speed's malformed html
        $xpath = new \DOMXPath($doc);
                    
        $elements = $xpath->query('//span[@id="lblRacerName"]');

        if ($elements->length > 0) {
            $this->racerName = $elements->item(0)->textContent;
        } else {
            // If this element wasn't found, the rest won't be either. Set an error and stop this method.
            $this->error = "No racer was found by that ID and location. Please double check both and try again. If they are correct, this could be because the location turned off publicly available lap times.";
            return false;
        }

        $elements = $xpath->query('//span[@id="lblSpeedLimit"]');
    
        if ($elements->length > 0) {
            $this->points = intval($elements->item(0)->textContent);
        }
        
        $elements = $xpath->query("//a[contains(@href,'HeatDetails.aspx?HeatNo=')]/../.."); 
        
        if ($elements->length > 0) {
            foreach ($elements as $element) {
                $tds = $element->childNodes;
                $heat = array(); 

                $heatNameCell = trim($tds->item(0)->nodeValue);
                $heatName_split = (explode("- Kart ", $heatNameCell));
                $heatName = (isset($heatName_split[0]) ? trim($heatName_split[0]) : $heatNameCell);
                $kartNumber = (isset($heatName_split[1]) ? intval($heatName_split[1]) : 0);

                $heatCsUrl = $tds->item(0)->firstChild->getAttribute('href');
                $heatCsUrl_split = explode("=", $heatCsUrl);
                $heatId = (isset($heatCsUrl_split[1]) ? intval($heatCsUrl_split[1]) : 0);

                $dateTime = trim($tds->item(1)->nodeValue);
                $bestTime = floatval(trim($tds->item(3)->nodeValue));
                $finalPosition = intval($tds->item(4)->nodeValue);

                $pointsCell = $tds->item(2)->nodeValue;
                $points = str_replace(array("(", ")"), "", $pointsCell);
                $points_split = explode(" ", $points);
                $pointsImpact = (isset($points_split[1]) ? intval($points_split[1]) : 0);
                $pointsAtStart = (isset($points_split[0]) ? intval($points_split[0]) : 0);

                $heatObject = new Heat($this->location, $heatId, $heatName, $dateTime);
                $heat['heat'] = $heatObject->getProperties();
                $heat['finalPosition'] = $finalPosition;
                $heat['pointsAtStart'] = $pointsAtStart;
                $heat['pointsImpact'] = $pointsImpact;
                $heat['kartNumber'] = $kartNumber;
                $heat['bestLapTime'] = $bestTime;

                $this->heats[] = $heat;
            }
        }
    }


    public function fetchDetails()
    {
        if (count($this->heats) == 0) {
            $this->parseHTML($this->fetchHTML());
        }
    }
}
