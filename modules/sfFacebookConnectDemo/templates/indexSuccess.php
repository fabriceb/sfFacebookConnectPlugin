<?php use_helper('sfFacebookConnect'); ?>
<?php 
  slot('fb_connect');
  include_facebook_connect_script();
  end_slot();
?>

<h1>
  Welcome <fb:name uid="<?php echo $sf_user->getCurrentFacebookUid() ?>" useyou="false" ></fb:name> !
  <fb:profile-pic uid="<?php echo $sf_user->getCurrentFacebookUid() ?>" linked="true" ></fb:profile-pic>
</h1>

<br />
<br />

<div>
  <?php if ($sf_user->isAuthenticated()): ?>
    Tu es connecté et ton login est : <?php echo $sf_user->getGuardUser()->getUsername() ?>
  <?php else: ?>
    Tu n'es pas connecté... Connecte toi avec Facebook connect !
    <?php echo facebook_connect_button(); ?>
  <?php endif; ?>
</div>




<!-- just before body in layout to avoid problems in IE -->
<?php if (has_slot('fb_connect')): ?>
  <?php include_slot('fb_connect') ?>
<?php endif; ?>
