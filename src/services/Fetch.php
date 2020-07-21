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
use supercool\fetch\fields\FetchField;
use yii\base\Component;

use supercool\fetch\Fetch as FetchPlugin;

class Fetch extends Component
{
    public static $PROVIDER_FLICKR = 2;
    public static $PROVIDER_INSTAGRAM = 3;
    public static $PROVIDER_SOUNDCLOUD = 4;
    public static $PROVIDER_TWITTER = 5;
    public static $PROVIDER_VIMEO = 6;
    public static $PROVIDER_YOUTUBE = 7;
    public static $PROVIDER_DISABLED_FOR_FIELD = 500;
    public static $PROVIDER_UNSUPPORTED = 1000;

    /**
     * Fetch the embed for a given url value
     *
     * @param $url
     * @param FetchField $field
     * @param bool $scripts
     * @return array|bool|null
     */
    public function get($url, FetchField $field, $scripts = true)
    {
        if (!$url) {
            return null;
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

            $provider = $this->getProvider($url, $field);

            if($provider === $this::$PROVIDER_DISABLED_FOR_FIELD) {
                return $this->getErrorResponse(
                    Craft::t("fetch", "Sorry, that service has been disabled for this field"),
                    $url,
                    $scripts
                );
            }

            if($provider === $this::$PROVIDER_UNSUPPORTED) {
                return $this->getErrorResponse(
                    Craft::t("fetch", "Sorry that service isn’t supported yet."),
                    $url,
                    $scripts
                );
            }

            try {
                $data = $this->getOembedResponse($url, $provider, $scripts);
            }
            catch (\Throwable $e) {

                Craft::error($e->getMessage(), FetchPlugin::$plugin->handle);

                return $this->getErrorResponse(
                    Craft::t("fetch", "There was an issue getting a response from the provider."),
                    $url,
                    $scripts
                );
            }

            // see if we have any html
            if ( $provider === $this::$PROVIDER_FLICKR)
            {
                if ( isset($data['url']) && $data['type'] == 'photo' )
                {
                    $html = '<img alt="' . $data['title'] . ' (Flickr image)"' .
                        ' src="' . $data['url'] .
                        '" width="' . $data['width'] .
                        '" height="' . $data['height'] .
                        '" class="fetch fetch--'.$this->getProviderString($provider).'">';
                }
                else
                {
                    return $this->getErrorResponse(
                        Craft::t("fetch", "Sorry that image didn’t seem to work."),
                        $url,
                        $scripts);
                }
            }
            else
            {
                if ( isset($data['html']) && ( ctype_space($data['html']) === false || $data['html'] !== '' ) )
                {
                    $html = '<div class="fetch  fetch--'.$this->getProviderString($provider).'">'.$data['html'].'</div>';

                    $html = preg_replace(
                        '/(?:<iframe([^>]*)>)/',
                        '<iframe title="' . $this->getProviderString($provider) . ' embed" $1 >',
                        $html,
                        1
                    );
                }
                else
                {
                    return $this->getErrorResponse(
                        Craft::t("fetch", "Sorry that url didn’t seem to work."),
                        $url,
                        $scripts
                    );
                }
            }
            // Instagram mods
            if ( $provider === $this::$PROVIDER_INSTAGRAM )
            {
                // Shortcode and media url
                $data['shortcode'] = false;

                // Try and parse out the shortcode
                preg_match("/(https?:)?\/\/(.*\.)?instagr(\.am|am\.com)\/p\/([^\/]*)/i", $url, $matches);
                if (isset($matches[4]))
                {
                    $data['thumbnail_url'] = "https://instagram.com/p/{$matches[4]}/media/";
                    $data['shortcode'] = $matches[4];
                }
                else if (!isset($data['thumbnail_url']))
                {
                    $data['thumbnail_url'] = false;
                }

                // Date it was posted
                $data['date'] = false;

                preg_match("/(datetime=)(.*)(\")(.*)(\")(.*)/i", $html, $matches);
                if (isset($matches[4]))
                {
                    $data['date'] = \DateTime::createFromFormat(DATE_ATOM,$matches[4]);
                }

            }

            // Youtube mods
            if ( $provider === $this::$PROVIDER_YOUTUBE )
            {
                // Add youtube ID
                preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?#&\"'>]+)/", $url, $matches);

                if (isset($matches[1])) {
                    $data['youtube_id'] = $matches[1];
                }

                // Modify the url in the iframe to add &wmode=transparent
                if (isset($html))
                {
                    $html = preg_replace('/src\=\\"(.*?)\\"(.*?)/i', 'src="$1$2&wmode=transparent"$3', $html);
                    $data['html'] = $html;
                }
            }

            // check we haven't any errors or 404 etc
            if ( !isset($html) || strpos($html, '<html') !== false || isset($data['errors']) || strpos($html, 'Not Found') !== false )
            {
                // Don’t cache ones that didn’t work
                return $this->getErrorResponse(
                    Craft::t("fetch", "Sorry content for that url couldn’t be found."),
                    $url,
                    $scripts
                );
            }
            else
            {
                $return = [
                    'success'  => true,
                    'url'      => $url,
                    'provider' => $this->getProviderString($provider),
                    'object'   => $data,
                    'html'     => $html,
                    'scripts'  => $scripts
                ];

                // Cache and return
                $cache->set('fetch.'.$url, $return);
                return $return;
            }
        }
    }

    /**
     * Works out the provider of a given url, and returns a constant appropriate for it.
     * Can also return two 'error' const's, one for the provider being disabled in the field settings
     * and one for the provider being unknown
     *
     * @param string $url
     * @param FetchField $field
     * @return int
     */
    protected function getProvider(string $url, FetchField $field) {

        if ( strpos($url, 'vimeo') !== false )
        {
            $provider = $field->allowVimeo ? $this::$PROVIDER_VIMEO : $this::$PROVIDER_DISABLED_FOR_FIELD;
        }
        elseif ( strpos($url, 'twitter') !== false )
        {
            $provider = $field->allowTwitter ? $this::$PROVIDER_TWITTER : $this::$PROVIDER_DISABLED_FOR_FIELD;
        }
        elseif ( strpos($url, 'youtu') !== false )
        {
            $provider = $field->allowYoutube ? $this::$PROVIDER_YOUTUBE : $this::$PROVIDER_DISABLED_FOR_FIELD;
        }
        elseif ( strpos($url, 'flickr') !== false )
        {
            $provider = $field->allowFlickr ? $this::$PROVIDER_FLICKR : $this::$PROVIDER_DISABLED_FOR_FIELD;
        }
        elseif ( strpos($url, 'soundcloud') !== false )
        {
            $provider = $field->allowSoundcloud ? $this::$PROVIDER_SOUNDCLOUD : $this::$PROVIDER_DISABLED_FOR_FIELD;
        }
        elseif ( strpos($url, 'instagr') !== false )
        {
            $provider = $field->allowInstagram ?  $this::$PROVIDER_INSTAGRAM : $this::$PROVIDER_DISABLED_FOR_FIELD;
        }
        else
        {
            $provider = $this::$PROVIDER_UNSUPPORTED;
        }

        return $provider;
    }

    /**
     * Gets the data from oembed for the passed in url and provider, returning data as an associative array
     *
     * @param $url
     * @param $provider
     * @param $includeScripts
     * @return mixed
     * @throws \Exception
     */
    protected function getOembedResponse(&$url, $provider, $includeScripts)
    {
        switch ($provider) {
            case $this::$PROVIDER_FLICKR:
                $apiUrl = 'https://www.flickr.com/services/oembed?url='.$url.'&format=json';
                break;

            case $this::$PROVIDER_INSTAGRAM:
                // Try and parse out the shortcode
                if (preg_match("/(https?:)?\/\/(.*\.)?instagr(\.am|am\.com)\/p\/([^\/]*)/i", $url, $matches))
                {
                    if (isset($matches[4]))
                    {
                        $url = "https://www.instagram.com/p/{$matches[4]}/";
                    }
                }

                $apiUrl = 'https://api.instagram.com/oembed/?url='.$url;

                break;

            case $this::$PROVIDER_SOUNDCLOUD:
                $apiUrl = 'https://soundcloud.com/oembed?url='.$url.'&format=json';
                break;

            case $this::$PROVIDER_TWITTER:
                if ($includeScripts) {
                    $apiUrl = 'https://api.twitter.com/1/statuses/oembed.json?url='.$url;
                } else {
                    $apiUrl = 'https://api.twitter.com/1/statuses/oembed.json?url='.$url.'&omit_script=true';
                }

                break;

            case $this::$PROVIDER_VIMEO:
                $apiUrl = 'https://vimeo.com/api/oembed.json?url=' .
                    $url . '&byline=false&title=false&portrait=false&autoplay=false';

                break;

            case $this::$PROVIDER_YOUTUBE:
                $apiUrl = 'https://www.youtube.com/oembed?url='.$url.'&format=json';
                break;

            default:
                // This should never be reached, as we are using constants,
                // and the only non-cased constant will have already returned an error.
                throw new \Exception("Invalid provider in fetch field: " . $provider);
        }

        return $this->getApiData($apiUrl);
    }

    /**
     * @param $provider - Constant format of providers
     * @return string
     */
    protected function getProviderString($provider)
    {
        switch ($provider) {
            case $this::$PROVIDER_FLICKR:
                return "flickr";
                break;

            case $this::$PROVIDER_INSTAGRAM:
                return "instagram";

                break;

            case $this::$PROVIDER_SOUNDCLOUD:
                return "soundcloud";
                break;

            case $this::$PROVIDER_TWITTER:
                return "twitter";

                break;

            case $this::$PROVIDER_VIMEO:
                return "vimeo";

                break;

            case $this::$PROVIDER_YOUTUBE:
                return "youtube";
                break;

            default:
                // This should never be reached, as we are using constants,
                // and the only non-cased constant will have already returned an error.
                return "";
                break;
        }
    }

    /**
     * Makes a curl request to the provided api url, and returns the response (json decoded)
     *
     * @param string $apiUrl
     * @return mixed
     */
    protected function getApiData(string $apiUrl) {
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
        return json_decode($output, true);
    }

    /**
     * Gets the array to return on error, to ensure consistent format across all error returns
     *
     * @param string $errorMessage
     * @param string $url
     * @param bool $scripts
     * @return array
     */
    protected function getErrorResponse(string $errorMessage, string $url, bool $scripts)
    {
        return [
            'success' => false,
            'error' => $errorMessage,
            'url' => $url,
            'provider' => null,
            'object' => null,
            'html' => '',
            'scripts' => $scripts
        ];
    }

}
