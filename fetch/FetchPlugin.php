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

class FetchPlugin extends BasePlugin
{

  public function getName()
  {
    return Craft::t('Fetch');
  }

  public function getVersion()
  {
    return '1.2';
  }

  public function getDeveloper()
  {
    return 'Supercool';
  }

  public function getDeveloperUrl()
  {
    return 'http://www.supercooldesign.co.uk';
  }

  protected function defineSettings()
  {
    return array(
      'embedlyApiKey' => array(AttributeType::String)
    );
  }

  public function getSettingsHtml()
  {
    return craft()->templates->render('fetch/settings', array(
      'settings' => $this->getSettings()
    ));
  }

}
