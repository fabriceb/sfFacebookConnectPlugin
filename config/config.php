<?php

/*
 * Register routing
 */
if (sfConfig::get('app_facebook_connect_load_routing', true))
{
  $this->dispatcher->connect('routing.load_configuration', array('sfFacebookConnectRoutingHelper', 'listenToLoadConfigurationEvent'));
}