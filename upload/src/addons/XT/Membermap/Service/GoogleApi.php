<?php

namespace XT\Membermap\Service;

use Exception;
use XF\Mvc\Entity\Entity;
use XF\Service\AbstractService;
use XF\Util\Arr;

class GoogleApi extends AbstractService
{
    protected $apiKey = null;

    protected function setup()
    {
        $this->apiKey = \XF::options()->xtMMGoogleMapsApiKey;
    }

    private function getApiKey()
    {
        return $this->apiKey;
    }

    public function fetchLocationData($location)
    {
        $apiUrl = 'https://maps.google.com/maps/api/geocode/json?address=' . urlencode($location) . '&key=' . $this->getApiKey();

        /**
         * @var \GuzzleHttp\Client $client
         */
        $client = \XF::app()->http()->client();

        $geocodeResponse = $client->get($apiUrl)->getBody();

        $geocodeData = \GuzzleHttp\json_decode($geocodeResponse);

        if (
            !empty($geocodeData)
            && $geocodeData->status == 'OK'
            && isset($geocodeData->results)
            && isset($geocodeData->results[0])
        ) {
            return [
                'latitude' => $geocodeData->results[0]->geometry->location->lat,
                'longitude' => $geocodeData->results[0]->geometry->location->lng
            ];
        }
    }

    /**
     * @param $apiKey String if a new key should be checked
     * @return Bool is ApiKey works
     */
    public function isAvaiable()
    {
        if (!$this->getApiKey())
        {
            $url = 'https://maps.google.com/maps/api/geocode/json?address=Berlin&key=' . $this->getApiKey();
            /**
             * @var \GuzzleHttp\Client $client
             */
            $client = \XF::app()->http()->client();
            $response = $client->get($url)->getBody();
            $jsonResponse = \GuzzleHttp\json_decode($response);
            if ($jsonResponse->status == 'OK')
            {
                return true;
            }
        }
        
        return true;
    }
}


