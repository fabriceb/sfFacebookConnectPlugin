<?php

class facebookgeneratefeedTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      // add your own options here
    ));

    $this->namespace        = 'facebook';
    $this->name             = 'generate-feed';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [facebook:generate-feed|INFO] task does things.
Call it with:

  [php symfony facebook:generate-feed|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $one_line_story_templates = array();
    $one_line_story_templates[] = '{*actor*} got a {*score*} score on his <a href="http://apps.facebook.com/symfony-quiz">Symfony quiz</a> ! Try to beat {*actor*} : take the <a href="http://apps.facebook.com/symfony-quiz">quiz</a> !';
    $short_story_templates = array();
    $short_story_templates[] = array(
      'template_title' => '{*actor*} got a {*score*} score on his <a href="http://apps.facebook.com/symfony-quiz">Symfony quiz</a> !',
      'template_body' => 'Test your symfony skills, know how good you are and try to beat {*actor*} : take the <a href="http://apps.facebook.com/symfony-quiz">quiz</a> !'
    );
    $full_story_template = null;
    $action_links = array();
    $action_links[] = array(
      'text'=>'Take the quiz directly',
      'href'=>'http://apps.facebook.com/symfony-quiz/questions/createTest'
    );
    $action_links[] = array(
      'text'=>'Go to the quiz homepage',
      'href'=>'http://apps.facebook.com/symfony-quiz'
    );
    echo sfFacebook::getFacebookApi()->feed_registerTemplateBundle(
      $one_line_story_templates,
      $short_story_templates,
      null,
      $action_links
    );
  }
}
