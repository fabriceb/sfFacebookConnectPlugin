<?php

/**
 *
 * @package    sfFacebookConnectPlugin
 * @author     Fabrice Bernhard
 *
 */
abstract class sfFacebookGuardAdapter
{

   /**
   * Gets the name of the column with the facebook Uid
   *
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getFacebookUidColumn()
  {

    return $this->getProfileColumnName('facebook_uid');
  }

   /**
   * Gets the name of the column with the email
   *
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getEmailColumn()
  {

    return $this->getProfileColumnName('email');
  }

  /**
   * Gets the name of the column with the email hash
   *
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getEmailHashColumn()
  {

    return $this->getProfileColumnName('email_hash');
  }

  /**
   * gets the profile email
   *
   * @param sfGuardUser $user
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getUserEmail(sfGuardUser $sfGuardUser)
  {

    return $this->getUserProfileProperty($sfGuardUser,'email');
  }

  /**
   * sets the profile email hash
   *
   * @param sfGuardUser $user
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  public function setUserEmailHash(&$user, $email_hash)
  {
    $this->setUserProfileProperty($user, 'email_hash', $email_hash);
  }

  /**
   * Gets the facebook uid of the user
   *
   * @param sfGuardUser $user
   * @return integer $facebook_uid
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getUserFacebookUid($user)
  {
    return $this->getUserProfileProperty($user, 'facebook_uid');
  }

  /**
   * Sets the facebook uid of the user
   *
   * @param sfGuardUser $user
   * @param integer $facebook_uid
   * @author fabriceb
   * @since 2009-05-17
   */
  public function setUserFacebookUid(&$user, $facebook_uid)
  {
    $this->setUserProfileProperty($user, 'facebook_uid', $facebook_uid);
  }

  /**
  *
  * @param sfGuardUser $user
  * @return boolean
  * @author fabriceb
  * @since Aug 25, 2009
  */
  public function isFacebookConnected($user)
  {

    return $this->getUserFacebookUid($user) != '';
  }

  /**
   * Sets a property of the profile of the user
   *
   * @param sfGuardUser $user
   * @param string $property_name
   * @param mixed $property
   */
  abstract function setUserProfileProperty(&$user, $property_name, $property);

  /**
   * Gets a property of the profile of the user
   *
   * @param sfGuardUser $user
   * @param string $property_name
   * @return mixed
   * @author fabriceb
   * @since 2009-05-17
   */
  abstract function getUserProfileProperty($user, $property_name);

  /**
   * Gets the name given to the field, if customized by the user
   *
   * @param string $field
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getFieldName($field_name)
  {

    return sfConfig::get('app_sf_guard_plugin_profile_'.$field_name.'_name', $field_name);
  }

   /**
   * Gets the Php name given to the field
   *
   * @param string $field
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  abstract function getProfilePhpName($field_name);

  /**
   * Gets the Php name given to the field
   *
   * @param string $field
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  abstract function getProfileColumnName($field_name);

  /**
   * gets a sfGuardUser using the facebook_uid column of his Profile class
   *
   * @param Integer $facebook_uid
   * @return sfGuardUser
   * @author fabriceb
   * @since 2009-05-17
   */
  abstract function retrieveSfGuardUserByFacebookUid($facebook_uid);
  
  /**
   * gets a sfGuardUser using the facebook_uid column of his Profile class or his email_hash
   *
   * @param Integer $facebook_uid
   * @return sfGuardUser
   * @author fabriceb
   * @since 2009-05-17
   */
  abstract function getSfGuardUserByFacebookUid($facebook_uid);

  /**
   * tries to get a sfGuardUser using the facebook email hash
   *
   * @param string[] $email_hashes
   * @return sfGuardUser
   * @author fabriceb
   * @since 2009-05-17
   */
  abstract function getSfGuardUserByEmailHashes($email_hashes);

  /**
   * Creates an empty sfGuardUser with profile field Facebook UID set
   *
   * @param Integer $facebook_uid
   * @return sfGuardUser
   * @author fabriceb
   * @since 2009-05-17
   * @since 2009-08-11 ORM-agnostic version
   */
  public function createSfGuardUserWithFacebookUidAndCon($facebook_uid, $con)
  {
    $sfGuardUser = new sfGuardUser();
    $sfGuardUser->setUsername('Facebook_'.$facebook_uid);
    $this->setUserFacebookUid($sfGuardUser, $facebook_uid);
    sfFacebookConnect::newSfGuardConnectionHook(&$sfGuardUser, $facebook_uid);

    // Save them into the database using a transaction to ensure a Facebook sfGuardUser cannot be stored without its facebook uid
    try
    {
      if (method_exists($con,'begin'))
      {
        $con->begin();
      }
      else
      {
        $con->beginTransaction();
      }
      $sfGuardUser->save();
      $sfGuardUser->getProfile()->save();
      $con->commit();
    }
    catch (Exception $e)
    {
      $con->rollback();
      throw $e;
    }
    $this->setDefaultPermissions($sfGuardUser);

    return $sfGuardUser;
  }

  /**
   *
   * @param sfGuardUser $sf_guard_user
   * @return sfGuardUser
   * @author fabriceb
   * @since May 22, 2009 fabriceb
   */
  public function setDefaultPermissions(sfGuardUser $sf_guard_user)
  {
    if (!$sf_guard_user->getId())
    {
      throw new sfException('To add permissions, user must already be in database');
    }
    $permissions = sfConfig::get('app_facebook_connect_user_permissions', array());
    foreach($permissions as $permission)
    {
      $sf_guard_user->addPermissionByName($permission);
    }

    return $sf_guard_user;
  }

  /**
   * gets Non Facebook-registered Users
   *
   * @return sfGuardUser[]
   * @author fabriceb
   * @since 2009-05-17
   */
  abstract function getNonRegisteredUsers();

  /**
  *
  * @param string $cookie
  * @return sfGuardUser
  * @author fabriceb
  * @since Aug 10, 2009
  */
  abstract function retrieveSfGuardUserByCookie($cookie);
}

