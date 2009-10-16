<?php

/**
 *
 * @author fabriceb
 * @package    sfFacebookConnectPlugin
 * @since Aug 24, 2009
 *
 */
class sfFacebookApplicationFilter extends sfFilter
{

  public function execute ($filterChain)
  {
    if ($this->isFirstCall() && !$this->getContext()->getUser()->isAuthenticated())
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        sfContext::getInstance()->getLogger()->info('{sfFacebookApplicationFilter} Trying to authenticate the current user');
      }
      $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession();
      if ($sfGuardUser)
      {
        $this->getContext()->getUser()->signIn($sfGuardUser, true);
        $this->getContext()->getUser()->setCurrentFacebookUid(sfFacebookGuardAdapter::getUserProfileProperty($sfGuardUser,'facebook_uid'));
      }
      else
      {
        if (sfConfig::get('sf_logging_enabled'))
        {
          sfContext::getInstance()->getLogger()->info('{sfFacebookApplicationFilter} No user found');
        }
      }
    }

    $filterChain->execute();
  }
}
