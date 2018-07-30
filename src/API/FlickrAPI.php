<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\Flickr\API
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-flickr
 */

namespace SilverWare\Flickr\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\SiteConfig\SiteConfig;
use Exception;

/**
 * A singleton wrapper providing access to the Flickr API.
 *
 * @package SilverWare\Flickr\API
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-flickr
 */
class FlickrAPI
{
    use Injectable;
    use Configurable;
    
    /**
     * Defines the default remote API endpoint.
     *
     * @var string
     * @config
     */
    private static $default_endpoint = 'https://api.flickr.com/services/rest';
    
    /**
     * Defines the default API timeout period in seconds.
     *
     * @var integer
     * @config
     */
    private static $default_timeout = 10;
    
    /**
     * Defines the photo source URL for retrieving photos from Flickr.
     *
     * @var string
     * @config
     */
    private static $photo_source_url = 'https://farm%s.staticflickr.com/%s/%s_%s_%s.jpg';
    
    /**
     * Defines the remote API endpoint.
     *
     * @var string
     */
    protected $endpoint;
    
    /**
     * Defines the API timeout period in seconds.
     *
     * @var integer
     */
    protected $timeout;
    
    /**
     * Holds the client for interacting with the remote API.
     *
     * @var GuzzleHttp\Client
     */
    protected $client;
    
    /**
     * Defines the format of the API response.
     *
     * @var string
     */
    protected $format = 'php_serial';
    
    /**
     * Constructs the object upon instantiation.
     */
    public function __construct()
    {
        // Define Endpoint:
        
        $this->endpoint = self::config()->default_endpoint;
        
        // Define Timeout:
        
        $this->timeout = self::config()->default_timeout;
        
        // Create Client:
        
        $this->client = new Client();
    }
    
    /**
     * Defines the value of the endpoint attribute.
     *
     * @param string $endpoint
     *
     * @return $this
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = (string) $endpoint;
        
        return $this;
    }
    
    /**
     * Answers the value of the endpoint attribute.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }
    
    /**
     * Defines the value of the timeout attribute.
     *
     * @param integer $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (integer) $timeout;
        
        return $this;
    }
    
    /**
     * Answers the value of the timeout attribute.
     *
     * @return integer
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
    
    /**
     * Defines the value of the format attribute.
     *
     * @param string $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = (string) $format;
        
        return $this;
    }
    
    /**
     * Answers the value of the format attribute.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }
    
    /**
     * Answers the API key from site or YAML configuration.
     *
     * @return string
     */
    public function getAPIKey()
    {
        $key = SiteConfig::current_site_config()->FlickrAPIKey;
        
        if (!$key) {
            $key = self::config()->api_key;
        }
        
        return $key;
    }
    
    /**
     * Answers true if the receiver has an API key.
     *
     * @return boolean
     */
    public function hasAPIKey()
    {
        return (boolean) $this->getAPIKey();
    }
    
    /**
     * Issues a request on the API and answers the response.
     *
     * @params string $method
     * @params array $params
     *
     * @return FlickrResponse
     */
    public function call($method, $params = [])
    {
        // Initialise:
        
        $data = [];
        
        // Attempt Request:
        
        try {
            
            // Obtain Response:
            
            $response = $this->client->get($this->endpoint, ['query' => $this->buildQuery($method, $params)]);
            
            // Obtain Response Body:
            
            $body = (string) $response->getBody();
            
            // Convert Body:
            
            $content = $this->processBody($body);
            
            // Define Response Data:
            
            $data = [
                'code' => $response->getStatusCode(),
                'error' => isset($content['code']) ? $content['code'] : null,
                'status' => isset($content['stat']) ? $content['stat'] : null,
                'message' => isset($content['message']) ? $content['message'] : null,
                'content' => $content
            ];
            
        } catch (RequestException $e) {
            
            if ($e->hasResponse()) {
                
                $data = [
                    'code' => $e->getResponse()->getStatusCode(),
                    'status' => 'fail'
                ];
                
            }
            
        }
        
        // Answer Response Object:
        
        return $this->buildResponse($data);
    }
    
    /**
     * Answers a photo source URL for the given array of photo data.
     *
     * @param array $photo
     * @param string $type
     *
     * @return string
     */
    public function getPhotoSource($photo = [], $type = 'm')
    {
        if (isset($photo['farm']) && isset($photo['server']) && isset($photo['id']) && isset($photo['secret'])) {
            
            return $this->getPhotoSourceURL(
                $photo['farm'],
                $photo['server'],
                $photo['id'],
                $photo['secret'],
                $type
            );
            
        }
    }
    
    /**
     * Answers a photo source URL for the given parameters.
     *
     * @param string $farm
     * @param string $server
     * @param string $id
     * @param string $secret
     * @param string $type
     *
     * @return string
     */
    public function getPhotoSourceURL($farm, $server, $id, $secret, $type = 'm')
    {
        return sprintf($this->config()->photo_source_url, $farm, $server, $id, $secret, $type);
    }
    
    /**
     * Builds the query array for issuing the request.
     *
     * @params string $method
     * @params array $params
     *
     * @return array
     */
    protected function buildQuery($method, $params = [])
    {
        $query = [
            'api_key' => $this->getAPIKey(),
            'format' => $this->format,
            'method' => $method
        ];
        
        $query = array_merge($query, $params);
        
        return $query;
    }
    
    /**
     * Builds a response object from the given data.
     *
     * @param array $data
     *
     * @return FlickrResponse
     */
    protected function buildResponse($data = [])
    {
        return FlickrResponse::create($data);
    }
    
    /**
     * Converts the response body into an array.
     *
     * @param string $body
     *
     * @return array
     */
    protected function processBody($body)
    {
        switch ($this->format) {
            
            case 'php_serial':
                return unserialize($body);
            
        }
        
        return [];
    }
}
