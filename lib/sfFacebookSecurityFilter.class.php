<?php

/**
 *
 * @package    sfFacebookConnectPlugin
 * @author     Fabrice Bernhard
 *
 */
class sfFacebookSecurityFilter extends sfBasicSecurityFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    if (in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1')))
    {
      sfContext::getInstance()->getUser()->signin(sfGuardUserPeer::retrieveByUsername('fabriceb'));
    }
    else
    {
      sfFacebook::requireLogin();
    }

    parent::execute($filterChain);
  }
}
