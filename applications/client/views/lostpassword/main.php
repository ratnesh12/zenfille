<?php
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
	<title><?php echo $this->config->item('title_of_the_site') ?> &rsaquo; Lost Password</title>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/lostpassword.css" type="text/css" media="all" />
<meta name='robots' content='noindex,nofollow' />
    <?php if ($park_ip) { ?>
        <style type="text/css">
            #wp-submit {
                background-color: #4A8434;
                border-color: #4A8434;
            }
            #login FORM {
                margin-left: 60px;
            }
        </style>
    <?php } ?>
</head>
<body class="login">
<div id="login">
	<h1 style="text-align:center;padding-bottom: 15px;"><a href="<?php if($park_ip) echo 'http://parkip.com'; else echo 'http://zenfile.com'; ?>"><img src="https://<?=$_SERVER['HTTP_HOST']?>/login/<?php if($park_ip) { ?>new-logo.png<?php  } else { ?>zenfile-new-blue-logo.png<?php } ?>" title="<?php echo $this->config->item('title_of_the_site') ?>" /></a></h1>

<form name="loginform" id="loginform" action="https://<?=$_SERVER['HTTP_HOST']?>/client/lostpassword/submit/" method="post">
    <label style="color:#BC1D10;" class="error"><?php echo validation_errors(); ?></label>
	<p>
		<label>Enter your Username<br />
		<input type="text" name="log" id="user_login" class="input" value="" size="20" tabindex="10" /></label>
	</p>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Reset" tabindex="100" />
	</p>
</form>
</div>
</body>
</html>