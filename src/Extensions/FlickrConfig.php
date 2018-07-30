<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\Flickr\Extensions
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-flickr
 */

namespace SilverWare\Flickr\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverWare\Extensions\Config\ServicesConfig;
use SilverWare\Forms\FieldSection;
use SilverWare\Flickr\API\FlickrAPI;

/**
 * An extension of the services config class which adds Flickr settings to site configuration.
 *
 * @package SilverWare\Flickr\Extensions
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-flickr
 */
class FlickrConfig extends ServicesConfig
{
    /**
     * Maps field names to field types for this object.
     *
     * @var array
     * @config
     */
    private static $db = [
        'FlickrAPIKey' => 'Varchar(128)'
    ];
    
    /**
     * Updates the CMS fields of the extended object.
     *
     * @param FieldList $fields List of CMS fields from the extended object.
     *
     * @return void
     */
    public function updateCMSFields(FieldList $fields)
    {
        // Update Field Objects (from parent):
        
        parent::updateCMSFields($fields);
        
        // Create Flickr Tab:
        
        $fields->findOrMakeTab(
            'Root.SilverWare.Services.Flickr',
            $this->owner->fieldLabel('Flickr')
        );
        
        // Create Field Objects:
        
        $fields->addFieldsToTab(
            'Root.SilverWare.Services.Flickr',
            [
                FieldSection::create(
                    'FlickrAPIConfig',
                    $this->owner->fieldLabel('FlickrAPIConfig'),
                    [
                        TextField::create(
                            'FlickrAPIKey',
                            $this->owner->fieldLabel('FlickrAPIKey')
                        )->setRightTitle(
                            _t(
                                __CLASS__ . '.FLICKRAPIKEYRIGHTTITLE',
                                'Create an API key from your Flickr account and paste the key here.'
                            )
                        )
                    ]
                )
            ]
        );
    }
    
    /**
     * Updates the field labels of the extended object.
     *
     * @param array $labels Array of field labels from the extended object.
     *
     * @return void
     */
    public function updateFieldLabels(&$labels)
    {
        // Update Field Labels (from parent):
        
        parent::updateFieldLabels($labels);
        
        // Update Field Labels:
        
        $labels['Flickr'] = _t(__CLASS__ . '.FLICKR', 'Flickr');
        $labels['FlickrAPIKey'] = _t(__CLASS__ . '.FLICKRAPIKEY', 'Flickr API Key');
        $labels['FlickrAPIConfig'] = _t(__CLASS__ . '.FLICKRAPI', 'Flickr API');
    }
    
    /**
     * Event method called before the extended object is written to the database.
     *
     * @return void
     */
    public function onBeforeWrite()
    {
        // Clean Attributes:
        
        $this->owner->FlickrAPIKey = trim($this->owner->FlickrAPIKey);
    }
    
    /**
     * Answers a status message array for the CMS interface.
     *
     * @return string
     */
    public function getFlickrStatusMessage()
    {
        $api = FlickrAPI::singleton();
        
        if (!$api->hasAPIKey()) {
            
            return _t(
                __CLASS__ . '.FLICKRAPIKEYMISSING',
                'Flickr API key has not been entered into site configuration.'
            );
            
        }
    }
}
