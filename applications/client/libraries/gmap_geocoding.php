<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gmap_geocoding
{
    /**
     * @class vars
     */

    // Google´s geocode URL
    public $url = 'http://maps.google.com/maps/api/geocode/json?';

    // Params for request
    public $sensor       = "false"; // REQUIRED FOR REQUEST!
    public $language     = "en";

    // Class vars
    public $response     = '';
    public $country_long = '';
    public $country_short= '';
    public $region_long  = '';
    public $region_short = '';
    public $city         = '';
    public $address      = '';
    public $lat          = '';
    public $lng          = '';
    public $location_type= '';

    /**
     * Constructor
     *
     * @param mixed $config
     * @return void
     */
    public function __construct($config = null)
    {

    }

    /**
     * Forward search: string must be an address
     *
     * @param string $address
     * @return obj $response
     */
    public function forwardSearch($address, $limit = 3)
    {
        return $this->_sendRequest("address=" . urlencode(stripslashes($address)), $limit);
    } // end forward

    /**
     * Reverse search: string must be latitude and longitude
     *
     * @param float $lat
     * @param float $lng
     * @return obj $response
     */
    public function reverseSearch($lat, $lng)
    {
        return $this->_sendRequest("latlng=" . (float) $lat . ',' . (float) $lng);
    } // end reverse

    /**
     * Search Address Components Object
     *
     * @param string $type
     * @return object / false
     */
    public function searchAddressComponents($type, $entry = array())
    {
        foreach ($entry -> address_components as $k => $found)
        {
            if (in_array($type, $found -> types))
            {
                return $found;
            }
        }
        return FALSE;
    }

    public function _parse_results($limit = 3)
    {
        $results = array();
        $index = 0;
        if (count($this -> response -> results) > 0)
        {
            foreach ($this -> response -> results as $entry)
            {
               $new_entry['country'] = $this -> searchAddressComponents("country", $entry);
               $new_entry['country_long'] = $new_entry['country'] -> long_name;
               $new_entry['country_short'] = $new_entry['country'] -> short_name;
               $new_entry['region'] = $this -> searchAddressComponents("administrative_area_level_1", $entry);
               $new_entry['region_long'] = isset($new_entry['region'] -> long_name) ? $new_entry['region'] -> long_name : '';
               $new_entry['region_short'] =isset($new_entry['region'] -> short_name) ? $new_entry['region'] -> short_name : '';
               $new_entry['city'] = $this -> searchAddressComponents("locality", $entry);
               $new_entry['formatted_address'] = $entry -> formatted_address;
               $new_entry['lat'] = $entry -> geometry -> location -> lat;
               $new_entry['lng'] = $entry -> geometry -> location -> lng;
               $new_entry['location_type'] = $entry -> geometry -> location_type;
               $new_entry['street_name'] = $this -> searchAddressComponents("route", $entry);
               $street_number =  $this -> searchAddressComponents("street_number", $entry);
               $new_entry['street_number'] = ( ! empty($street_number)) ? $street_number : '';
               $new_entry['postal_code'] = $this -> searchAddressComponents("postal_code", $entry);
               $fnumber = $this -> searchAddressComponents("subpremise", $entry);

               if ($fnumber == FALSE)
               {
                   $new_entry['fnumber'] = (object)array('long_name'  =>  ' ', 'short_name'  =>  ' ');
               }
               else
               {
                   $new_entry['fnumber'] = $fnumber;
               }
               $results[] = $new_entry;

               $index++;
            }
        }
        //$results['count'] = $index - 1;
        //return $results;
        return array_slice($results, 1, $limit);
    }
    /**
     * Parse JSON default values: map object values to readable content
     *
     * @param none
     * @return none
     */
    private function _setDefaults()
    {
        $country = $this->searchAddressComponents("country");
        $this->country_long    = $country->long_name;
        $this->country_short    = $country->short_name;
        $region = $this->searchAddressComponents("administrative_area_level_1");
        $this->region_long = $region->long_name;
        $this->region_short    = $region->short_name;
        $city = $this->searchAddressComponents("locality");
        $this->city    = $city->short_name;
        $this->address = $this->response->results[0]->formatted_address;
        $this->lat = $this->response->results[0]->geometry->location->lat;
        $this->lng = $this->response->results[0]->geometry->location->lng;
        $this->location_type = $this->response->results[0]->geometry->location_type;
    } // end set

    /**
     * Send Google geocoding request
     *
     * @param string $search
     * @return object response (body only)
     */
    /**
     * Send Google geocoding request
     *
     * @param string $search
     * @return object response (body only)
     */
    private function _sendRequest($search, $limit)
    {
        $url = $this -> url . $search . '&language=' . strtolower($this->language) . '&sensor=' . strtolower($this -> sensor);

        $resp_json = self::curl_file_get_contents($url);
        $this -> response = json_decode($resp_json);

        if ($this -> response -> status == "OK")
        {
            return $this->_parse_results($limit);
        }
        else
        {
            echo 'Geocoding failed, server responded: ' . $this -> response -> status;
            return FALSE;
        }
    } // end request

    /**
     * Use CURL to make request
     *
     * @param URL
     * @return Contents
     */
    private function curl_file_get_contents($URL)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
        else return FALSE;
    }

} // end class