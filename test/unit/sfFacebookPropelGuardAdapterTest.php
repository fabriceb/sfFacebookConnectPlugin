<?php
/**
 * Teste la sauvegarde d'équipes dans le backend
 * @author fabriceb
 * @since Feb 16, 2009 fabriceb
 */
include(dirname(__FILE__).'/../bootstrap/unit10.php');
//$app='frontend';
//include(dirname(__FILE__).'/../bootstrap/functional.php');

$sfGuardUser = sfGuardUserPeer::retrieveByUsername('test', false);
if ($sfGuardUser)
{
  $sfGuardUser->delete();
}
$sfGuardUser = sfGuardUserPeer::retrieveByUsername('Facebook_9999999999', false);
if ($sfGuardUser)
{
  $sfGuardUser->delete();
}

$t = new lime_test(5, new lime_output_color());

$t->diag('sfFacebookPropelGuardAdapter Tests');


$t->diag('sfFacebook::getGuardAdapter()->getProfilePeerClassName Test');

$t->is(sfFacebook::getGuardAdapter()->getProfilePeerClassName(),sfConfig::get('app_sf_guard_plugin_profile_class').'Peer');


$t->diag('sfFacebook::getGuardAdapter()->getSfGuardUserByFacebookUid Test');

$sfGuardUser = new sfGuardUser();
$sfGuardUser->setUsername('test');
sfFacebook::getGuardAdapter()->setUserFacebookUid($sfGuardUser,9999999999);
try
{
  $con = Propel::getConnection(sfGuardUserPeer::DATABASE_NAME);
  $con->begin();
  $sfGuardUser->save();
  $sfGuardUser->getProfile()->save();
  $con->commit();
  $t->is(sfFacebook::getGuardAdapter()->getSfGuardUserByFacebookUid(9999999999)->getUsername(),'test');
}
catch (PropelException $e)
{
  $con->rollback();
  throw $e;
}
$sfGuardUser->delete();


$t->diag('sfFacebook::getGuardAdapter()->getSfGuardUserByEmailHashes Test');

$sfGuardUser = new sfGuardUser();
$sfGuardUser->setUsername('test');
sfFacebook::getGuardAdapter()->setUserProfileProperty($sfGuardUser,'email_hash',sfFacebookConnect::getEmailHash('fabriceb@theodo.fr'));
try
{
  $con = Propel::getConnection(sfGuardUserPeer::DATABASE_NAME);
  $con->begin();
  $sfGuardUser->save();
  $sfGuardUser->getProfile()->save();
  $con->commit();
  $t->is(sfFacebook::getGuardAdapter()->getSfGuardUserByEmailHashes(array('trucmuche',sfFacebookConnect::getEmailHash('fabriceb@theodo.fr')))->getUsername(),'test');
}
catch (PropelException $e)
{
  $con->rollback();
  throw $e;
}
$sfGuardUser->delete();




$t->diag('sfFacebook::getGuardAdapter()->createSfGuardUserWithFacebookUid Test');

sfFacebook::getGuardAdapter()->createSfGuardUserWithFacebookUid(9999999999);
$sfGuardUser = sfFacebook::getGuardAdapter()->getSfGuardUserByFacebookUid(9999999999);
$t->is($sfGuardUser->getUsername(),'Facebook_9999999999');
$sfGuardUser->delete();


$t->diag('sfFacebook::getGuardAdapter()->setDefaultPermissions Test');

sfConfig::set('app_facebook_connect_user_permissions',array('member'));
$sfGuardUser = new sfGuardUser();
$sfGuardUser->setUsername('test');
$sfGuardUser->save();
sfFacebook::getGuardAdapter()->setDefaultPermissions($sfGuardUser);
$t->is($sfGuardUser->getPermissionNames(),array('member'));
$sfGuardUser->delete();