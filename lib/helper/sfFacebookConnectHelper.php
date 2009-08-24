<?php

/**
 * 
 * @param string $on_load_js
 * @author fabriceb
 * @since May 27, 2009 fabriceb
 */
function include_bottom_facebook_connect_script($on_load_js = '')
{
  ?>
  <script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script>
  <script src="/sfFacebookConnectPlugin/js/sfFacebookConnect.js" type="text/javascript"></script>
  
  <script type="text/javascript">
    //<![CDATA[
    var sf_fb = null;
    window.onload = function()
    {
      sf_fb = new sfFacebookConnect('<?php echo sfConfig::get('app_facebook_api_key') ?>', '<?php echo url_for('sfFacebookConnectAuth/signin') ?>');
      <?php echo $on_load_js ?>
    }
    //]]>
  </script>
  <?php  
}

/**
 * 
 * @author fabriceb
 * @since May 27, 2009 fabriceb
 */
function include_facebook_connect_script()
{
  ?>
  <script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script>
  <script src="/sfFacebookConnectPlugin/js/sfFacebookConnect.js" type="text/javascript"></script>
  
  <script type="text/javascript">
    //<![CDATA[
    var sf_fb = new sfFacebookConnect('<?php echo sfConfig::get('app_facebook_api_key') ?>', '<?php echo url_for('sfFacebookConnectAuth/signin') ?>');
    //]]>
  </script>
  <?php
}


/**
 * 
 * @param string $forward a url to forward to
 * @return string
 * @author fabriceb
 * @since May 22, 2009 fabriceb
 */
function facebook_connect_button($forward = '', $callback = '', $options = array())
{
  $default_options =
    array(
      'size' => 'medium',
      'bg'   => 'light'
    );
  $options = array_merge($default_options, $options);
  
  $js_arguments = array("'".rawurlencode($forward)."'");
  if ($callback != '')
  {
    array_push($js_arguments,$callback);
  }
  
  $html = 
  '
  <a href="#" onclick="sf_fb.requireSession('.implode(',',$js_arguments).');return false;">'.
    image_tag(
      '/sfFacebookConnectPlugin/images/fb_'.$options['bg'].'_'.$options['size'].'_short.gif',
      array(
        'id' => 'fb_login_image',
        'alt' => 'Facebook Connect'
      )
    ).
  '</a>';
    
  return $html;
}