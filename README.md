# Kart Laps
The [Kart Laps API](http://www.kartlaps.info) aims to provide developers with machine-readable access to go-karting raceway timing data, from raceway venues that use Club Speed timing service.

### Wait, What is Club Speed?
Club Speed is the timing system used by many arrive-and-drive sport karting raceways. After registering and signing in at your local karting raceway, your race timing data is stored and published on Club Speed's web app. But this data is displayed on the raceway's website inside iframe elements, surrounded by the raceways own branding images, and there is currently no customer-facing access to the data in a machine-readable way.

### What Does the API Do?
The first time I raced at a arrive-and-drive facility, I loved the fact that after the race was finished I could go online and view the results of my races – but it frustrated me that just looking at my times was pretty much all I could do. I wanted access to the data for my own nerdy reasons. The API requests the HTML document from Club Speed's site and converts human-readable information in to machine-readable JSON.  

### Access and API Keys
There is currently no limit of requests that you can make, and no key required to get back responses. There is some short-term memcache magic at work here to keep things quick, but that's all. Just assume that for each request you make to this API, that there is a request made to Club Speed's site (or in the case of searches, actually two). Design responsibly.

### So, How Do I Use This Thing?
Hit a url, and get back a response containing a JSON object.
> This is kind of a fresh project. It's behavior may be buggy. Feel free to [complain to me about stuff](http://twitter.com/thomasesmith). I will fix it. 

Start with your raceway's unique Club Speed venue name. This can be found in the URLs that your raceways website loads when loading the timing data iframes. Open up your browser inspector and watch when the clubspeed.com pages load. The url is usually something like this:

![Alt text](howto_findvenuename.jpg?raw=true "Like this:")

Or, sometimes like this:

![Alt text](howto_findvenuename2.png?raw=true "Or, sometimes like this:")

### Searching a Location for Racers
To search the locations database of racers, hit a url like http://kartlaps.info/mb2sylmar/racer-search/estes ('Estes' being the part of the name of the person I am searching for). I'll get back something like...

###### Path:
`http://kartlaps.info/mb2sylmar/racer-search/estes`

###### Response:
```json
{
    "location": "mb2sylmar",
    "search_string": "Estes",
    "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer-search\/Estes",
    "matches": [
      {
            "first_name": "Anthony",
            "last_name": "Estes",
            "racer_name": "anthony",
            "racer_id": "1091504",
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/1091504"
        },
        {
            "first_name": "Brandon",
            "last_name": "Estes",
            "racer_name": "Brandon Estes",
            "racer_id": "1093241",
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/1093241"
        },
        {
            "first_name": "Madison",
            "last_name": "Estes",
            "racer_name": "Madison estes",
            "racer_id": "1094813",
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/1094813"
        },
        {
            "first_name": "Casey",
            "last_name": "Estes",
            "racer_name": "casey estes",
            "racer_id": "1097973",
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/1097973"
        }
    ]
}
```

>I don't particular care for the quality of the search feature on raceway "login" pages. It only lists the first 50 matches it finds and offers no pagination for the rest of the results. But hey, I can only parse what I gets.

### Or, Check Out the Locations Leaderboard
To check out a location's top 100 points earners, I could hit a url like http://kartlaps.info/mb2sylmar/leaderboard.

###### Path: `http://kartlaps.info/mb2sylmar/leaderboard`

###### Response:
```json
{
    "location": "mb2sylmar",
    "uri": "http:\/\/kartlaps.info\/mb2sylmar\/leaderboard",
    "leaders": [
        {
            "racer_rank": 1,
            "racer_name": "McShredder Varner",
            "racer_points": 8330,
            "racer_city": "Shredderville",
            "racer_id": 9484,
            "racer_uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/9484"
        },
        {
            "racer_rank": 2,
            "racer_name": "BTOsports\/Demolition",
            "racer_points": 6630,
            "racer_city": "hollywood",
            "racer_id": 48630,
            "racer_uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/48630"
        },
        {
            "racer_rank": 3,
            "racer_name": "Monkey Sherman (L)",
            "racer_points": 5382,
            "racer_city": "van nuys",
            "racer_id": 95248,
            "racer_uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/95248"
        },

        ...

    ],
    "crawled_at_unix_time": 1414790536
}
```

### Get Individual Racer Information
Brandon Estes! I found him! Now, to get more information about all of his races at this location, I can hit his uri at http://kartlaps.info/mb2sylmar/racer/1093241 which spits back something like:

###### Path: `http://kartlaps.info/mb2sylmar/racer/1093241`

###### Response:
```json
{
    "location": "mb2sylmar",
    "racer_id": "1093241",
    "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/1093241",
    "racer_name": "Brandon Estes",
    "points": "1608",
    "heats": [
        {
            "heat_name": "FF 14 Lap Grid - Kart 16",
            "heat_id": 140545,
            "kart_number": 16,
            "date_time_local": "2\/20\/2013 7:50 PM",
            "final_position": 6,
            "points_effect": -20,
            "final_points": 1628,
            "best_time": 25.354,
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/heat\/140545"
        },
        {
            "heat_name": "IKC Qual - Kart 4",
            "heat_id": 140543,
            "kart_number": 4,
            "date_time_local": "2\/20\/2013 7:30 PM",
            "final_position": 6,
            "points_effect": 5,
            "final_points": 1623,
            "best_time": 25.202,
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/heat\/140543"
        },
        {
            "heat_name": "IKC Qual - Kart 22",
            "heat_id": 140532,
            "kart_number": 22,
            "date_time_local": "2\/20\/2013 7:10 PM",
            "final_position": 6,
            "points_effect": 5,
            "final_points": 1618,
            "best_time": 25.285,
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/heat\/140532"
        }
    ],
    "crawled_at_unix_time": 1414780120
}
```
### Get Race Information
Killer. I now have access to my friends entire race history, and now I can get the details of each of his races by selecting one of the heats and hitting its uri. Let's try http://kartlaps.info/mb2sylmar/heat/140532.

###### Path: `http://kartlaps.info/mb2sylmar/heat/140532`

###### Response:
```json
{
    "location": "mb2sylmar",
    "heat_id": 140532,
    "uri": "http:\/\/kartlaps.info\/mb2sylmar\/heat\/140532",
    "heat_name": "IKC Qual",
    "date_time_local": "2\/20\/2013 7:10 PM",
    "win_by": "Best Lap",
    "participants": [
        {
            "racer_name": "D.coch",
            "racer_id": 96084,
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/96084"
        },
        {
            "racer_name": "BTOsports\/Demo...",
            "racer_id": 48630,
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/48630"
        },
        {
            "racer_name": "358",
            "racer_id": 21429,
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/21429"
        },
        {
            "racer_name": "woody50",
            "racer_id": 124219,
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/124219"
        },
        {
            "racer_name": "Thomas Smith",
            "racer_id": 1091312,
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/1091312"
        },
        {
            "racer_name": "Brandon Estes",
            "racer_id": 1093241,
            "uri": "http:\/\/kartlaps.info\/mb2sylmar\/racer\/1093241"
        },

        ...

    ],
    "podium": [
        {
            "final_position": 1,
            "racer_name": "D.coch",
            "racer_id": 96084
        },
        {
            "final_position": 2,
            "racer_name": "BTOsports\/Demo...",
            "racer_id": 48630
        },
        {
            "final_position": 3,
            "racer_name": "358",
            "racer_id": 21429
        }
    ],
    "laps": {
        "96084": [
            {
                "lap_number": 1,
                "seconds": 24.678,
                "position": 1
            },
            {
                "lap_number": 2,
                "seconds": 24.34,
                "position": 1
            },
            {
                "lap_number": 3,
                "seconds": 24.603,
                "position": 1
            }

            ...

        ],
        "48630": [
            {
                "lap_number": 1,
                "seconds": 28.045,
                "position": 6
            },
            {
                "lap_number": 2,
                "seconds": 24.55,
                "position": 2
            },
            {
                "lap_number": 3,
                "seconds": 24.38,
                "position": 2
            }

            ...
        ]

        ...
    },
    "crawled_at_unix_time": 1414780220,
}
```
So on the heat-level, you can access details about the race, the participants (and their uris), and each laps time and positon, per racer id.

### Limitations
There are many. Because this API just goes out and grabs an HTML document then simply converts it, there's very little I can do to support requests with multiple parameters, or query the data in a unique way. Maybe that will change some day.

### "X, Y, Z Doesn't Work!"
If you find anything broken, or if you just have questions, tweet me at  [@thomasesmith](http://twitter.com/thomasesmith).

