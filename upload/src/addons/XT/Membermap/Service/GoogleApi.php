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

    private $serviceList = [
        'javascript' => '',
        'geocode' => 'https://maps.google.com/maps/api/geocode/json',
        'staticmap' => 'https://maps.googleapis.com/maps/api/staticmap',
      ];

    private function getServiceUrl($serviceID)
    {
        $service = strtolower($serviceID);

        switch($service) {
            case 'static':
            case 'staticmap':
                return $this->serviceList['staticmap'];
                break;
            case 'geocode':
                return $this->serviceList['geocode'];
                break;
            case 'javascript':
                return $this->serviceList['javascript'];
                break;
            default:
                return false;
            break;
        }
    }

    public function fetchService($serviceID, $params, $raw=false)
    {
        $url = $this->getServiceUrl($serviceID);        
        
        if(!empty($this->getApiKey()))
        {
            $params += [
                'key' => $this->getApiKey(),
            ]; 
        }
               
        $apiUrl =  $url . '?' . http_build_query($params);

        return $apiUrl;
    }

    public function fetchLocationData($location)
    {
        $serviceID = 'geocode';
        $address = $location;
        $params = [
            'address' => $address,
        ];

        $apiUrl = $this->fetchService($serviceID, $params);

        /** @var \GuzzleHttp\Client $client **/
        $client = \XF::app()->http()->client();
        $geocodeResponse = $client->get($apiUrl)->getBody();
        $geocodeData = \GuzzleHttp\json_decode($geocodeResponse);

        /** @var \XT\Membermap\XF\Entity\User $visitor */
        $visitorId = \XF::visitor()->user_id;
        
        $logData = [
            'user_id' => $visitorId,
            'request_url' => $apiUrl,
            'request_status' => $geocodeData->status,
            'request_data' => $geocodeData->results
        ];

        $requestLog = $this->em()->create('XT\Membermap:Log');
        $requestLog->bulkSet($logData);
        $requestLog->save();

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
     * @deprecated use User Entity function instead
     */
    public function getStaticLocationImageUser(\XF\Entity\User $user)
    {
        // try to find a saved image
        $minimapUrl = $user->getMinimapUrl();
        $minimapPath = $user->getAbstractedMinimapPath();

        if (!\XF\Util\File::abstractedPathExists($minimapPath))
        {
            $image = $this->fetchStaticLocationImageUser($user->Profile, $user->getMapMarkerIconPath());
            if (!\XF\Util\File::copyFileToAbstractedPath($image, $minimapPath))
            {
                $minimapUrl = false;
            }
        }

        return $minimapUrl;
    }

    /**
     * @param \XF\Entity\User $user
     * @return binary image;
     */
    public function fetchStaticLocationImageUser(\XF\Entity\UserProfile $profile, string $icon = '')
    {
        $serviceID = 'static';
        $params = [
            'zoom' => 13,
            'size' => '228x228',
            'markers' => $this->markerBuilder($profile->xt_mm_location_lat, $profile->xt_mm_location_long, $icon)
        ];
        $url = $this->fetchService($serviceID, $params);

        $logData = [
            'user_id' => $profile->user_id,
            'request_url' => $url,
            'request_status' => (isset($url) ? 'OK' : 'ERROR'),
            'request_data' => [
                'Latitude' => $profile->xt_mm_location_lat,
                'Longetude' => $profile->xt_mm_location_long,
                'Icon' => $icon,
                'Marker' => $this->markerBuilder($profile->xt_mm_location_lat, $profile->xt_mm_location_long, $icon)
            ],
        ];

        $requestLog = $this->em()->create('XT\Membermap:Log');
        $requestLog->bulkSet($logData);
        $requestLog->save();

        return $url;
    }

    private function markerBuilder($lat, $long, $icon)
    {
        if (!empty($icon))
        {
            $marker = 'icon:' . $icon;
        }
        else
        {
            $marker = 'color:red';
        }
        $marker .= '|' . $lat . ',' . $long;
        
        return $marker;
    }
    /**
     * @param $apiKey String if a new key should be checked
     * @return Bool is ApiKey works
     */
    public function isAvaiable($testKey = false)
    {
        if ($testKey)
        {
            $serviceID = 'geocode';
            $params = [
                'address' => 'Berlin',
                'key' => $testKey,
            ];
            $apiUrl = $this->fetchService($serviceID, $params);

            /** @var \GuzzleHttp\Client $client **/
            $client = \XF::app()->http()->client();
            $response = $client->get($apiUrl)->getBody();
            $data = \GuzzleHttp\json_decode($response);

            if ($data->status == 'OK')
            {
                return true;
            }
            return false;
        }
        else
        {
            return !empty($this->getApiKey());
        }
    }
}


