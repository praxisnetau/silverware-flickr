# SilverWare Flickr Module

[![Latest Stable Version](https://poser.pugx.org/silverware/flickr/v/stable)](https://packagist.org/packages/silverware/flickr)
[![Latest Unstable Version](https://poser.pugx.org/silverware/flickr/v/unstable)](https://packagist.org/packages/silverware/flickr)
[![License](https://poser.pugx.org/silverware/flickr/license)](https://packagist.org/packages/silverware/flickr)

Provides a component to show a series of tagged photos from a Flickr account in [SilverWare][silverware] apps.

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Issues](#issues)
- [Contribution](#contribution)
- [Maintainers](#maintainers)
- [License](#license)

## Requirements

- [SilverWare][silverware]
- [SilverWare Lightbox Module][silverware-lightbox]

## Installation

Installation is via [Composer][composer]:

```
$ composer require silverware/flickr
```

## Configuration

As with all SilverStripe modules, configuration is via YAML. You may modify the default endpoint and timeout
for the API via YAML:

```yml
SilverWare\Flickr\API\FlickrAPI:
  default_endpoint: https://api.flickr.com/services/rest
  default_timeout: 10
```

Before this module can be used, you will need to create a Flickr API key.
Once you have created your API key, you can define it for your app in one of two ways:

- via site configuration (Settings tab)
- via YAML configuration file

This module will add a Flickr tab to the Services tab under SilverWare
in your site settings. You can paste your API key into the 'Flickr API Key' field.

Alternatively, you can add your API key to YAML config for your app:

```yml
SilverWare\Flickr\API\FlickrAPI:
  api_key: <paste the key here>
```

The key defined in site configuration will take precedence over the YAML key.

## Usage

This module provides a `FlickrPhotosComponent` for use within your SilverWare templates and layouts.

After creating the component, you may enter the Flickr User ID (also known as the NSID) for the component
using the CMS. The Flickr User ID is typically in the format `12345678@N01`.  In addition, you can define a
series of comma-separated tags in order to show only photos with those tags.

On the Options tab of the component, you may change any of the default options, which include:

* Number of photos
* Thumbnail size (in pixels)
* Tag mode: all or any (determines whether to show photos with all tags, or any tag)
* Title mode: none, title or footer (for lightbox popups)
* Logo width (in pixels)
* Link title
* Cache duration (in seconds)

Resulting photos are cached between requests to avoid unnecessary API calls. Any time you change either
the User ID, photo tags, or tag mode, the component will automatically clear the cache. Flushing the site will also
clear the photo cache.

## Issues

Please use the [issue tracker][issues] for bug reports and feature requests.

## Contribution

Your contributions are gladly welcomed to help make this project better.
Please see [contributing](CONTRIBUTING.md) for more information.

## Maintainers

[![Colin Tucker](https://avatars3.githubusercontent.com/u/1853705?s=144)](https://github.com/colintucker) | [![Praxis Interactive](https://avatars2.githubusercontent.com/u/1782612?s=144)](https://www.praxis.net.au)
---|---
[Colin Tucker](https://github.com/colintucker) | [Praxis Interactive](https://www.praxis.net.au)

## License

[BSD-3-Clause](LICENSE.md) &copy; Praxis Interactive

[silverware]: https://github.com/praxisnetau/silverware
[silverware-lightbox]: https://github.com/praxisnetau/silverware-lightbox
[composer]: https://getcomposer.org
[issues]: https://github.com/praxisnetau/silverware-flickr/issues
