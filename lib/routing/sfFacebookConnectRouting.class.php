<?php
/**
 * Routing event listener
 *
 * It's usefull to deal with restrictive routing configuration (with no `:module/:action` route enabled)
 *
 * @author tparisot
 * @package sfFacebookConnectPlugin
 * @subpackage routing
 */
class sfFacebookConnectRoutingHelper
{
  /**
   * Load routes
   *
   * @static
   * @author tparisot
   * @param sfEvent $event
   */
  public static function listenToLoadConfigurationEvent(sfEvent $event)
  {
    $routing = $event->getSubject();

    /*
     * Signin
     */
    $routing->prependRoute('sf_facebook_connect_signin', new sfRoute('/fb-connect/signin', array(
      'module' => 'sfFacebookConnectAuth',
      'action' => 'signin',
    )));

    /*
     * Ajax Signin
     */
    $routing->prependRoute('sf_facebook_connect_ajax_signin', new sfRoute('/fb-connect/ajax-signin', array(
      'module' => 'sfFacebookConnectAuth',
      'action' => 'ajaxSignin',
    )));
  }
}