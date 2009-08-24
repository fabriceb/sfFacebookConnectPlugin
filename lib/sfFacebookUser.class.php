<?php

/**
 * 
 * @author fabriceb
 *
 */
class sfFacebookUser extends sfGuardSecurityUser
{
  protected $currentFacebookUid = false;
  
  /**
   * 
   * @return integer
   * @author fabriceb
   * @since May 27, 2009 fabriceb
   */
  public function getCurrentFacebookUid()
  {
    if ($this->currentFacebookUid===false)
    {
      $this->currentFacebookUid = sfFacebook::getFacebookClient()->get_loggedin_user();
    }
    
    return $this->currentFacebookUid;
  }
  
  /**
   * 
   * @param integer $facebook_uid
   * @author fabriceb
   * @since May 27, 2009 fabriceb
   */
  public function setCurrentFacebookUid($facebook_uid)
  {
    $this->currentFacebookUid = $facebook_uid;
  }
  
  public function isFacebookConnected()
  {
    
    return ($this->getCurrentFacebookUid() != null);
  }
  
  public function signOut()
  {
    $this->setCurrentFacebookUid(false);
    parent::signOut();
  }
  
  /**
   * Gets information about the user
   *
   * @param array $fields
   * @return array
   */
  public function getInfos($fields)
  {
    $users_infos = sfFacebook::getFacebookApi()->users_getInfo(array($this->getCurrentFacebookUid()),$fields);
    
    return reset($users_infos);
  }
  
}
