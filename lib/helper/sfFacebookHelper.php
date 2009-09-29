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
function fb_url_for()
{
  $arguments = func_get_args();
    
  $host = '';
  if (sfFacebook::inCanvas())
  {
    $host = sfConfig::get('app_facebook_app_url'); 
  }

  return $host.call_user_func_array('url_for', $arguments);
}