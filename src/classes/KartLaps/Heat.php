<?php 
namespace KartLaps;

class Heat extends CSObject implements iCSObject {

	private $location;
	private $id = 0;
	private $name = "";
	private $url = "";
    private $localDateTime = "";
    private $winBy = "";
    private $participants = [];
    private $podium = [];
    private $laps = [];
    private $finalPositions = [];

	function __construct(Location $location, $heat_id, $name = "", $dateTime = "")
	{
		$this->location = $location;
		$this->id = intval($heat_id);
		$this->url = APP_PROTOCOL . APP_URL . "/" . $this->location->getProperties()['id'] . "/heat/" . $this->id;
		$this->name = $name;
		$this->localDateTime = $dateTime;
	}


    public function getProperties(array $excludeFields = [])
    {
        $properties = array(); 

        $properties['url'] = $this->url;
        $properties['id'] = $this->id;
        $properties['location'] = $this->location->getProperties();
        $properties['name'] = $this->name;

        if (strlen($this->winBy) > 0) {
            $properties['winBy'] = $this->winBy;
        }

        $properties['localDateTime'] = $this->localDateTime;

        if (count($this->participants) > 0) {
            $properties['participants'] = $this->participants;
        }

        if (count($this->podium) > 0) {
            $properties['podium'] = $this->podium;
        }

        if (count($this->laps) > 0) {
            $properties['laps'] = $this->laps;
        }

        if (count($this->finalPositions) > 0) {
            $properties['finalPositions'] = $this->finalPositions;
        }

        // If called with an exclusion list, remove those keys
        foreach ($excludeFields as $exclusion) {
            unset($properties[$exclusion]);
        }

        return $properties;
    }


    public function fetchDetails()
    {
        $html = $this->fetchHTML();

        $this->parseHTML($html);
    }


    function __toString()
    {
        return $this->id;
    }


	private function fetchHTML()
	{
        $clubSpeedUrl = $this->location->getProperties()['id'] . ".clubspeedtiming.com/sp_center/HeatDetails.aspx?HeatNo=" . $this->id;
        
        try {
            $request = new PageRequest($clubSpeedUrl, "GET");
            $responseHTML = $request->getHTML();
            return $responseHTML;
        } catch (KartLapsException $e) {
            throw new KartLapsException("No heat was found by the id '" . $this->id . "' at location '" . $this->location . "'. Please double check both and try again. If they are correct, this could be because the location has turned off publicly available lap times.");
        }
    }


    private function parseHTML($html)
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML($html);
        // Use a @ here to squash the PHP warnings caused by Club Speed's malformed html
        $xpath = new \DOMXPath($doc);
                    
        $elements = $xpath->query('//span[@id="lblRaceType"]');
        if ($elements->length > 0) {
            $this->name = $elements->item(0)->textContent;
        } else {
            //  If this element wasn't found in the dom, the rest won't be either.
            //  Throw an exception and stop this method.
            throw new KartLapsException("No heat was found by the id '" . $this->id . "' at location '" . $this->location . "'. Please double check both and try again. If they are correct, this could be because the location has turned off publicly available lap times.");
            return false;
        }

        $elements = $xpath->query('//span[@id="lblDate"]');
        if ($elements->length > 0) {
            $this->localDateTime = $elements->item(0)->textContent;
        }

        $elements = $xpath->query('//span[@id="lblWinnerBy"]');
        if ($elements->length > 0) {
            $this->winBy = $elements->item(0)->textContent;
        }

        // Participants
        $elements = $xpath->query("//a[contains(@href,'RacerHistory.aspx?CustID=')]");

        if ($elements->length > 0) {
            foreach ($elements as $element) {
                $participantCsUrl_split = explode("=", $element->getAttribute('href'));
                $end = (isset($participantCsUrl_split[1]) ? $participantCsUrl_split[1] : '');
                $participant_split2 = explode("&", $end);

                $racerId = (isset($participant_split2[0]) ? intval($participant_split2[0]) : 0);
                $racerName = $element->textContent;

                $racer = new Racer($this->location, $racerId, $racerName);

                $excludeFields = ["location"];
                $this->participants[] = $racer->getProperties($excludeFields); 
                $this->laps[$racerId] = array();
            }
        }

        // Get the podium 
        $podium = array();
        $count = 0;

        $elements = $xpath->query("//table[@class='RaceResults']/tbody/tr[contains(@class,'Top3WinnersRow')]/td[@class='Racername']/span/a");

        foreach ($elements as $element) {
            $podiumItem = array();
            $count++;

            $podiumItem['finalPosition'] = $count;
 
            $racerCsUrl_split = explode("CustID=", $element->getAttribute('href'));
            $racerId = intval($racerCsUrl_split[1]);
            $racerName = $element->nodeValue;

            $racer = new Racer($this->location, $racerId, $racerName);

            $excludeFields = ["location"];
            $podiumItem['racer'] = $racer->getProperties($excludeFields);

            $this->podium[] = $podiumItem;
        }

        // Get the lap times of all the participants
        foreach ($this->participants as $participant) {
            
            $elements = $xpath->query('//table[@class="LapTimes"]/thead/tr[th//text()[contains(., "' . $participant['racerName'] . '")]]/../../tbody/tr[contains(@class, "LapTimesRow")]');

            if ($elements->length > 0) {
                foreach ($elements as $element) {

                    $lapString = '';

                    $nodes = $element->childNodes;
                        
                    foreach ($nodes as $node) {
                        $lapString .= $node->nodeValue . "|";
                    }
            
                    $lapString_split = explode("|", $lapString);
                    $lapNumber = (isset($lapString_split[0]) ? intval($lapString_split[0]) : 0);
                    $lapTimePos = str_replace(array('[',']'), "", $lapString_split[1]);
                    $lapTimePos_split = explode(" ", $lapTimePos);
                    $lapTime = (isset($lapTimePos_split[0]) ? floatval($lapTimePos_split[0]) : 0.000);
                    $lapPosition = (isset($lapTimePos_split[1]) ? intval($lapTimePos_split[1]) : 0);

                    $this->laps[$participant['id']][$lapNumber] = array('seconds' => $lapTime, 'position' => $lapPosition);

                    // @TODO Perhaps there should be a Lap object?               
                }
            }
            
        }
        
    }

}
