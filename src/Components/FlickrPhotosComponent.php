<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\Flickr\Components
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-flickr
 */

namespace SilverWare\Flickr\Components;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Flushable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverWare\Components\BaseComponent;
use SilverWare\Forms\FieldSection;

/**
 * An extension of the base component class for a Flickr photos component.
 *
 * @package SilverWare\Flickr\Components
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2018 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-flickr
 */
class FlickrPhotosComponent extends BaseComponent implements Flushable
{
    /**
     * Define tag mode constants.
     */
    const TAG_MODE_ANY = 'any';
    const TAG_MODE_ALL = 'all';
    
    /**
     * Define title mode constants.
     */
    const TITLE_MODE_NONE   = 'none';
    const TITLE_MODE_TITLE  = 'title';
    const TITLE_MODE_FOOTER = 'footer';
    
    /**
     * Defines the injector dependencies for this object.
     *
     * @var array
     * @config
     */
    private static $dependencies = [
        'api' => '%$FlickrAPI'
    ];
    
    /**
     * Human-readable singular name.
     *
     * @var string
     * @config
     */
    private static $singular_name = 'Flickr Photos Component';
    
    /**
     * Human-readable plural name.
     *
     * @var string
     * @config
     */
    private static $plural_name = 'Flickr Photos Components';
    
    /**
     * Description of this object.
     *
     * @var string
     * @config
     */
    private static $description = 'A component which shows a series of photos from Flickr';
    
    /**
     * Icon file for this object.
     *
     * @var string
     * @config
     */
    private static $icon = 'silverware/flickr: admin/client/dist/images/icons/FlickrPhotosComponent.png';
    
    /**
     * Defines the table name to use for this object.
     *
     * @var string
     * @config
     */
    private static $table_name = 'SilverWare_FlickrPhotosComponent';
    
    /**
     * Defines an ancestor class to hide from the admin interface.
     *
     * @var string
     * @config
     */
    private static $hide_ancestor = BaseComponent::class;
    
    /**
     * Maps field names to field types for this object.
     *
     * @var array
     * @config
     */
    private static $db = [
        'User' => 'Varchar(32)',
        'Tags' => 'Varchar(255)',
        'TagMode' => 'Varchar(8)',
        'TitleMode' => 'Varchar(8)',
        'LinkTitle' => 'Varchar(255)',
        'LogoWidth' => 'Int',
        'CacheDuration' => 'Int',
        'NumberOfPhotos' => 'AbsoluteInt',
        'ThumbnailSize' => 'Int',
        'HideNoDataMessage' => 'Boolean'
    ];
    
    /**
     * Defines the default values for the fields of this object.
     *
     * @var array
     * @config
     */
    private static $defaults = [
        'TagMode' => self::TAG_MODE_ANY,
        'TitleMode' => self::TITLE_MODE_NONE,
        'LogoWidth' => 50,
        'ThumbnailSize' => 50,
        'CacheDuration' => 1800,
        'NumberOfPhotos' => 20,
        'HideNoDataMessage' => 0
    ];
    
    /**
     * Maps field and method names to the class names of casting objects.
     *
     * @var array
     * @config
     */
    private static $casting = [
        'getTitleModeAttribute' => 'HTMLFragment'
    ];
    
    /**
     * Defines the URL for the Flickr link.
     *
     * @var string
     * @config
     */
    private static $flickr_url = 'https://www.flickr.com/photos/%s';
    
    /**
     * Defines the URL for the Flickr link with tags.
     *
     * @var string
     * @config
     */
    private static $flickr_url_tags = 'https://www.flickr.com/photos/%s/tags/%s';
    
    /**
     * Clears the cache upon flush.
     *
     * @return void
     */
    public static function flush()
    {
        self::cache()->clear();
    }
    
    /**
     * Answers the cache object.
     *
     * @return CacheInterface
     */
    public static function cache()
    {
        return Injector::inst()->get(CacheInterface::class . '.FlickrPhotosComponentCache');
    }
    
    /**
     * Answers a list of field objects for the CMS interface.
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        // Obtain Field Objects (from parent):
        
        $fields = parent::getCMSFields();
        
        // Add Status Message (if exists):
        
        $fields->addStatusMessage($this->getSiteConfig()->getFlickrStatusMessage());
        
        // Create Main Fields:
        
        $fields->addFieldsToTab(
            'Root.Main',
            [
                FieldSection::create(
                    'FlickrSection',
                    $this->fieldLabel('Flickr'),
                    [
                        TextField::create(
                            'User',
                            $this->fieldLabel('User')
                        )->setRightTitle(
                            _t(
                                __CLASS__ . '.USERRIGHTTITLE',
                                'Enter the Flickr User ID to retrieve photos (typically in the format 12345678@N01).'
                            )
                        ),
                        TextField::create(
                            'Tags',
                            $this->fieldLabel('Tags')
                        )->setRightTitle(
                            _t(
                                __CLASS__ . '.TAGSRIGHTTITLE',
                                'Separate multiple photo tags with commas.'
                            )
                        )
                    ]
                )
            ]
        );
        
        // Create Options Fields:
        
        $fields->addFieldsToTab(
            'Root.Options',
            [
                FieldSection::create(
                    'FlickrPhotosOptions',
                    $this->fieldLabel('FlickrPhotos'),
                    [
                        TextField::create(
                            'NumberOfPhotos',
                            $this->fieldLabel('NumberOfPhotos')
                        ),
                        NumericField::create(
                            'ThumbnailSize',
                            $this->fieldLabel('ThumbnailSize')
                        ),
                        DropdownField::create(
                            'TagMode',
                            $this->fieldLabel('TagMode'),
                            $this->getTagModeOptions()
                        ),
                        DropdownField::create(
                            'TitleMode',
                            $this->fieldLabel('TitleMode'),
                            $this->getTitleModeOptions()
                        )->setRightTitle(
                            _t(
                                __CLASS__ . '.TITLEMODERIGHTTITLE',
                                'Determines where to show the title of popup images.'
                            )
                        ),
                        NumericField::create(
                            'LogoWidth',
                            $this->fieldLabel('LogoWidth')
                        ),
                        TextField::create(
                            'LinkTitle',
                            $this->fieldLabel('LinkTitle')
                        ),
                        NumericField::create(
                            'CacheDuration',
                            $this->fieldLabel('CacheDuration')
                        ),
                        CheckboxField::create(
                            'HideNoDataMessage',
                            $this->fieldLabel('HideNoDataMessage')
                        )
                    ]
                )
            ]
        );
        
        // Answer Field Objects:
        
        return $fields;
    }
    
    /**
     * Answers the labels for the fields of the receiver.
     *
     * @param boolean $includerelations Include labels for relations.
     *
     * @return array
     */
    public function fieldLabels($includerelations = true)
    {
        // Obtain Field Labels (from parent):
        
        $labels = parent::fieldLabels($includerelations);
        
        // Define Field Labels:
        
        $labels['User'] = _t(__CLASS__ . '.USER', 'User');
        $labels['Tags'] = _t(__CLASS__ . '.TAGS', 'Tags');
        $labels['TagMode'] = _t(__CLASS__ . '.TAGMODE', 'Tag mode');
        $labels['TitleMode'] = _t(__CLASS__ . '.TITLEMODE', 'Title mode');
        $labels['LinkTitle'] = _t(__CLASS__ . '.LINKTITLE', 'Link title');
        
        $labels['CacheDuration'] = _t(__CLASS__ . '.CACHEDURATIONINSECONDS', 'Cache duration (in seconds)');
        $labels['NumberOfPhotos'] = _t(__CLASS__ . '.NUMBEROFPHOTOS', 'Number of photos');
        $labels['HideNoDataMessage'] = _t(__CLASS__ . '.HIDENODATAMESSAGE', 'Hide no data message');
        $labels['ThumbnailSize'] = _t(__CLASS__ . '.THUMBNAILSIZEINPIXELS', 'Thumbnail size (in pixels)');
        $labels['LogoWidth'] = _t(__CLASS__ . '.LOGOWIDTHINPIXELS', 'Logo width (in pixels)');
        
        $labels['Flickr'] = _t(__CLASS__ . '.FLICKR', 'Flickr');
        $labels['FlickrPhotos'] = _t(__CLASS__ . '.FLICKRPHOTOS', 'Flickr Photos');
        
        // Answer Field Labels:
        
        return $labels;
    }
    
    /**
     * Event method called before the receiver is written to the database.
     *
     * @return void
     */
    public function onBeforeWrite()
    {
        // Call Parent Event:
        
        parent::onBeforeWrite();
        
        // Clean Attributes:
        
        $this->cleanUser();
        $this->cleanTags();
        
        // Flush Cache (if required):
        
        if ($this->isChanged('User') || $this->isChanged('Tags') || $this->isChanged('TagMode')) {
            self::cache()->delete($this->getCacheKey());
        }
    }
    
    /**
     * Populates the default values for the fields of the receiver.
     *
     * @return void
     */
    public function populateDefaults()
    {
        // Populate Defaults (from parent):
        
        parent::populateDefaults();
        
        // Populate Defaults:
        
        $this->LinkTitle = _t(__CLASS__ . '.DEFAULTLINKTITLE', 'More photos on Flickr');
    }
    
    /**
     * Answers a list of photos from the Flickr API.
     *
     * @return ArrayList
     */
    public function getPhotos()
    {
        // Create List:
        
        $list = ArrayList::create();
        
        // Check User ID:
        
        if (!$this->User) {
            return $list;
        }
        
        // Obtain Cached Photo Data:
        
        $photos = self::cache()->get($this->getCacheKey());
        
        // Retrieve Photo Data and Cache (if none cached):
        
        if (!$photos) {

            // Retrieve Photo Data via API:
            
            $response = $this->api->call(
                'flickr.photos.search',
                [
                    'user_id' => $this->User,
                    'tags' => $this->Tags,
                    'tag_mode' => $this->TagMode
                ]
            );
            
            // Did We Receive Photo Data? (if so, cache)
            
            if (($photos = $response->get('photos')) && !empty($photos['photo'])) {
                
                self::cache()->set(
                    $this->getCacheKey(),
                    $photos,
                    (integer) $this->CacheDuration
                );
                
            }
            
        }
        
        // Define List:
        
        if (!empty($photos) && !empty($photos['photo'])) {
            
            // Slice Photo Array:
            
            $sliced = array_slice($photos['photo'], 0, $this->NumberOfPhotos);
            
            // Iterate Photos:
            
            foreach ($sliced as $photo) {
                
                $list->push(
                    ArrayData::create([
                        'Title' => $photo['title'],
                        'URL' => $this->api->getPhotoSource($photo, 'b'),
                        'ThumbnailURL' => $this->api->getPhotoSource($photo, 'q')
                    ])
                );
                
            }
            
        }
        
        // Answer List:
        
        return $list;
    }
    
    /**
     * Answers true of the receiver has tags defined.
     *
     * @return boolean
     */
    public function hasTags()
    {
        return (boolean) ($this->Tags !== '');
    }
    
    /**
     * Answers the URL for the Flickr link.
     *
     * @return string
     */
    public function getFlickrLink()
    {
        if ($this->hasTags()) {
            return sprintf($this->config()->flickr_url_tags, $this->User, $this->Tags);
        }
        
        return sprintf($this->config()->flickr_url, $this->User);
    }
    
    /**
     * Answers the title for the Flickr link.
     *
     * @return string
     */
    public function getFlickrLinkTitle()
    {
        return $this->LinkTitle;
    }
    
    /**
     * Answers the width for the Flickr logo.
     *
     * @return string
     */
    public function getFlickrLogoWidth()
    {
        return $this->LogoWidth;
    }
    
    /**
     * Answers the URL for the Flickr logo vector image.
     *
     * @return string
     */
    public function getFlickrLogoURL()
    {
        return ModuleResourceLoader::singleton()->resolveURL('silverware/flickr: client/dist/svg/flickr-logo.svg');
    }
    
    /**
     * Answers a message string to be shown when no data is available.
     *
     * @return string
     */
    public function getNoDataMessage()
    {
        return _t(__CLASS__ . '.NODATAAVAILABLE', 'No data available.');
    }
    
    /**
     * Answers true if the no data message is to be shown.
     *
     * @return boolean
     */
    public function getNoDataMessageShown()
    {
        return !$this->HideNoDataMessage;
    }
    
    /**
     * Answers the key used with the cache.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return sprintf('flickr-photos-component-%d', $this->ID);
    }
    
    /**
     * Answers the title attribute according to the selected title mode.
     *
     * @param string $title
     *
     * @return string
     */
    public function getTitleModeAttribute($title = null)
    {
        if ($this->TitleMode != self::TITLE_MODE_NONE) {
            
            switch ($this->TitleMode) {
                case self::TITLE_MODE_TITLE:
                    return sprintf(' data-title="%s"', $title);
                case self::TITLE_MODE_FOOTER:
                    return sprintf(' data-footer="%s"', $title);
            }
            
        }
    }
    
    /**
     * Answers an array of options for the tag mode field.
     *
     * @return array
     */
    public function getTagModeOptions()
    {
        return [
            self::TAG_MODE_ANY => _t(__CLASS__ . '.ANY', 'Any'),
            self::TAG_MODE_ALL => _t(__CLASS__ . '.ALL', 'All')
        ];
    }
    
    /**
     * Answers an array of options for the title mode field.
     *
     * @return array
     */
    public function getTitleModeOptions()
    {
        return [
            self::TITLE_MODE_NONE => _t(__CLASS__ . '.NONE', 'None'),
            self::TITLE_MODE_TITLE => _t(__CLASS__ . '.TITLE', 'Title'),
            self::TITLE_MODE_FOOTER => _t(__CLASS__ . '.FOOTER', 'Footer')
        ];
    }
    
    /**
     * Cleans the user attribute before writing.
     *
     * @return void
     */
    protected function cleanUser()
    {
        $this->User = trim($this->User);
    }
    
    /**
     * Cleans the tags attribute before writing.
     *
     * @return void
     */
    protected function cleanTags()
    {
        $this->Tags = implode(',', preg_split('/[\s,]+/', $this->Tags, -1, PREG_SPLIT_NO_EMPTY));
    }
}
