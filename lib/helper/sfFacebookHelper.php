<?php

/**
* @author fabriceb
* @since Sep 29, 2009
*/


/**
 * Add the facebook app url in front
 *
 * @param string $internal_uri
 * @return string
 * @author fabriceb
 * @since 2009-06-03
 */
function fb_url_for($internal_uri)
{
  $host = '';
  if (sfFacebook::inCanvas())
  {
    $host = sfConfig::get('app_facebook_app_url'); 
  }

  return $host.url_for($internal_uri, false);
}