<?php 
namespace KartLaps;

class PageRequest {

	private $url = "";
	private $method = "GET";
	private $responseHTML;
	private $tryCache = true; 
	private $postData = [];
	private $responseHeaders;

	function __construct($url = "", $method = "GET", $tryCache = true, $postData = [])
	{
		$this->url = $url;
		$this->method = $method;
		$this->postData = $postData;
		$this->tryCache = $tryCache;

		$this->responseHTML = $this->makeHttpRequest();
	}


	public function getHTML()
	{
        return $this->responseHTML;
	}


	public function getResponseHeaders()
	{
        return $this->responseHeaders;
	}


	private function makeHttpRequest()
	{
		if ($this->tryCache == true && MEMCACHE_ENABLED == 'true') {
			// Check the cache service to see if we have already requested this page recently
			/*
			$memcache = new \Memcache;
			$memcache->connect(MEMCACHE_HOST, MEMCACHE_PORT);
			$memcache_html = $memcache->get($this->url);

    		if ($memcache_html === false) { } else {
				$html = $memcache_html;
				return $html;
    		}
    		*/
		}

		// No cache was recovered, so request the url...

        if ($this->method == "GET") {
	        $context = [
	            'http' => [
	                'method' => $this->method,
	                'header' => "User-Agent: " . USER_AGENT . "\r\n",
   	                'follow_location' => false
	            ]
	        ];
		}

        if ($this->method == "POST")  {
	        $data = http_build_query($this->postData);
	        $context = [
        		'http' => [
                	'method' => $this->method,
                	'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
			                	"User-Agent: " . USER_AGENT . "\r\n",
					'follow_location' => false,
                	'content' => $data
		        ]
	        ];
        }

        $url = "http://" . $this->url;
        $context = stream_context_create($context);
        @$responseHTML = file_get_contents($url, false, $context);
        // Use a @ here to squash the PHP warnings caused by Club Speed's malformed html

        if ($responseHTML === false) {
        	throw new \Exception();
        } else {
	    	/*
			if ($this->tryCache == true && MEMCACHE_ENABLED == 'true') {
				// Save the response to the memcache with an expiration
				$memcache->set($this->url, $responseHTML, MEMCACHE_TTL);
			}
			*/

        	$this->responseHeaders = $http_response_header;
        	return $responseHTML;    	
        }
	}

}
