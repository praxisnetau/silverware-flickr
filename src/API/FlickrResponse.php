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

use SilverStripe\Core\Injector\Injectable;

/**
 * Represents a response retrieved from the Flickr API.
 *
 * @package SilverWare\Flickr\API
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-flickr
 */
class FlickrResponse
{
    use Injectable;
    
    /**
     * The status code returned by the remote API.
     *
     * @var integer
     */
    protected $code;
    
    /**
     * Any error code returned by the remote API.
     *
     * @var integer
     */
    protected $error;
    
    /**
     * The status returned by the remote API.
     *
     * @var string
     */
    protected $status;
    
    /**
     * Any message received from the remote API.
     *
     * @var string
     */
    protected $message;
    
    /**
     * The content of the response received from the remote API.
     *
     * @var array
     */
    protected $content;
    
    /**
     * Constructs the object upon instantiation.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        // Construct Object:
        
        $this->fromData($data);
    }
    
    /**
     * Defines the value of the code attribute.
     *
     * @param integer $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = (integer) $code;
        
        return $this;
    }
    
    /**
     * Answers the value of the code attribute.
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }
    
    /**
     * Defines the value of the error attribute.
     *
     * @param integer $error
     *
     * @return $this
     */
    public function setError($error)
    {
        $this->error = (integer) $error;
        
        return $this;
    }
    
    /**
     * Answers the value of the error attribute.
     *
     * @return integer
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * Defines the value of the status attribute.
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = (string) $status;
        
        return $this;
    }
    
    /**
     * Answers the value of the status attribute.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Defines the value of the message attribute.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = (string) $message;
        
        return $this;
    }
    
    /**
     * Answers the value of the message attribute.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * Defines the value of the content attribute.
     *
     * @param array $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = (array) $content;
        
        return $this;
    }
    
    /**
     * Answers the value of the content attribute.
     *
     * @return array
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Defines the receiver from the given data array.
     *
     * @param array $data
     *
     * @return void
     */
    public function fromData($data = [])
    {
        if (isset($data['code'])) {
            $this->setCode($data['code']);
        }
        
        if (isset($data['error'])) {
            $this->setError($data['error']);
        }
        
        if (isset($data['status'])) {
            $this->setStatus($data['status']);
        }
        
        if (isset($data['message'])) {
            $this->setMessage($data['message']);
        }
        
        if (isset($data['content'])) {
            $this->setContent($data['content']);
        }
    }
    
    /**
     * Answers a named value from the content array.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return isset($this->content[$name]) ? $this->content[$name] : null;
    }
}
