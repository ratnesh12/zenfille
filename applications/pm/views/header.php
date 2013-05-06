<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
    <title><?php echo (isset($page_name)) ? $page_name : ''; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="<?php echo base_url(); ?>assets/css/facebox.css" media="screen" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/facebox.js"></script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.tooltip.js"></script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/default.js"></script>
</head>
<script type="text/javascript">
    var BASE_URL    =   "<?php echo $this->config->item('base_url'); site_url()?>";
</script>