<?php

pake_desc('Register Facebook feed');
pake_task('facebook-register-feed');

function run_facebook_register_feed($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide an application.');
  }

  $app = $args[0];
  define('SF_ROOT_DIR',    sfConfig::get('sf_root_dir'));
  define('SF_APP',         $app);
  require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');

  $one_line_story_templates = array();
  $one_line_story_templates[] = '{*actor*} va voir le match {*match*} au bar {*bar*} {*date*} !';
  $short_story_templates = array();
  $short_story_templates[] = array(
    'template_title' => '{*actor*} va voir le match {*match*} au bar {*bar*}!',
    'template_body' => '{*actor*} va supporter {*team*} au bar {*bar*} {*date*}.'
  );
  $full_story_template = null;
  $action_links = array();
  $action_links[] = array(
    'text'=>'Trouve des bars qui diffusent le match {*match*}',
    'href'=>'http://www.allomatch.com/{*match-path*}'
  );
  $action_links[] = array(
    'text'=>'Plus d\'infos sur le bar {*bar*}',
    'href'=>'http://www.allomatch.com/{*bar-path*}'
  );
  echo sfFacebook::getFacebookApi()->feed_registerTemplateBundle(
    $one_line_story_templates,
    $short_story_templates,
    null,
    $action_links
  );


}
/*
 *
  $one_line_story_templates = array();
  $one_line_story_templates[] = '{*actor*} va voir le match {*match*} au bar {*bar*} {*date*} !';
  $short_story_templates = array();
  $short_story_templates[] = array(
    'template_title' => '{*actor*} va voir le match {*match*} au bar {*bar*}!',
    'template_body' => '{*actor*} va voir le match {*match*} au bar {*bar*} {*date*}.'
  );
  $full_story_template = null;
  $action_links = array();
  $action_links[] = array(
    'text'=>'Trouve des bars qui diffusent le match {*match*}',
    'href'=>'http://www.allomatch.com/{*match-path*}'
  );
  $action_links[] = array(
    'text'=>'Plus d\'infos sur le bar {*bar*}',
    'href'=>'http://www.allomatch.com/{*bar-path*}'
  );
 */