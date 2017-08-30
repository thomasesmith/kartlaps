<?php 
namespace KartLaps;

class PointsLeaderboard extends CSObject implements iCSObject {

    private $location; 
    private $leaders = array(); 
    private $url;

	function __construct(Location $location)
    {
		$this->location = $location;
        $this->parseHTML($this->fetchHTML());
        $this->url = APP_PROTOCOL . APP_URL . '/' . $this->location . '/pointsleaderboard';
	}


    public function getProperties(array $excludeFields = [])
    {
        $properties = array();

        $properties['url'] = $this->url;
        $properties['location'] = $this->location->getProperties();

        if (count($this->leaders) > 0) {
            $properties['leaders'] = $this->leaders; 
        }

        // If called with an exclusion list, remove those keys
        foreach ($excludeFields as $exclusion) {
            unset($properties[$exclusion]);
        }

        return $properties;
    }


    private function fetchHTML()
    {
        $clubSpeedUrl = $this->location->getProperties()['id'] . ".clubspeedtiming.com/sp_center/SpeedLimit.aspx";

        try {
            $request = new PageRequest($clubSpeedUrl, "GET");
            $responseHTML = $request->getHTML();
            return $responseHTML;
        } catch (KartLapsException $e) {
            throw new KartLapsException("No location was found by the id '" . $this->location . "'. Please double check it and try again. If it is correct, this could be because the location has turned off publicly available lap times.");
        }
    }
    

	private function parseHTML($html)
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        // Use a @ here to squash the PHP warnings caused by Club Speed's malformed html
        $xpath = new \DOMXPath($doc);

        $elements = $xpath->query("//a[contains(@href,'RacerHistory.aspx')]/../..");

        if ($elements->length > 0) {
            foreach ($elements as $element) {

                $tds = $element->childNodes;

                $racerRank = intval(trim($tds->item(0)->textContent));
                $racerName = trim($tds->item(1)->textContent);
                $racerCsUrl = $tds->item(1)->childNodes->item(1)->getAttribute('href');
                $racerId = intval(explode("=", $racerCsUrl)[1]);
                $racerPoints = intval($tds->item(2)->textContent);
                $racerCity = trim($tds->item(3)->textContent);

                $racer = new Racer($this->location, $racerId, $racerName, $racerPoints, $racerCity);

                $excludeFields = ["location"];
                $this->leaders[intval($racerRank)] = $racer->getProperties($excludeFields);
            }
        } 
    }

}
