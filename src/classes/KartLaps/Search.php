<?php 
namespace KartLaps;

class Search extends CSObject implements iCSObject {

    private $location;
    private $id;
    private $url;
    private $searchString = "";
    private $searchByEmail = false;
    private $results = [];
    private $cs_viewstate = "";
    private $cs_eventvalidation = "";
    private $pageRequestObject;
    
    function __construct(Location $location, $searchString)
    {
        $this->location = $location;
        $this->searchString = urldecode(trim($searchString));

        // If we detect they submitted an email address as a search string,  
        // search for a racer by their email address. 
        if (strpos($this->searchString, '@') > 0) {
            $this->searchByEmail = true;
        }
        /* @TODO Consider what would happen if someone tries to search
            for a racer who used an @ in their racer name.
        */

        $html = $this->fetchHTML();
        
        $this->url = APP_PROTOCOL . APP_URL . "/" . $this->location->getProperties()['id'] . "/search/" . $this->searchString;

        $this->parseHTML($html);
    }


    public function getProperties(array $excludeFields = [])
    {
        $properties = array();

        $properties['url'] = $this->url;
        $properties['location'] = $this->location->getProperties();
        $properties['searchString'] = $this->searchString;
        $properties['results'] = [];

        if (count($this->results) > 0) {
            // 'results' will be array of Racer objects
            foreach ($this->results as $result) {
                $properties['results'][] = $result->getProperties(["location", "points", "heats"]); 
            }
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


    private function fetchToken()
    {
        $clubSpeedUrl = $this->location->getProperties()['id'] . ".clubspeedtiming.com/sp_center/Login.aspx?";

        try {
            $this->pageRequestObject = new PageRequest($clubSpeedUrl, 'GET', false);
            $html = $this->pageRequestObject->getHTML();

            $doc = new \DOMDocument();
            @$doc->loadHTML($html);
            // Use a @ here to squash the PHP warnings caused by Club Speed's malformed html
            $xpath = new \DOMXPath($doc);

            $elements = $xpath->query('//input[@id="__VIEWSTATE"]');
            $this->cs_viewstate = $elements->item(0)->getAttribute('value');
                        
            $elements = $xpath->query('//input[@id="__EVENTVALIDATION"]');
            $this->cs_eventvalidation = $elements->item(0)->getAttribute('value');
        } catch (KartLapsException $e) {
            throw new KartLapsException("For one reason or another, we couldn't retrieve the tokens required to conduct a search at location '" . $this->location . "'. Please double check the location name and try again. If it is correct, this could be because the location has turned off publicly available lap times.");
        }
    }


    private function fetchHTML()
    {
        $this->fetchToken();

        if ($this->cs_eventvalidation == "" || $this->cs_viewstate == "") {
            throw new KartLapsException("For one reason or another, we couldn't retrieve the tokens required to conduct a search at location '" . $this->location . "'. Please double check the location name and try again. If it is correct, this could be because the location has turned off publicly available lap times.");
            return false;
        }

        try {
            $clubSpeedUrl = $this->location->getProperties()['id'] . ".clubspeedtiming.com/sp_center/Login.aspx";

            if ($this->searchByEmail) {
                $fieldToSearch = 'tbxEmail';
            } else {
                $fieldToSearch = 'tbxRacerName';
            }
            
            $postData = [   
                            $fieldToSearch => $this->searchString,
                            'btnSubmit' => 'Submit',
                            '__VIEWSTATE' => $this->cs_viewstate,
                            '__EVENTVALIDATION' => $this->cs_eventvalidation
                        ];

            $this->pageRequestObject = new PageRequest($clubSpeedUrl, 'POST', false, $postData);

            if (strpos($this->pageRequestObject->getResponseHeaders()[0], "HTTP/1.1 302") === false) {
                // In the event of a HTTP 200 response
                return $this->pageRequestObject->getHTML();
            } else {
                /*  When the Club Speed search function finds only one result,
                    it doesn't return a list of results, but instead 302 redirects
                    directly to the one results racer page...
                */

                // No gaurantee where the "Location:" header is going to be so we have to find it
                foreach ($this->pageRequestObject->getResponseHeaders() as $headerLine) {
                    if (strtolower(substr($headerLine, 0, 9)) == "location:") {
                        $redirectUrl = $headerLine;
                        break; // Stop looping once found.
                    }
                }

                $redirectUrl_split = (isset($redirectUrl) ? explode("CustID=", $redirectUrl) : []);
                $racerId = (isset($redirectUrl_split[1]) ? $redirectUrl_split[1] : '0');

                if ($racerId > 0) {
                    // ...So in this case, return a little emulation of Club Speed's search
                    // results table row html using what we know, so the parse method isn't the wiser
                    $names = explode(" ", ucwords(strtolower($this->searchString)));

                    return '<table id="gv"><tr class="TableItemStyle"><td ><a href="RacerHistory.aspx?CustID=' . $racerId . '"></a></td><td></td><td></td><td></td></tr></table>';
                } else {
                    return '';
                }
            }
                        
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

        $elements = $xpath->query('//table[@id="gv"]/tr[contains(@class, "ItemStyle")]');

        if ($elements->length > 0) {
            foreach ($elements as $element) {
                $tds = $element->childNodes;

                $linkTd = $tds->item(0)->getElementsByTagName('a');
                $link = $linkTd->item(0);
                $racerCsUrl = $link->getAttribute('href');
                $racerCsUrl_split = explode("CustID=", $racerCsUrl);

                $racerId = (isset($racerCsUrl_split[1]) ? intval($racerCsUrl_split[1]) : 0);

                $realFirstName = $tds->item(0)->nodeValue;
                $realLastName = $tds->item(1)->nodeValue;
                $racerName = $tds->item(2)->nodeValue;
                $racerCity = ucwords(strtolower($tds->item(3)->nodeValue));

                $racer = new Racer($this->location, $racerId, $racerName, 0, $racerCity, $realFirstName, $realLastName);

                $this->results[] = $racer;
            }

            if (count($this->results) == 1) {
                // If there was only one result, we have to fetch their additional info on our own
                $this->results[0]->fetchDetails();
            }
        }
    }

}
