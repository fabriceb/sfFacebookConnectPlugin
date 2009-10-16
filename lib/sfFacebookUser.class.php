<?php

/**
 *
 * @package    sfFacebookConnectPlugin
 * @author     Fabrice Bernhard
 *
 */
class sfFacebookUser extends sfGuardSecurityUser
{

  /**
   *
   * @return integer
   * @author fabriceb
   * @since May 27, 2009 fabriceb
   */
  public function getCurrentFacebookUid()
  {
    $sfGuardUser = $this->getGuardUser();
    if ($sfGuardUser && sfFacebook::getFacebookClient()->get_loggedin_user() == sfFacebook::getGuardAdapter()->getUserFacebookUid($sfGuardUser))
    {
      return sfFacebook::getFacebookClient()->get_loggedin_user();
    }

    return null;
  }

  public function isFacebookConnected()
  {

    return !is_null($this->getCurrentFacebookUid());
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
