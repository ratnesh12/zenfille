<?php
	if (isset($_COOKIE['zen_login_error']))
	{
		$login_error_message = $_COOKIE['zen_login_error'];
		$_COOKIE['zen_login_error']	= '';
		setcookie('zen_login_error', '');
	}

    if($_SERVER['HTTP_HOST'] == 'parkipfiling.com' || $_SERVER['HTTP_HOST'] == 'www.parkipfiling.com' || $_SERVER['HTTP_HOST'] == 'zen') {
        $park_ip = true;
    } else {
        $park_ip = false;
    }

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php if($park_ip) echo 'Park Ip'; else echo 'ZenFile' ?> &rsaquo; Log In</title>
	<link rel="stylesheet" href="login.css" type="text/css" media="all" />
	<meta name='robots' content='noindex,nofollow' />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script type="text/javascript" src="jquery.validate.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$("#loginform").validate({
				rules: {
					username: {
						required: true,
						minlength: 2
					},
					password: {
						required: true,
						minlength: 5
					}
				},
				messages: {
					username: {
						required: "Please enter a username",
						minlength: "Your username must consist of at least 4 characters"
					},
					password: {
						required: "Please provide a password",
						minlength: "Your password must be at least 4 characters long"
					}
				}
			});
		});
	</script>

    <?php if($park_ip) { ?>
        <style type="text/css">
            input.button-primary, button.button-primary, a.button-primary {
                background-color: #4A8434;
                border-color: #4A8434;
                color: #FFFFFF;
                font-weight: bold;
                text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.3);
            }

            .login #nav a, .login #backtoblog a {
                color: #508c33 !important;
            }
        </style>
    <?php } ?>
</head>
<body class="login">
<div id="login">
	<h1><a href="<?php if($park_ip) echo 'http://parkip.com'; else echo 'http://zenfile.com'; ?>"><img src="<?php if($park_ip) { ?>new-logo.png<?php  } else { ?>zenfile-new-blue-logo.png<?php } ?>" title="ZenFile" /></a></h1>
<?php echo $_SERVER['HTTP_HOST'] ?>
<form name="loginform" id="loginform" action="https://<?php echo $_SERVER['HTTP_HOST'] ?>/new_project/client/auth/login/" method="post">
	<?php if ( ! empty($login_error_message)): ?>
		<label class="error"><?php echo $login_error_message ?></label>
	<?php endif ?>
	<p>
		<label>Username<br />
		<input type="text" name="username" id="username" class="input" value="" size="20" tabindex="10" /></label>
	</p>
	<p>
		<label>Password<br />
		<input type="password" name="password" id="password" class="input" value="" size="20" tabindex="20" /></label>
	</p>

	<p class="forgetmenot">
		<!--<label>
			<input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> Remember Me
		</label>-->
	</p>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Log In" tabindex="100" />
	</p>
</form>
<p id="nav">

	<a href="<?php if(!$park_ip) { ?>http://zenfile.com<?php } else { ?>http://parkip.com<?php } ?>" class="left" title="Are you lost?">&larr; Back to <?php if(!$park_ip) { ?>ZenFile<?php } else { ?>ParkIP<?php } ?></a>
	<a href="https://<?=$_SERVER['HTTP_HOST']?>/lostpassword/" class="right" title="Password Lost and Found">Lost your password?</a>
</p>
	<p id="backtoblog">

	</p>
</div>
</body>
</html>