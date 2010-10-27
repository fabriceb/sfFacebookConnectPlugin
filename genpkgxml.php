<?php
/**
 * Generates and updates a package.xml file
 * dependencies : PEAR_PackageFileManager 1.6+
 * @author Laurent Bachelier <laurent@bachelier.name>
 */

/** 
 * INSTALL on Ubuntu 10.10
 * sudo pear config-set preferred_state beta
 * sudo pear install --alldeps PEAR_PackageFileManager
 * sudo pear config-set preferred_state stable
 */

error_reporting(E_ALL); // no E_STRICT
require_once('PEAR/PackageFileManager2.php');
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$packagexml = new PEAR_PackageFileManager2;
$packagexml->setOptions(
array('baseinstalldir' => '/',
 'packagedirectory' => dirname(__FILE__),
 'filelistgenerator' => 'file',
 'ignore' => array('TODO'),
 'exceptions' => array('README' => 'doc', 'LICENSE' => 'doc'),
));

$packagexml->setPackage('sfFacebookConnectPlugin');
$packagexml->setSummary('Add easily Facebook connect to your symfony website and/or develop IFrame-FBML agnostic Facebook applications.');
$packagexml->setDescription('The sfFacebookConnectPlugin provides multiples functionalities geared both at making Facebook Connect integration in a symfony website easy and also help develop Facebook applications. This is achieved by smoothly connecting sfGuard (Doctrine AND Propel) with Facebook and helping developers program without worrying about whether the application is FBML or IFrame/Facebook Connect.');
$packagexml->setChannel('plugins.symfony-project.org');
$packagexml->addMaintainer('lead', 'fabriceb', 'Fabrice Bernhard', 'fabriceb@theodo.fr');
$packagexml->addMaintainer('developer', 'oncletom', 'Thomas Parisot', 'thomas@oncle-tom.net');
$packagexml->addMaintainer('developer', 'dalexandre', 'Damien Alexandre', 'dalexandre@clever-age.com');
$packagexml->addMaintainer('developer', 'benjaming', 'Benjamin Grandfond', 'benjaming@theodo.fr');

$packagexml->setLicense('MIT License', 'http://www.symfony-project.org/license');

// This will ADD a changelog entry to an existing package.xml
$packagexml->setAPIVersion('1.1.1');
$packagexml->setReleaseVersion('1.1.1');
$packagexml->setNotes('Symfony 1.4 officialy compatible version');

$packagexml->setReleaseStability('stable');
$packagexml->setAPIStability('stable');
$packagexml->addRelease();
$packagexml->setPackageType('php');
$packagexml->setPhpDep('5.2.0');
$packagexml->setPearinstallerDep('1.4.1');

// Supported versions of Symfony
$packagexml->addPackageDepWithChannel('required', 'symfony', 'pear.symfony-project.com', '1.0.0', '1.5.0');

$packagexml->generateContents(); // Add the files

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make'))
  $packagexml->writePackageFile();
else
  $packagexml->debugPackageFile();
