<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"  xml:lang="en" lang="en" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>

<?php include_http_metas() ?>
<?php include_metas() ?>

<?php include_title() ?>

<link rel="shortcut icon" href="/favicon.ico" />

</head>
<body>
<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script>

<?php echo $sf_data->getRaw('sf_content') ?>



<?php echo javascript_tag("
  FB.XFBML.Host.autoParseDomTree = false;
  var sf_fb = new sfFacebookConnect(".sfConfig::get('app_facebook_api_key').");
") ?>


</body>
</html>
