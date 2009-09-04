<?php

pake_desc('Register Facebook users for later linking');
pake_task('facebook-register-users');

function run_facebook_register_users($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide an application.');
  }

  $app = $args[0];
  define('SF_ROOT_DIR',    sfConfig::get('sf_root_dir'));
  define('SF_APP',         $app);
  require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');

  sfContext::getInstance();
  $sfGuardUsers = sfFacebookConnect::getNonRegisteredUsers();
  echo count($sfGuardUsers)." non registered users in your database\n";
  $chunks = array_chunk($sfGuardUsers,50);
  $num_registered = 0;
  foreach($chunks as $chunk)
  {
    $num_registered += sfFacebookConnect::registerUsers($chunk);
    echo $num_registered." registered.\n";
  }


}
