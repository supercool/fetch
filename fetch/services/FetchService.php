<?php
namespace Craft;

/**
 * Fetch by Supercool
 *
 * @package   Fetch
 * @author    Josh Angell
 * @copyright Copyright (c) 2014, Supercool Ltd
 * @link      http://www.supercooldesign.co.uk
 */

class FetchService extends BaseApplicationComponent
{

  private $settings = '';


  public function __construct()
  {
    // get plugin settings
    $plugin = craft()->plugins->getPlugin('fetch');
    $this->settings = $plugin->getSettings();
  }


  public function get($url, $scripts = true)
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
      return array(
        'success' => false,
        'error' => Craft::t("Sorry that service isn’t supported yet.")
      );
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
          'error' => Craft::t("Sorry that image didn’t seem to work.")
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
          'error' => Craft::t("Sorry that url didn’t seem to work.")
        );
      }

    }

    // For Instagram add thumbnail_url for all
    if ( $provider === 'instagram' && !isset($decodedJSON['thumbnail_url']) )
    {

      // String query string if there is one
      $thumbUrl = preg_replace('/\?.*/', '', $url);

      // Add the media thumb part
      $thumbUrl .= '/media?size=l';

      // Remove any double slashes
      $thumbUrl = preg_replace('/([^:])(\/{2,})/', '$1/', $thumbUrl);

      // Set it on the thumbnail_url attribute
      $decodedJSON['thumbnail_url'] = $thumbUrl;

    }

    // check we haven't any errors or 404 etc
    if ( !isset($html) || strpos($html, '<html') !== false || isset($decodedJSON['errors']) || strpos($html, 'Not Found') !== false )
    {

      return array(
        'success' => false,
        'error' => Craft::t("Sorry content for that url couldn’t be found.")
      );

    }
    else
    {

      return array(
        'success' => true,
        'url'     => $url,
        'object'  => $decodedJSON,
        'html'    => $html,
        'scripts' => $scripts
      );

    }

  }

}
