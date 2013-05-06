<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>ZenFile &rsaquo; Log In</title>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/login.css" type="text/css" media="all" />
<meta name='robots' content='noindex,nofollow' />
</head>
<body class="login">
<div id="login">
	<h1 align="center"><a href="http://zenfile.com/"><img src="<?php echo base_url()?>assets/images/skin/small-logo.png"/></a></h1>

<form name="loginform" id="loginform" action="http://zenfile.com/client/auth/login/" method="post">

	<p>
		<label>Username<br />
		<input type="text" name="username" id="user_login" class="input" value="" size="20" tabindex="10" /></label>
	</p>
	<p>
		<label>Password<br />
		<input type="password" name="password" id="user_pass" class="input" value="" size="20" tabindex="20" /></label>
	</p>

	<p class="forgetmenot">
	</p>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Log In" tabindex="100" />		
	</p>
</form>
<p id="nav">
	<a href="<?php echo base_url();?>lostpassword/" title="Password Lost and Found">Lost your password?</a>
</p>
	<p id="backtoblog">
		<a href="http://zenfile.com" title="Are you lost?">&larr; Back to ZenFile</a>
	</p>
</div>
</body>
</html>