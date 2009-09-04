<?php


/**
 *
 * @param string $on_load_js
 * @author fabriceb
 * @since May 27, 2009 fabriceb
 */
function include_bottom_facebook_connect_script($on_load_js = '')
{
  if (sfFacebook::isJsLoaded())
  {
    return;
  }
  ?>
  <script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/<?php echo sfFacebook::getLocale() ?>" type="text/javascript"></script>
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
  sfFacebook::setJsLoaded();
}

/**
 *
 * @author fabriceb
 * @since May 27, 2009 fabriceb
 */
function include_facebook_connect_script()
{
  if (sfFacebook::isJsLoaded())
  {
    return;
  }
  ?>
  <script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/<?php echo sfFacebook::getLocale() ?>" type="text/javascript"></script>
  <script src="/sfFacebookConnectPlugin/js/sfFacebookConnect.js" type="text/javascript"></script>

  <script type="text/javascript">
    //<![CDATA[
    if (typeof sf_fb == 'undefined')
    {
      var sf_fb = new sfFacebookConnect('<?php echo sfConfig::get('app_facebook_api_key') ?>', '<?php echo url_for('sfFacebookConnectAuth/signin') ?>');
    }
    //]]>
  </script>
  <?php
  sfFacebook::setJsLoaded();
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
  // options
  $default_options =
    array(
      'size' => 'medium',
      'bg'   => 'light'
    );
  $options = array_merge($default_options, $options);

  // js arguments
  $js_arguments = array();
  if ($forward)
  {
    $js_arguments['forward'] = "'".rawurlencode($forward)."'";
  }
  if ($callback != '')
  {
    $js_arguments['callback'] = $callback;
  }

  if (sfConfig::get('app_facebook_redirect_after_connect'))
  {
    $js_arguments['redirect'] = 'true';
  }
  // convert to json but do not quote every value 
  $options_array = array();
  foreach($js_arguments as $name => $value)
  {
    if (!is_null($value))
  	{
  	  $options_array[] = $name.': '.$value;
  	}
  }
  $js_arguments = '{'.implode(', ', $options_array).'}';
  
  // html
  $html =
  '
  <a href="#" onclick="sf_fb.requireSession('.$js_arguments.');return false;">'.
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