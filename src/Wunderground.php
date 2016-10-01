<?php
namespace Skybluesofa\Wunderground;

use InvalidArgumentException;

class Wunderground
{

    /**
     * @var string Weather Underground provided API key
     */
    public $apikey;

    private $state = null;
    private $cityOrZip = null;

    const CONDITIONS = 'conditions';
    const ALERTS = 'alerts';
    const FORECAST = 'forecast';

    /**
     * Wunderground constructor.
     * @param string $key API Key
     * @throws InvalidArgumentException if an empty API key is provided.
     */
    function __construct($key=null, $state=null, $cityOrZip=null)
    {
        if (empty($key)) {
            $dotenv = new Dotenv\Dotenv(__DIR__);
            $dotenv->load();
            $key = getenv('WUNDERGROUND_API_KEY');
        }
        if (empty($key)) {
            throw new InvalidArgumentException('Wunderground class requires a valid API key');
        }

        $this->apikey = urlencode($key);
        $this->state = urlencode($state);
        $this->cityOrZip = urlencode($cityOrZip);
    }

    function updateLocation($arg1=null, $arg2=null) {
        $this->state = $arg1 ? $arg1 : $this->state;
        $this->cityOrZip = $arg2 ? $arg2 : $this->cityOrZip;

    }

    /**
     * Returns the current conditions as a decoded JSON object
     *
     * @param string|int $arg1 State or zip code
     * @param string $arg2 City if first argument is a state
     * @return mixed
     */
    function currentConditions($arg1=null, $arg2 = null)
    {
        $this->updateLocation($arg1, $arg2);
        $location = $this->buildLocation($this->state, $this->cityOrZip);

        $results = $this->apiCall(Wunderground::CONDITIONS, $location);

        return $results->current_observation;
    }

    /**
     * Returns current severe weather alerts as a decoded JSON object
     *
     * @param string|int $arg1 State or zip code
     * @param string $arg2 City if first argument is a state
     * @return mixed
     */
    function alerts($arg1=null, $arg2=null)
    {
        $this->updateLocation($arg1, $arg2);
        $location = $this->buildLocation($this->state, $this->cityOrZip);

        $results = $this->apiCall(Wunderground::ALERTS, $location);

        return $results->alerts;
    }

    /**
     * Returns current conditions and severe weather alerts as a decoded JSON object
     *
     * @param string|int $arg1 State or zip code
     * @param string $arg2 City if first argument is a state
     * @return mixed
     */
    function currentConditionsAndAlerts($arg1=null, $arg2=null)
    {
        $this->updateLocation($arg1, $arg2);
        $location = $this->buildLocation($this->state, $this->cityOrZip);

        $results = $this->apiCall(Wunderground::CONDITIONS . '/' . Wunderground::ALERTS, $location);

        $return = new \stdClass();
        $return->conditions = $results->current_observation;
        $return->alerts = $results->alerts;

        return $return;
    }

    /**
     * Returns current conditions and severe weather alerts as a decoded JSON object
     *
     * @param string|int $arg1 State or zip code
     * @param string $arg2 City if first argument is a state
     * @return mixed
     */
    public function forecast($arg1=null, $arg2=null)
    {
        $this->updateLocation($arg1, $arg2);
        $location = $this->buildLocation($this->state, $this->cityOrZip);

        $results = $this->apiCall(Wunderground::FORECAST, $location);

        $return = new \stdClass();
        $return->forecast = $results->forecast->simpleforecast->forecastday;

        return $return;
    }

    /**
     * URL encodes the arguments to pass to the API call
     *
     * @param string|int $arg1 State or zip code
     * @param string $arg2 City if first argument is a state
     * @return string
     */
    private function buildLocation($arg1, $arg2)
    {

        if (empty($arg1)) {
            return $this->apiCall(Wunderground::CONDITIONS, 'CA/San_Francisco');
        }

        return urlencode($arg1) . (empty($arg2) ? '/' . urlencode($arg2) : '');

        return $location;
    }

    /**
     * Makes the API call
     *
     * @param Methods|string $method API method
     * @param string $location url encoded location
     * @return mixed
     */
    private function apiCall($method, $location)
    {
        $method = $method ? '/'.$method : '';
        $url = 'http://api.wunderground.com/api/' . $this->apikey . $method . '/q/' . $location . '.json';
        $json = file_get_contents($url);
        return json_decode($json);
    }


}
