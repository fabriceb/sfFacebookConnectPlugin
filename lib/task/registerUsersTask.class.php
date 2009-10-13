<?php
/**
 * Fetch your non registered users and register their email_hashes and ids to facebook
 * 
 * @see http://wiki.developers.facebook.com/index.php/Linking_Accounts_and_Finding_Friends
 * @author dalexandre
 * @since  2009/01/09
 */
class facebookRegisterUsersTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace        = 'facebook';
    $this->name             = 'register-users';
    $this->briefDescription = 'Register Facebook users for later linking';
    $this->detailedDescription = <<<EOF
The [register-users|INFO] register your sfGuardUser Facebook account for later linking.
Check [http://wiki.developers.facebook.com/index.php/Linking_Accounts_and_Finding_Friends|INFO  ] for more informations about this tool utilities.
Call it with:

  [php symfony facebook:register-users|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);

    $sfGuardUsers = sfFacebook::getGuardAdapter()->getNonRegisteredUsers();
    
    $this->logSection('info', count($sfGuardUsers)." non registered users in your database");

    $chunks = array_chunk($sfGuardUsers, 50);
    $num_registered = 0;
    foreach($chunks as $chunk)
    {
      // Call to facebook API
      $num_registered += sfFacebookConnect::registerUsers($chunk);
      $this->logSection('do', $num_registered." registered.");
    }
  }
}
