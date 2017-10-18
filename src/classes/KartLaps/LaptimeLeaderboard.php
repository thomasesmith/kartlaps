<?php 
namespace KartLaps;

class LaptimeLeaderboard extends CSObject implements iCSObject {

    private $location; 
    private $leaders = array(); 
    private $url;
    private $days = 1;
    private $PageRequestObject;

	function __construct(Location $location, $days = 1)
    {
        $this->location = $location;
        if ($days == 1 || $days == 7 || $days == 30) {
            $this->days = $days;
        }
        $this->url = APP_PROTOCOL . APP_URL . '/' . $this->location . '/laptimeleaderboard/' . $this->days;
        $this->parseHTML($this->fetchHTML());
	}


    public function getProperties(array $excludeFields = [])
    {
        $properties = array();

        $properties['url'] = $this->url;
        $properties['location'] = $this->location->getProperties();
        $properties['days'] = $this->days;

        if (count($this->leaders) > 0) {
            $properties['leaders'] = $this->leaders; 
        }

        // If called with an exclusion list, remove those keys
        foreach ($excludeFields as $exclusion) {
            unset($properties[$exclusion]);
        }

        return $properties;
    }


    public function getPageRequestObject()
    {
        return $this->pageRequestObject;
    }
    

    private function fetchHTML()
    {
        $clubSpeedUrl = $this->location->getProperties()['id'] . ".clubspeedtiming.com/sp_center/Toptime.aspx?Days=" . $this->days;
        try {
            $this->pageRequestObject = new PageRequest($clubSpeedUrl, 'GET');
            $responseHTML = $this->pageRequestObject->getHTML();
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

        $trs = $xpath->query("//table[@class='TableStyle']/tr[@class!='TableHeaderStyle']");

        if ($trs->length > 0) {
            foreach ($trs as $tr) {
                $leader = array();

                $rank = intval($tr->childNodes->item(0)->textContent);
                $leader['racerName'] = trim($tr->childNodes->item(1)->textContent);
                $leader['lapTime'] = trim($tr->childNodes->item(2)->textContent);
                $leader['localDateTime'] = trim($tr->childNodes->item(3)->textContent);

                // We can't invoke a Racer object for each of these, because the Club Speed
                // Top Times page doesn't include a racer id with each row :(

                $this->leaders[intval($rank)] = $leader;
            }
        } 
    }   

}
