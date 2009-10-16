<?php

/** 
 * 
 * Based on sfGuardRememberMeFilter
 *
 *  Place this filter before the security filter in filters.yml e.g.
 *  ...
 *  facebook_connect:
 *    class: sfFacebookConnectFilter
 *  security: ~
 *  ...
 *
 * @package    sfFacebookConnectPlugin
 * @author     fabriceb
 */
class sfFacebookConnectFilter extends sfFilter
{

  /**
   * @see sfFilter
   */
  public function execute($filterChain)
  {
    $cookieName = sfConfig::get('app_sf_guard_plugin_remember_cookie_name', 'sfRemember');

    if (
      $this->isFirstCall()
      &&
      $this->context->getUser()->isAnonymous()
      &&
      $cookie = $this->context->getRequest()->getCookie($cookieName)
    )
    {
      /*
      $q = Doctrine_Query::create()
            ->from('sfGuardRememberKey r')
            ->innerJoin('r.sfGuardUser u')
            ->where('r.remember_key = ?', $cookie);
      */
      $sfGuardUser = sfFacebook::getGuardAdapter()->retrieveSfGuardUserByCookie($cookie);

      if ($sfGuardUser)
      {
        $this->getContext()->getUser()->signIn($sfGuardUser, true);
        $fb_sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession();
        if ($fb_sfGuardUser && $fb_sfGuardUser->getId() == $sfGuardUser->getId())
        {
          $this->getContext()->getUser()->setCurrentFacebookUid(sfFacebookGuardAdapter::getUserProfileProperty($sfGuardUser,'facebook_uid'));
        }
      }
    }

    $filterChain->execute();
  }
}
