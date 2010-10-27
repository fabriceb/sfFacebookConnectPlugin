<?php

/**
 *
 * Based on sfGuardRememberMeFilter
 *
 *  Place this filter before the security filter in filters.yml e.g.
 *  ...
 *  facebook_connect:
 *    class: sfFacebookAutoConnectFilter
 *  security: ~
 *  ...
 *
 * This filter will auto-connect you looking at your Facebook session.
 *
 * @package    sfFacebookConnectPlugin
 * @author     fabriceb
 */
class sfFacebookAutoConnectFilter extends sfFilter
{

  /**
   * @see sfFilter
   */
  public function execute($filterChain)
  {
    if ($this->isFirstCall() && $this->context->getUser()->isAnonymous())
    {
      $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession();

      if ($sfGuardUser)
      {
        $this->getContext()->getUser()->signIn($sfGuardUser, true);
      }
    }

    $filterChain->execute();
  }
}
