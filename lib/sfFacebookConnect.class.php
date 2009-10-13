<?php

/**
 *
 * @package    sfFacebookConnectPlugin
 * @author     Fabrice Bernhard
 *
 */
class sfFacebookConnect
{




  /**
   * If a Facebook user already has an account with this site, then
   * their email hash will be returned.
   *
   * This only works if the site has called facebook.connect.registerUsers
   * on the same email before
   * @param integer $fb_uid
   * @return string[]
   * @since 2009-05-17 fabriceb addapted to current class
   */
  public static function getFacebookUserEmailHashes($fb_uid)
  {
    $query = 'SELECT email_hashes FROM user WHERE uid=\''.intval($fb_uid).'\'';
    try
    {
      $rows = sfFacebook::getFacebookClient()->api_client->fql_query($query);
    }
    catch (Exception $e)
    {
      // probably an expired session
      return null;
    }

    if (is_array($rows) && (count($rows) == 1) && is_array($rows[0]['email_hashes']))
    {

      return $rows[0]['email_hashes'];
    }

    return null;
  }

  /**
   * tries to get a sfGuardUser using the facebook email hash
   *
   * @param Integer $facebook_uid
   * @param boolean $isActive
   * @return sfGuardUser
   * @author fabriceb
   * @since 2009-05-17
   */
  public static function getSfGuardUserByFacebookEmail($facebook_uid, $isActive = true)
  {
    try
    {
      sfFacebook::getGuardAdapter()->getEmailHashColumn();
    }
    catch (Exception $e)
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        sfContext::getInstance()->getLogger()->info('{sfFacebookConnect} No email_hash column for this user');
      }

      return null;
    }
    $email_hashes = self::getFacebookUserEmailHashes($facebook_uid);
    $sfGuardUser = sfFacebook::getGuardAdapter()->getSfGuardUserByEmailHashes($email_hashes, $isActive);
    if ($sfGuardUser)
    {
      // Since we looked up by email_hash, save the fb_uid
      // so we can look up directly next time
      sfFacebook::getGuardAdapter()->setUserFacebookUid($sfGuardUser, $facebook_uid);
      self::newSfGuardConnectionHook(&$sfGuardUser, $facebook_uid);
      $sfGuardUser->getProfile()->save();
    }

    return $sfGuardUser;
  }

  /**
   *
   * @param $sfGuardUser
   * @param $facebook_uid
   * @return unknown_type
   * @author fabriceb
   * @since Sep 1, 2009
   */
  public static function newSfGuardConnectionHook($sfGuardUser, $facebook_uid)
  {
    if (class_exists('sfEvent'))
    {
      sfContext::getInstance()->getEventDispatcher()->notify(
          new sfEvent(
            $sfGuardUser,
            'sf_guard.user.facebook.create',
            array('facebook_uid' => $facebook_uid)
          )
      );
    }
    else
    {
      foreach (sfMixer::getCallables('sfFacebookConnect:newSfGuardConnection:preSave') as $callable)
      {
        call_user_func($callable, &$sfGuardUser, $facebook_uid);
      }
    }
  }







  /**
  * Returns the "public" hash of the email address, i.e., the one we give out
  * to select partners via our API.
  *
  * @param  string $email An email address to hash
  * @return string        A public hash of the form crc32($email)_md5($email)
  * @since 2009-05-17 fabriceb cleaned the function
  */
  public static function getEmailHash($email)
  {
    if ($email != null)
    {
      $email = trim(strtolower($email));

      return sprintf("%u", crc32($email)) . '_' . md5($email);
    }

    return '';
  }

  /**
   * Register new accounts with Facebook to facilitate friend linking.
   * Note: this is an optional step, and only makes sense if you have
   * a site with an existing userbase that you want to tie into
   * Facebook Connect.
   *
   * See http://wiki.developers.facebook.com/index.php/Friend_Linking
   * for more details.
   *
   * @param $sfGuardUsers  array of sfGuardUsers
   * @return integer the number of users registered
   * @since 2009-05-17 fabriceb cleaned the function and adapted to sfGuardUser
   */
  public static function registerUsers($sfGuardUsers)
  {
    $accounts = array();
    $hashed_users = array();
    foreach($sfGuardUsers as $sfGuardUser)
    {
      $email = sfFacebook::getGuardAdapter()->getUserEmail($sfGuardUser);
      $email_hash = self::getEmailHash($email);
      if ($email_hash != '')
      {
        array_push($accounts,
          array(
            'account_id' => $sfGuardUser->getId(),
            'email_hash' => $email_hash
          )
        );
        $hashed_users[$email_hash] = $sfGuardUser;
      }
    }
    if (count($accounts)==0)
    {

      return 0;
    }
    $facebook = sfFacebook::getFacebookClient();
    $session_key = $facebook->api_client->session_key;
    $facebook->api_client->session_key = null;

    $result = false;
    try
    {
      $ret = $facebook->api_client->call_method(
               'facebook.connect.registerUsers',
               array('accounts' => json_encode($accounts)));

      // On success, return the set of email hashes registered
      // An email hash will be registered even if the email does not match a Facebook account
      $result = count($ret);
      foreach($ret as $email_hash)
      {
        sfFacebook::getGuardAdapter()->setUserEmailHash($hashed_users[$email_hash],$email_hash);
        $hashed_users[$email_hash]->getProfile()->save();
      }
    }
    catch (Exception $e)
    {
      error_log("Exception thrown while calling facebook.connect.registerUsers: ".$e->getMessage());
    }
    $facebook->api_client->session_key = $session_key;

    return $result;
  }



}