<?php


class BasesfFacebookConnectAuthActions extends sfActions
{

   /**
   * Sign in with the Facebook account, ajax
   * @author fabriceb
   * @since 2009-05-17
   *
   */
  public function executeAjaxSignin()
  {
    $this->setLayout(false);
    $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession();
    if ($sfGuardUser)
    {
      $this->getContext()->getUser()->signIn($sfGuardUser);
      $this->getResponse()->setHeaderOnly(true);

      return sfView::NONE;
    }

    $this->getResponse()->setHeaderOnly(true);
    $this->getResponse()->setStatusCode(401);

    return sfView::NONE;
  }

   /**
   * Sign in with the Facebook account
   * @author fabriceb
   * @since 2009-05-17
   *
   */
  public function executeSignin()
  {
    $create_automatically = !sfConfig::get('app_facebook_redirect_after_connect', false);
    $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession($create_automatically);

    $user = $this->getUser();
    if ($sfGuardUser)
    {
      $this->getContext()->getUser()->signIn($sfGuardUser);


      $referer = $user->getAttribute('referer', $this->getRequest()->getReferer());
      $user->getAttributeHolder()->remove('referer');

      $signin_url = sfConfig::get('app_sf_guard_plugin_success_signin_url', $referer);

      $forward = $this->getRequestParameter('forward');

      $signin_url = $forward != '' ? $forward : $signin_url;

      $this->redirect('' != $signin_url ? $signin_url : '@homepage');
    }
    // check if user forgot to activate the account
    $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession($create_automatically, false);
    if (!$sfGuardUser)
    {
      if ($this->getRequest()->isXmlHttpRequest())
      {
        $this->getResponse()->setHeaderOnly(true);
        $this->getResponse()->setStatusCode(401);
  
        return sfView::NONE;
      }
  
      if (!$user->hasAttribute('referer'))
      {
        $user->setAttribute('referer', $this->getRequest()->getUri());
      }
      
      $redirect_url = sfConfig::get('app_facebook_redirect_after_connect_url');
      if (!$redirect_url)
      {
        $redirect_url = sfConfig::get('sf_login_module').'/'.sfConfig::get('sf_login_action');
      }
  
      return $this->redirect($redirect_url);
    }
    
    // TODO: What if the guy did not activate his account ?
  }
  

}
