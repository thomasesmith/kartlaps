# Kart Laps
[Kart Laps](http://www.kartlaps.info) is a simple API that aims to provide **machine-readable** access to go-kart timing data from raceway venues that use Club Speed timing services. Below you'll find instructions on how to use what's running in production at [kartlaps.info](http://www.kartlaps.info), or how to use the source code to set up your own instance. 

>Club Speed is a popular timing system used by many arrive-and-drive karting raceways. After registering at your local raceway, your race timing data is stored and published with Club Speed's web application. But this data is only available for human consumption, often displayed on the raceways website inside of iFrame elements. I could find no way to access that timing data in a machine-readable way, so I made one.

#### Using It
It's simple: you GET a particular URL. In response, you'll get back a JSON object containing the information you're requesting; searching for racers, data about individual racers, data about individual heats, the location's leaderboard, etc. 

#### How To Start
Start by figuring out the unique Club Speed location name of the raceway you want to request information about. If your raceway offers a "Lap Times" link on their website, chances are you can find it in the URLs that the raceway website loads when you click on that link. Open up your browser's inspector and filter the loaded page's network activity to show only "clubspeed" requests and look for any request made to an .aspx file. The location name is usually found as a subdomain in that request's URL. You'll find something like `http://mb2sylmar.clubspeedtiming.com/sp_center/Toptime.aspx?days=30` where "mb2sylmar" is the location name.

![Finding a location name](https://i.imgur.com/4Gil3im.png?raw=true "Finding a location name")

>A location name is required for all requests to the API. But even if you have one, some raceways insist they keep their lap time information entirely private. This seems to be an option in the Club Speed software that each location can control individually. For locations that are set up this way, Kart Laps won't work. If you suspect the location you're working with might be one of those, [contact me](http://www.twitter.com/thomasesmith) before you give up, and let me make sure.

#### Searching a Location for Racers
Once you have your location name, a good place to start is by searching that location for racers. Request a URL like http://kartlaps.info/v2/mb2sylmar/search/smith with "mb2sylmar" replaced with your location name, and "smith" replaced with your search string. Club Speed searches the location's racers by their real first name, last name, racer name, or the email address they used to sign up at the raceway...
>Some locations just don't support finding racers by their email address. This seems to be a per-location settting.
###### Request:
`http://kartlaps.info/v2/mb2sylmar/search/smith`

###### Response Example:
```json
{
    "search": {
        "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/search\/smith",
        "location": {
            "id": "mb2sylmar"
        },
        "searchString": "smith",
        "results": [
            {
                "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/racer\/619",
                "id": 619,
                "racerName": "Chipper",
                "realFirstName": "Chipper",
                "realLastName": "Smith",
                "city": "newhall"
            },
            {
                "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/racer\/712",
                "id": 712,
                "racerName": "Jules",
                "realFirstName": "Julie",
                "realLastName": "Smith",
                "city": "Chatsworth"
            },
            ...
```

>Club Speed's search page can return up to 50 unpaginated results, with no option of pagination.

#### Location Leaderboard
To check out a location's top 100 points leaders, go to a URL like http://kartlaps.info/v2/mb2sylmar/pointsleaderboard where "mb2sylmar" is replaced with the location name of the location for which you'd like to see the leaderboard...

###### Request: 
`http://kartlaps.info/v2/mb2sylmar/pointsleaderboard`

###### Response Example:
```json
{
    "pointsleaderboard": {
        "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/pointsleaderboard",
        "location": {
            "id": "mb2sylmar"
        },
        "leaders": {
            "1": {
                "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/racer\/9484",
                "id": 9484,
                "racerName": "McShredder Varner",
                "points": 9085,
                "city": "Shredderville"
            },
            "2": {
                "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/racer\/48630",
                "id": 48630,
                "racerName": "K. Clouston",
                "points": 7734,
                "city": "hollywood"
            },
            ...
```

#### Individual Racer's Basic Info and Race History
You can get the basic information and race history of an individual racer at a location by hitting a URL like http://kartlaps.info/v2/mb2sylmar/racer/75116 with "mb2sylmar" replaced with the location name you're querying, and the trailing set of numbers of the url replaced with the ID of the racer you want to know more about...

###### Request: 
`http://kartlaps.info/v2/mb2sylmar/racer/75116`

###### Response Example:
```json
{
    "racer": {
        "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/racer\/75116",
        "id": 75116,
        "location": {
            "id": "mb2sylmar"
        },
        "racerName": "ANIMAL",
        "points": 5495,
        "heats": [
            {
                "heat": {
                    "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/heat\/237669",
                    "id": 237669,
                    "location": {
                        "id": "mb2sylmar"
                    },
                    "name": "~MB2 14 Lap Race",
                    "localDateTime": "1\/5\/2017 6:40 PM"
                },
                "finalPosition": 1,
                "pointsAtStart": 5482,
                "pointsImpact": 13,
                "kartNumber": 10,
                "bestLapTime": 21.632
            },
            {
                "heat": {
                    "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/heat\/233248",
                    "id": 233248,
                    "location": {
                        "id": "mb2sylmar"
                    },
                    "name": "~MB2 14 Lap Race",
                    "localDateTime": "10\/31\/2016 8:00 PM"
                },
                "finalPosition": 1,
                "pointsAtStart": 5420,
                "pointsImpact": 62,
                "kartNumber": 5,
                "bestLapTime": 20.984
            },
            ...
```
### Get Heat Data
You can get a detailed account of each race at a location that includes the participants, the final positions of the participants, and each of their lap's times by hitting a URL like  http://kartlaps.info/v2/mb2sylmar/heat/237669 with "mb2sylmar" replaced with the location name you're querying, and the trailing set of numbers replaced by the heat id you want to know more about...

###### Request: 
`http://kartlaps.info/v2/mb2sylmar/heat/237669`

###### Response Example:
```json
{
    "heat": {
        "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/heat\/237669",
        "id": "237669",
        "location": {
            "id": "mb2sylmar"
        },
        "name": "~MB2 14 Lap Race",
        "winBy": "Best Lap",
        "localDateTime": "1\/5\/2017 6:40 PM",
        "participants": [
            {
                "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/racer\/75116",
                "id": 75116,
                "racerName": "ANIMAL"
            },
            {
                "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/racer\/3900",
                "id": 3900,
                "racerName": "Isra"
            },
            ...
        ],
        "podium": [
            {
                "finalPosition": 1,
                "racer": {
                    "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/racer\/75116",
                    "id": 75116,
                    "racerName": "ANIMAL"
                }
            },
            {
                "finalPosition": 2,
                "racer": {
                    "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/racer\/3900",
                    "id": 3900,
                    "racerName": "Isra"
                }
            },
            {
                "finalPosition": 3,
                "racer": {
                    "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/racer\/1279496",
                    "id": 1279496,
                    "racerName": "evilgt500"
                }
            }
        ],
        "laps": {
            "75116": {
                "1": {
                    "seconds": 27.753,
                    "position": 2
                },
                "2": {
                    "seconds": 25.418,
                    "position": 5
                },
                "3": {
                    "seconds": 24.64,
                    "position": 4
                },
                ...
            },
            "3900": {
                "1": {
                    "seconds": 27.753,
                    "position": 2
                },
                "2": {
                    "seconds": 25.418,
                    "position": 5
                },
                "3": {
                    "seconds": 24.64,
                    "position": 4
                },
                ...
            },
            ...
```
### Top Times
In addition to a points leaderboard, there is also a laptime leaderboard, that displays best times over the last 1, 7, or 30 day(s). Try a URL like http://kartlaps.info/v2/mb2sylmar/laptimeleaderboard/1 with "mb2sylmar" replace with the location you're querying, and the final number replaced with the amount of days history to show (acceptable values are 1, 7, and 30).

###### Request: 
`http://kartlaps.info/v2/mb2sylmar/laptimeleaderboard/1`

###### Response Example:
```json
{
    "laptimeleaderboard": {
        "url": "http:\/\/kartlaps.info\/v2\/mb2sylmar\/laptimeleaderboard\/1",
        "location": {
            "id": "mb2sylmar"
        },
        "days": "1",
        "leaders": {
            "1": {
                "racerName": "Andrew",
                "lapTime": "00:19:065",
                "localDateTime": "8\/26\/2017 2:06 PM"
            },
            "2": {
                "racerName": "YRUSLOW",
                "lapTime": "00:19:468",
                "localDateTime": "8\/26\/2017 11:12 AM"
            },
            "3": {
                "racerName": "Dan Brown",
                "lapTime": "00:19:621",
                "localDateTime": "8\/26\/2017 2:06 PM"
            },
            ...
```
> You may notice this output is a list of racer objects, but that those objects don't contain a racer id. This is because Club Speed's "Top Times" pages strangely don't include the racer's id in this list. Another quirk of their system that I haven't devised a work-around for.

#### API Keys
There is currently no limit of requests that you can make, and no key required to include in your requests.

> But! Please assume that for each request you make to this API, that there is in-turn a request made to Club Speed servers. There is some caching done to improve response speeds and to not bombard Club Speed servers by making too many requests of the same unchanged data, but still use this API responsibly.


***

## Roll Your Own
The above is all good if you just want to consume the service and not worry about anything else. But for you tinkerers, the source code that responds to all the `/v2/` requests [in production](http://www.kartlaps.info) is included in the '/src' folder of this git.

### Setting It Up
Pretty straight-forward PHP app. But here are some things to note...

##### Get Those Pretty URLs
If you do decide to run your own instance, you may want to use whatever url rewrite scheme your http server allow to set it up so that a requested URL like `/mb2sylmar/racer/4567` results in the passing of three GET values to the script in the same order:
- `l` represents the first section of the path and should equal the location name. This is required.
- `o` should equal the second section of the path, the object you're trying to get. Acceptable values are `pointsleaderboard` , `laptimeleaderboard`, `racer`, `heat`, or `search`. If this is left off, the object defaults to `pointsleaderboard` and the location's points leaderboard be returned.
- `q` is the third value of the path, a required value with any object type except for `pointsleaderboard` and `laptimeleaderboard`. If `o` is set to `heat`, then this value should be the heat ID that you're requesting. If `o` is set to `racer`, then this value should be the racer ID that you're requesting. This can be a string in the case of the `search` object. And for the `laptimeleaderboard` object, you can set this to `1`, `7` or `30` for as many days of history you'd like to include in the leaderboard.

##### Cache
I prefer for this to run with some kind of caching. The version in production uses memchached. Since the data we're crawling doesn't change that often, it's nice to cache requests for five minutes or so and save Club Speed servers from any uneccessary load of repetitive requests for pages that likely haven't changed. If the app has just requested the page recently, it should look to the cache to retrieve the data again, not initiate a new http request to the Club Speed URL. You can configure it to use your own in the `_config.php` file and `PageRequest.php` class file, or leave it disabled if you're confident that your instance won't be getting a high volume of traffic.

***

## "x Doesn't Work!"
Things tends to break sometimes, or not work as expected. Send me a tweet with any questions that come up using this: [@thomasesmith](http://twitter.com/thomasesmith).