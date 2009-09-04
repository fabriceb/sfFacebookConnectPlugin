sfFacebookConnect = function(api_key, signin_url, redirect_url)
{
  this.xd_receiver_path = "/sfFacebookConnectPlugin/xd_receiver.htm";
  this.api_key = api_key;
  this.signin_url = signin_url;
  this.redirect_url = redirect_url;
  this.callback = '';
  this.forward = '';
  
  this.init();
};
sfFacebookConnect.prototype.init = function()
{
  FB.init(this.api_key,this.xd_receiver_path);
}
sfFacebookConnect.prototype.getSigninUrl = function(redirect)
{
  t_signin_url = this.signin_url + '?';
  if(this.forward != undefined && this.forward != '')
  {
    t_signin_url += '&forward=' + this.forward; 
  }
  if (redirect != undefined && redirect == true)
  {
    t_signin_url += '&redirect=' + redirect;
  }
  
  return t_signin_url;
}
sfFacebookConnect.prototype.gotoLoginPage = function(redirect)
{
  document.location.href= this.getSigninUrl(redirect);
};
sfFacebookConnect.prototype.gotoLoginOrRedirectPage = function()
{
  document.location.href= this.getSigninOrRedirectUrl();
};
/**
 * @param options
 * {
 *   forward: url to forward to after successful signin
 *   callback: the js function to execute after Facebook Connection
 *   redirect: url to redirect to if Facebook Connection is successful but sfGuardUser account does not exist
 * }
 */
sfFacebookConnect.prototype.requireSession = function(options)
{
  this.forward = options.forward;
  console.log(this.forward);
  console.log(options.callback);
  console.log(options.redirect);
  if (options.callback==undefined )
  {
    if (options.redirect==undefined)
    {
	  var current_obj = this;
	  options.callback = function(){current_obj.gotoLoginPage()};
    }
    else
    {
      var current_obj = this;
      options.callback = function(){current_obj.gotoLoginPage(true)};
    }
  }
  FB.Connect.requireSession(options.callback);
};

/*
 * Show the feed form. This would be typically called in response to the
 * onclick handler of a "Publish" button, or in the onload event after
 * the user submits a form with info that should be published.
 *
 */
sfFacebookConnect.prototype.publishFeedStory = function(form_bundle_id, template_data)
{
  // Load the feed form
  FB.ensureInit(
    function()
    {
      FB.Connect.showFeedDialog(form_bundle_id, template_data);
      //FB.Connect.showFeedDialog(form_bundle_id, template_data, null, null, FB.FeedStorySize.shortStory, FB.RequireConnect.promptConnect);
      
      // hide the "Loading feed story ..." div
      //ge('feed_loading').style.visibility = "hidden";
    }
  );
};