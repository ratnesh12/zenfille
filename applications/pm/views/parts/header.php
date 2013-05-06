<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<noscript><meta http-equiv="Refresh" content="0; URL=<?php echo base_url(); ?>errors/noscript"></noscript>
        <title><?php echo (isset($page_name)) ? $page_name : ''; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <script type="text/javascript">
            var BASE_URL    =   "<?php echo $this->config->item('base_url'); site_url()?>";
        </script>
	<link href="<?php echo base_url(); ?>assets/css/skin.css?v=<?php echo get_file_modification_time('assets/css/skin.css')?>" media="screen" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url(); ?>assets/css/facebox.css" media="screen" rel="stylesheet" type="text/css" />
	<link href="<?php echo base_url(); ?>assets/css/ajax-uploader.css" media="screen" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.7.1.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/jquery-ui.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/facebox.js"></script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.tooltip.js"></script>
	<!--<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/default.js"></script>-->
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ajaxupload.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/global.zenfile.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {
			var selected_menu = "<?php echo (isset($selected_menu)) ? $selected_menu : ''; ?>";
			if (selected_menu != "") {
				$("#"+selected_menu).addClass("selected");
			}

			$(".arrow").hover(
				function() {
					$("ul#projects-menu").fadeIn(100);
				},
				function() {
					$("ul#projects-menu").fadeOut(100);
				}
			);
		});
	</script>
</head>
    <img width="0" height="0" src="<?= base_url()?>assets/img/stan_popup/message_box.png">
    <img width="0" height="0" src="<?= base_url()?>assets/img/stan_popup/i.png">
    <img width="0" height="0" src="<?= base_url()?>assets/img/stan_popup/x.png">
    <div class="stan_layout"></div>
        <div class="stan_message_box_container">
            <table align="center">
                <tr>
                    <td>
                        <div class="message_box">
                            <div class="info_part"></div>
                            <div class="info_close" onClick="stanOverlay.HIDE()"></div>
                            <div class="info_center">
                                <div class="info_Information"></div>
                                <div class="info_message"></div>
                            </div>

                        </div>
                    </td>
                </tr>
            </table>
        </div>
    <div class="gear_background hidden"></div>
		<div class="loader hidden"></div>
		<div class="gear hidden"></div>
<div id="header">
	<div id="top-links-box">
		<div id="logout-box">
			<p>
				Welcome Back <?php echo $this -> session -> userdata('manager_firstname'); ?>&nbsp;<?php echo $this -> session -> userdata('manager_lastname');?>&nbsp;|&nbsp;
				<?php echo anchor('/profile/', 'Profile'); ?>
				&nbsp;|&nbsp;
                <a href="https://<?php echo $_SERVER['HTTP_HOST'];  ?>/client/auth/logout">Logout</a>
			</p>
		</div>
	</div>
</div>
<div id="subheader">

</div>
<div id="wrapper">
	<div id="menu-box">
		<div id="logo">
			<a href="<?php echo base_url(); ?>dashboard/">
				<img src="<?php echo base_url(); ?>assets/images/skin/zen-logo.png" title="Zen File" />
			</a>
		</div>
		<div id="menu">
            <?php if($this->session->userdata('type') == 'admin'){?>
            <div class="item" id="cases">
                <a href="<?php echo base_url(); ?>admin/">
                    <br/>
                    <span class="icon"><img src="<?php echo base_url(); ?>assets/images/navigation/search.gif" /></span>
                    <span class="label">Cases</span>
                </a>
            </div>
            <div class="item" id="users">
                <a href="<?php echo base_url(); ?>users/">
                    <br/>
                    <span class="icon"><img src="<?php echo base_url(); ?>assets/images/navigation/search.gif" /></span>
                    <span class="label">Users</span>
                </a>
            </div>
            <div class="item" id="ip-addresses">
                <a href="<?php echo base_url(); ?>ip/">
                    <br/>
                    <span class="icon"><img src="<?php echo base_url(); ?>assets/images/navigation/search.gif" /></span>
                    <span class="label">White IP list</span>
                </a>
            </div>
            <?php }else{?>
			<div class="item" id="dashboard">
				<p>
				<a href="<?php echo base_url(); ?>dashboard/">
					<br/>
					<span class="icon"><img src="<?php echo base_url(); ?>assets/images/navigation/dashboard.gif" /></span>
					<span class="label">Dashboard</span>
				</a>
				</p>
			</div>
			<div class="item" id="clients">
				<a href="<?php echo base_url(); ?>clients/">
					<br/>
					<span class="icon"><img src="<?php echo base_url(); ?>assets/images/navigation/search.gif" /></span>
					<span class="label">Clients</span>
				</a>
			</div>
			<div class="item" id="emails">
				<a href="<?php echo base_url(); ?>emails/">
					<br/>
					<span class="icon"><img src="<?php echo base_url(); ?>assets/images/navigation/starred.gif" /></span>
					<span class="label">Emails</span>
				</a>
			</div>
			<div class="item" id="park_fees">
				<a  href="<?php echo base_url(); ?>park_fees/">
					<br/>
					<span class="icon"><img src="<?php echo base_url(); ?>assets/images/navigation/park_fees.png" /></span>
					<span class="label">Park Fees</span>
				</a>
			</div>
			<div class="item" id="fees">
				<a  href="<?php echo base_url(); ?>fees/">
					<br/>
					<span class="icon"><img src="<?php echo base_url(); ?>assets/images/navigation/currencies.png" /></span>
					<span class="label">Fees</span>
				</a>
			</div>
			<div class="item" id="associates">
				<a href="<?php echo base_url(); ?>associates/">
					<br/>
					<span class="icon"><img src="<?php echo base_url(); ?>assets/images/navigation/associates.png" /></span>
					<span class="label">Associates</span>
				</a>
			</div>
			<div class="item" id="countries">
				<a href="<?php echo base_url(); ?>countries/">
					<br/>
					<span class="icon"><img src="<?php echo base_url(); ?>assets/images/navigation/countries.png" /></span>
					<span class="label">Countries</span>
				</a>
			</div>
            <?php }?>
		</div>
	</div>
	<div id="subheader">
		<p><?php echo (isset($subheader_message)) ? $subheader_message : ''; ?></p>
	</div>
	<div id="breadcrumb">
	</div>
	<div id="notification-box"></div>

	<div id="content">