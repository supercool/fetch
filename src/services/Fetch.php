<?php

/**
 * Fetch plugin for Craft CMS 3.x
 *
 * A field type to embed videos for Craft CMS
 *
 * @link      http://www.supercooldesign.co.uk/
 * @copyright Copyright (c) 2018 Supercool Ltd
 */

namespace supercool\fetch\services;

use Craft;
use yii\base\Component;

use supercool\fetch\Fetch as FetchPlugin;

class Fetch extends Component
{

    private $settings = '';

    public function __construct()
    {
        // get plugin settings
        $plugin = FetchPlugin::$plugin;
        $this->settings = $plugin->getSettings();
    }


    public function get($url, $scripts = true)
    {
        if (!$url) {
            return;
        }

        // Check cache first
        $cache = Craft::$app->getCache();
        $cached = $cache->get('fetch.'.$url);

        if ($cached)
        {
            return $cached;
        }
        else
        {
            // clean up spaces, flipping users.
            $url = trim($url);

            // check if there is a protocol, add if not
            if ( parse_url($url, PHP_URL_SCHEME) === null )
            {
                $url = 'http://' . $url;
            }

            // prep
            $apiUrl = '';
            $provider = '';

            if ( $this->settings['embedlyApiKey'] != '' )
            {
                $embedlyApiKey = $this->settings['embedlyApiKey'];
            }
            else
            {
                $embedlyApiKey = false;
            }

            // switch on the provider, starting with vimeo
            if ( strpos($url, 'vimeo') !== false )
            {
                $provider = 'vimeo';
                $apiUrl = 'https://vimeo.com/api/oembed.json?url='.$url.'&byline=false&title=false&portrait=false&autoplay=false';
            }
            // twitter
            elseif ( strpos($url, 'twitter') !== false )
            {
                $provider = 'twitter';
                if ( $scripts ) {
                    $apiUrl = 'https://api.twitter.com/1/statuses/oembed.json?url='.$url;
                } else {
                    $apiUrl = 'https://api.twitter.com/1/statuses/oembed.json?url='.$url.'&omit_script=true';
                }
            }
            // youtube
            elseif ( strpos($url, 'youtu') !== false )
            {
                $provider = 'youtube';
                $apiUrl = 'https://www.youtube.com/oembed?url='.$url.'&format=json';
                // add these params to the html after curling ?
                // &modestbranding=1&rel=0&showinfo=0&autoplay=0
            }
            // flickr
            elseif ( strpos($url, 'flickr') !== false )
            {
                $provider = 'flickr';
                $apiUrl = 'https://www.flickr.com/services/oembed?url='.$url.'&format=json';
            }
            // soundcloud
            elseif ( strpos($url, 'soundcloud') !== false )
            {
                $provider = 'soundcloud';
                $apiUrl = 'https://soundcloud.com/oembed?url='.$url.'&format=json';
            }
            // instagram
            elseif ( strpos($url, 'instagr') !== false )
            {
                $provider = 'instagram';

                // Try and parse out the shortcode
                if (preg_match("/(https?:)?\/\/(.*\.)?instagr(\.am|am\.com)\/p\/([^\/]*)/i", $url, $matches))
                {
                    if (isset($matches[4]))
                    {
                        $shortcode = $matches[4];
                        $url = "https://www.instagram.com/p/{$shortcode}/";
                    }
                }
                $apiUrl = 'https://api.instagram.com/oembed/?url='.$url;
            }
            // pinterest
            elseif ( strpos($url, 'pinterest') !== false && $embedlyApiKey )
            {
                $provider = 'pinterest';
                $apiUrl = 'https://api.embed.ly/1/oembed?key='.$embedlyApiKey.'&url='.$url;
            }
            // unsupported service
            else
            {
                return [
                    'success' => false,
                    'error' => Craft::t("fetch", "Sorry that service isn’t supported yet.")
                ];
            }

            // create curl resource
            $ch = curl_init();

            // set url
            curl_setopt($ch, CURLOPT_URL, $apiUrl);

            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // $output contains the output string
            $output = curl_exec($ch);

            // close curl resource to free up system resources
            curl_close($ch);

            // decode returned json
            $decodedJSON = json_decode($output, true);

            // see if we have any html
            if ( $provider === 'flickr' || $provider === 'pinterest' )
            {
                if ( isset($decodedJSON['url']) && $decodedJSON['type'] == 'photo' )
                {
                    $html = '<img src="'.$decodedJSON['url'].'" width="'.$decodedJSON['width'].'" height="'.$decodedJSON['height'].'" class="fetch fetch--'.$provider.'">';
                }
                else
                {
                    return array(
                        'success' => false,
                        'error' => Craft::t("fetch", "Sorry that image didn’t seem to work.")
                    );
                }
            }
            else
            {
                if ( isset($decodedJSON['html']) && ( ctype_space($decodedJSON['html']) === false || $decodedJSON['html'] !== '' ) )
                {
                    $html = '<div class="fetch  fetch--'.$provider.'">'.$decodedJSON['html'].'</div>';
                }
                else
                {
                    return array(
                        'success' => false,
                        'error' => Craft::t("fetch", "Sorry that url didn’t seem to work.")
                    );
                }
            }
            // Instagram mods
            if ( $provider === 'instagram' )
            {
                // Shortcode and media url
                $decodedJSON['shortcode'] = false;

                if (isset($shortcode))
                {
                    $decodedJSON['thumbnail_url'] = "https://instagram.com/p/{$shortcode}/media/";
                    $decodedJSON['shortcode'] = $shortcode;
                }
                else
                {
                    if (!isset($decodedJSON['thumbnail_url']))
                    {
                        $decodedJSON['thumbnail_url'] = false;
                    }
                }

                // Date it was posted
                $decodedJSON['date'] = false;

                if(preg_match("/(datetime\=)(.*)(\")(.*)(\")(.*)/i", $html, $matches))
                {
                    if (isset($matches[4]))
                    {
                        $decodedJSON['date'] = DateTime::createFromString($matches[4]);
                    }
                }
            }

            // Youtube mods
            if ( $provider === 'youtube' )
            {
                // Add youtube ID
                preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?#&\"'>]+)/", $url, $matches);

                if (isset($matches[1])) {
                    $decodedJSON['youtube_id'] = $matches[1];
                }

                // Modify the url in the iframe to add &wmode=transparent
                if (isset($html))
                {
                    $html = preg_replace('/src\=\\"(.*?)\\"(.*?)/i', 'src="$1$2&wmode=transparent"$3', $html);
                    $decodedJSON['html'] = $html;
                }
            }

            // check we haven't any errors or 404 etc
            if ( !isset($html) || strpos($html, '<html') !== false || isset($decodedJSON['errors']) || strpos($html, 'Not Found') !== false )
            {
                // Don’t cache ones that didn’t work
                return [
                    'success' => false,
                    'error' => Craft::t("fetch", "Sorry content for that url couldn’t be found.")
                ];
            }
            else
            {
                $return = [
                    'success'  => true,
                    'url'      => $url,
                    'provider' => $provider,
                    'object'   => $decodedJSON,
                    'html'     => $html,
                    'scripts'  => $scripts
                ];

                // Cache and return
                $cache->set('fetch.'.$url, $return);
                return $return;
            }
        }
    }

}
