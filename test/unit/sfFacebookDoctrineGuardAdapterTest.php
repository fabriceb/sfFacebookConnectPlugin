<?php
/**
 * Teste la sauvegarde d'équipes dans le backend
 * @author fabriceb
 * @since Feb 16, 2009 fabriceb
 */
include(dirname(__FILE__).'/../bootstrap/unit12.php');
$app='frontend';
include(dirname(__FILE__).'/../bootstrap/functional.php');

$t = new lime_test(4, new lime_output_color());

$t->diag('sfFacebookDoctrineGuardAdapter Tests');


$t->diag('sfFacebookDoctrineGuardAdapter->getSfGuardUserByFacebookUid Test');

$sfGuardUser = new sfGuardUser();
$sfGuardUser->setUsername('test');
sfFacebook::getGuardAdapter()->setUserFacebookUid($sfGuardUser,9999999999);
try
{
  $con = Doctrine::getConnectionByTableName('sfGuardUser');
  $con->beginTransaction();
  $sfGuardUser->save();
  $sfGuardUser->getProfile()->save();
  $con->commit();
  $t->is(sfFacebook::getGuardAdapter()->getSfGuardUserByFacebookUid(9999999999)->getUsername(),'test');
}
catch (Exception $e)
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
  $con = Doctrine::getConnectionByTableName('sfGuardUser');
  $con->beginTransaction();

  $sfGuardUser->save();


  $sfGuardUser->getProfile()->save();
  $con->commit();
  $t->is(sfFacebook::getGuardAdapter()->getSfGuardUserByEmailHashes(array('trucmuche',sfFacebookConnect::getEmailHash('fabriceb@theodo.fr')))->getUsername(),'test');
}
catch (Exception $e)
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

$permission = new sfGuardPermission();
$permission->setName('member');
$permission->save();

sfConfig::set('app_facebook_connect_user_permissions',array('member'));
$sfGuardUser = new sfGuardUser();
$sfGuardUser->setUsername('test');
$sfGuardUser->save();
sfFacebook::getGuardAdapter()->setDefaultPermissions($sfGuardUser);
$t->is($sfGuardUser->getPermissionNames(),array('member'));
$sfGuardUser->delete();
$permission->delete();
