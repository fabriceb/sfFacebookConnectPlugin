<?php

/**
 *
 * @package    sfFacebookConnectPlugin
 * @author     Fabrice Bernhard
 *
 */
class sfFacebookPropelGuardAdapter extends sfFacebookGuardAdapter
{

  /**
   * Gets the profile class name connected to the sfGuardUser
   *
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getProfilePeerClassName()
  {

    $profileClass = sfConfig::get('app_sf_guard_plugin_profile_class', 'sfGuardUserProfile');
    if (!class_exists($profileClass))
    {
      throw new sfException(sprintf('The user profile class "%s" does not exist.', $profileClass));
    }
    $profilePeerClass =  $profileClass.'Peer';
    // to avoid php segmentation fault
    class_exists($profilePeerClass);

    return $profilePeerClass;
  }
  /**
   * Gets the foreign key connecting profile and user
   *
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getSfGuardUserforeignKeyColumn()
  {
    $profilePeerClass = $this->getProfilePeerClassName();
    $sfGuardUserfieldName = sfConfig::get('app_sf_guard_plugin_profile_field_name', 'user_id');
    $sfGuardUserforeignKeyColumn = call_user_func_array(array($profilePeerClass, 'translateFieldName'), array($sfGuardUserfieldName, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_COLNAME));
    if (!$sfGuardUserforeignKeyColumn)
    {
      throw new sfException(sprintf('The user profile class "%s" does not contain a "%s" column.', $profileClass, $sfGuardUserfieldName));
    }

    return $sfGuardUserforeignKeyColumn;
  }

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
    $this->setUserProfileProperty($user, 'email_hash',$email_hash);
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
    $this->setUserProfileProperty($user, 'facebook_uid',$facebook_uid);
  }

  /**
   * Sets a property of the profile of the user
   *
   * @param sfGuardUser $user
   * @param string $property_name
   * @param mixed $property
   */
  public function setUserProfileProperty(&$user, $property_name, $property)
  {
    $setPropertyMethod = 'set'.$this->getProfilePhpName($property_name);
    $user->getProfile()->$setPropertyMethod($property);
  }

  /**
   * Gets a property of the profile of the user
   *
   * @param sfGuardUser $user
   * @param string $property_name
   * @return mixed
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getUserProfileProperty($user, $property_name)
  {
    $getPropertyMethod = 'get'.$this->getProfilePhpName($property_name);

    return $user->getProfile()->$getPropertyMethod();
  }



   /**
   * Gets the Php name given to the field
   *
   * @param string $field
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getProfilePhpName($field_name)
  {
    $field_name = $this->getFieldName($field_name);
    $profilePeerClass = $this->getProfilePeerClassName();

    $phpname = call_user_func_array(array($profilePeerClass, 'translateFieldName'), array($field_name, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_PHPNAME));
    if (!$phpname)
    {
      throw new sfException(sprintf('The user profile class "%s" does not contain a "%s" propertyy.', $profileClass, $phpname));
    }

    return $phpname;
  }

  /**
   * Gets the Php name given to the field
   *
   * @param string $field
   * @return string
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getProfileColumnName($field_name)
  {
    $field_name = $this->getFieldName($field_name);
    $profilePeerClass = $this->getProfilePeerClassName();

    $column = call_user_func_array(array($profilePeerClass, 'translateFieldName'), array($field_name, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_COLNAME));
    if (!$column)
    {
      throw new sfException(sprintf('The user profile class "%s" does not contain a "%s" column.', $profileClass, $column));
    }

    return $column;
  }

  /**
   * gets a sfGuardUser using the facebook_uid column of his Profile class
   *
   * @param Integer $facebook_uid
   * @param boolean $isActive
   * @return sfGuardUser
   * @author fabriceb
   * @since 2009-05-17
   */
  public function retrieveSfGuardUserByFacebookUid($facebook_uid, $isActive = true)
  {
    $c = new Criteria();
    $c->addJoin(sfGuardUserPeer::ID,$this->getSfGuardUserforeignKeyColumn());
    $c->add($this->getFacebookUidColumn(),$facebook_uid);
    $c->add(sfGuardUserPeer::IS_ACTIVE, $isActive);

    return sfGuardUserPeer::doSelectOne($c);
  }

  /**
   * gets a sfGuardUser using the facebook_uid column of his Profile class or his email_hash
   *
   * @param Integer $facebook_uid
   * @param boolean $isActive
   * @return sfGuardUser
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getSfGuardUserByFacebookUid($facebook_uid, $isActive = true)
  {
    $sfGuardUser = self::retrieveSfGuardUserByFacebookUid($facebook_uid, $isActive);

    if (!$sfGuardUser instanceof sfGuardUser)
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        sfContext::getInstance()->getLogger()->info('{sfFacebookConnect} No user exists with current facebook_uid');
      }
      $sfGuardUser = sfFacebookConnect::getSfGuardUserByFacebookEmail($facebook_uid, $isActive);
    }

    return $sfGuardUser;
  }

  /**
   * tries to get a sfGuardUser using the facebook email hash
   *
   * @param string[] $email_hashes
   * @param boolean $isActive
   * @return sfGuardUser
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getSfGuardUserByEmailHashes($email_hashes, $isActive = true)
  {
    if (!is_array($email_hashes) || count($email_hashes) == 0)
    {

      return null;
    }

    $c = new Criteria();
    $c->addJoin(sfGuardUserPeer::ID,$this->getSfGuardUserforeignKeyColumn());
    $c->add($this->getEmailHashColumn(), $email_hashes, Criteria::IN);
    $c->add(sfGuardUserPeer::IS_ACTIVE, $isActive);

    // NOTE: if a user has multiple emails on their facebook account,
    // and more than one is registered on the site, then we will
    // only return the first one.
    $sfGuardUser = sfGuardUserPeer::doSelectOne($c);

    return $sfGuardUser;
  }

  /**
   * Creates an empty sfGuardUser with profile field Facebook UID set
   *
   * @param Integer $facebook_uid
   * @return sfGuardUser
   * @author fabriceb
   * @since 2009-08-11
   */
  public function createSfGuardUserWithFacebookUid($facebook_uid)
  {
    $con = Propel::getConnection(sfGuardUserPeer::DATABASE_NAME);

    return parent::createSfGuardUserWithFacebookUidAndCon($facebook_uid, $con);
  }


  /**
   * gets Non Facebook-registered Users
   *
   * @return sfGuardUser[]
   * @author fabriceb
   * @since 2009-05-17
   */
  public function getNonRegisteredUsers()
  {
    $c = new Criteria();
    $c->addJoin(sfGuardUserPeer::ID,$this->getSfGuardUserforeignKeyColumn());
    $c->add($this->getEmailHashColumn(), null, Criteria::ISNULL);

    $sfGuardUsers = sfGuardUserPeer::doSelect($c);

    return $sfGuardUsers;
  }

  /**
  *
  * @param string $cookie
  * @return sfGuardUser
  * @author fabriceb
  * @since Aug 10, 2009
  */
  public function retrieveSfGuardUserByCookie($cookie)
  {
    $c = new Criteria();
    $c->add(sfGuardRememberKeyPeer::REMEMBER_KEY, $cookie);
    $rk = sfGuardRememberKeyPeer::doSelectOne($c);

    if ($rk)
    {

      return $rk->getSfGuardUser();
    }

    return null;
  }
}

