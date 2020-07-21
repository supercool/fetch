# Fetch plugin for Craft CMS 3.x

A field type to embed videos for Craft CMS

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require supercool/fetch

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Fetch.


## Usage

Supported providers (links taken at random from site for example purposes):

* Vimeo - Videos (e.g https://vimeo.com/437391842)
* Twitter - Tweets (e.g https://twitter.com/Telegraph/status/1284153855098355712)
* Youtube - Videos (e.g https://www.youtube.com/watch?v=1DxRko3ER4Q)
* Flickr - Pictures (e.g https://www.flickr.com/photos/53889145@N02/7029097495/)
* Soundcloud - Audio (e.g https://soundcloud.com/queen-69312/dont-stop-me-now-remastered)
* Instagram - Posts, NOT STORIES (e.g https://www.instagram.com/p/CCxzeaeDGxV/)

### Settings

#### Plugin

"validateUrlsOnSave" - Lightswitch - When disabled, elements will be able to save even if the link is invalid, or the service is non-responsive.
this can be used in the case of another service issue, similar to when linode was blacklisted by youtube.

#### Field

Each available provider will have a lightswitch that allows you to enable/disable them for the field. This will default to enabled.


### Twig

If the field is not populated, it will return either null or an empty string.

If the field is populated and has no issues, it will return a FetchModel (detailed below)

If the field is populated and has errors getting the embed, it will return a FetchModel containing no data

#### FetchModel

Available methods:

```
{{ field.success }}
or
{{ field.getSuccess() }}
```
Returns a boolean, false if there were any errors fetching the embed, true if there weren't

***

```
{{ field.errorMessage }}
or
{{ field.getErrorMessage() }}
```
If the embed fetch was unsuccessful, returns the error message, else returns null

***

```
{{ field.twig }}
or
{{ field.getTwig() }}
```
Returns the twig for the embed, will be an empty string if there was an error fetching the embed

***

```
{{ field.html }}
or
{{ field.getHtml() }}
```
Identical to ```.twig / .getTwig()```, but returns the markup as html rather than twig_markup

***

```
{{ field.provider }}
or
{{ field.getProvider() }}
```
Returns the provider identified for the url, in lowercase (e.g youtube), or null if there was an error

***

```
{{ field.object }}
or
{{ field.getObject() }}
```
Returns the data received from the oembed api, or null if there was an error.

This data will vary from api to api, and can best be found by looking at the relevant api docs.
e.g https://developers.facebook.com/docs/instagram/embedding/ for instagram.

***


Brought to you by [Supercool Ltd](http://www.supercooldesign.co.uk/)
