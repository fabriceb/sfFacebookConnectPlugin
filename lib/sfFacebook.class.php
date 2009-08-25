<?php

/**
 *
 * @package    sfFacebookConnectPlugin
 * @author     Fabrice Bernhard
 *
 */
class sfFacebook
{
  protected static
    $client          = null;
  protected static
    $guard_adapter   = null;
    
  
  /**
   * gets the facebook client instance
   *
   * @return Facebook
   * @author fabriceb
   * @since 2009-05-17
   */
  public static function getFacebookClient()
  {
    if (self::$client === null)
    {
      self::$client = new Facebook(self::getApiKey(), self::getApiSecret());
    }

    if (!self::$client)
    {
      error_log('Could not create facebook client.');
    }
    
    return self::$client;
  }
  
  /**
   *
   * @return FacebookRestClient
   * @author fabriceb
   * @since 2009-06-10
   */
  public static function getFacebookApi()
  {
  
    return self::getFacebookClient()->api_client;
  }

   /**
   * gets the facebook api key
   *
   * @return Facebook
   * @author fabriceb
   * @since 2009-05-17
   */
  public static function getApiKey()
  {
    
    return sfConfig::get('app_facebook_api_key');
  }
  
   /**
   * gets the facebook api secret
   *
   * @return Facebook
   * @author fabriceb
   * @since 2009-05-17
   */  
  public static function getApiSecret()
  {
    
    return sfConfig::get('app_facebook_api_secret');
  }
  

  
  /**
   * gets or create user with facebook uid inprofile
   *
   * @param Integer $facebook_uid
   * @return sfGuardUser $sfGuardUser
   */
  public static function getOrCreateUserByFacebookUid($facebook_uid)
  {
    $sfGuardUser = self::getGuardAdapter()->getSfGuardUserByFacebookUid($facebook_uid);
    if (!$sfGuardUser instanceof sfGuardUser)
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        sfContext::getInstance()->getLogger()->info('{sfFacebookConnect} No user exists with current facebook_uid');
      }
      $sfGuardUser = sfFacebookConnect::getSfGuardUserByFacebookEmail($facebook_uid);
    }
    if (!$sfGuardUser instanceof sfGuardUser)
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        sfContext::getInstance()->getLogger()->info('{sfFacebookConnect} No user exists with current email hash');
      }      
      $sfGuardUser = self::getGuardAdapter()->createSfGuardUserWithFacebookUid($facebook_uid);
    }
    
    return $sfGuardUser;
  }
  
  /**
   * Gets the currently logged sfGuardUser using Facebook Session
   *
   * @return sfGuardUser
   * @author fabriceb
   * @since 2009-05-17
   * @since 2009-08-25 
   */
  public static function getSfGuardUserByFacebookSession()
  {
    // We get the facebook uid from session
    $fb_uid = self::getFacebookClient()->get_loggedin_user();
    if ($fb_uid)
    {
      
      return self::getOrCreateUserByFacebookUid($fb_uid);
    }
    
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->info('{sfFacebookConnect} No current Facebook session');
    }
    
    return null;
  }
  
  /**
   * checks the existence of the HTTP_X_FB_USER_REMOTE_ADDR porperty in the header
   * which is a sign of being included by the fbml interface
   * 
   * @return boolean
   * @author fabriceb
   * @since Jun 8, 2009 fabriceb
   */
  public static function isInsideFacebook()
  {
    
    return isset($_SERVER['HTTP_X_FB_USER_REMOTE_ADDR']);
  }
  
  /**
   * 
   * @return boolean
   * @author fabriceb
   * @since Jun 8, 2009 fabriceb
   */
  public static function inCanvas()
  {
    
    return self::getFacebookClient()->in_fb_canvas();
  }
  
  /**
   * redirects to the login page of the Facebook application if not logged yet
   * 
   * @author fabriceb
   * @since Jun 8, 2009 fabriceb
   */
  public static function requireLogin()
  {
    self::getFacebookClient()->require_login();
  }
  
  /**
   * redirects depnding on in canvas or not
   * 
   * @param $url
   * @param $statusCode
   * @return mixed sfView::NONE or sfStopException
   * @author fabriceb
   * @since Jun 8, 2009 fabriceb
   */
  public static function redirect($url, $statusCode = 302)
  {
    if (self::inCanvas())
    {
      $url = sfContext::getInstance()->getController()->genUrl($url, false);
      $url = sfConfig::get('app_facebook_app_url').$url;
      $text = '<fb:redirect url="' . $url . '"/>';
      
      sfContext::getInstance()->getResponse()->setContent(sfContext::getInstance()->getResponse()->getContent().$text);
      
      return sfView::NONE;
    }    
    sfContext::getInstance()->getController()->redirect($url, 0, $statusCode);

    throw new sfStopException();
  }
  
  /**
   * 
   * @param integer $user_uid
   * @return integer[]
   * @author fabriceb
   * @since Jun 9, 2009 fabriceb
   */
  public static function getFacebookFriendsUids($user_uid = null)
  {
    
    try
    {
      $friends_uids = self::getFacebookApi()->friends_get(null, $user_uid);
    }
    catch(FacebookRestClientException $e)
    {
      $friends_uids = array();
      if (sfConfig::get('sf_logging_enabled'))
      {
        sfContext::getInstance()->getLogger()->info('{FacebookRestClientException} '.$e->getMessage());
      }
    }
  
    return $friends_uids; 
  }
  
  /**
  *
  * @return sfFacebookGuardAdapter
  * @author fabriceb
  * @since Aug 10, 2009
  */
  public static function getGuardAdapter()
  {    
    
    
  
    if (self::$guard_adapter === null)
    {
      if (class_exists('sfGuardUserPeer', true))
      {
        self::$guard_adapter = new sfFacebookPropelGuardAdapter();
      }
      else
      {
        self::$guard_adapter = new sfFacebookDoctrineGuardAdapter();
      }
    }

    if (!self::$guard_adapter)
    {
      error_log('Could not create guard adapter.');
    }
    
    return self::$guard_adapter;
  }
  
}