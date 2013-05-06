<?php
$globalStyles = array(
    // libraries css
    'reset.css' => 'reset.css',
    'jquery.selectbox.css' => 'jquery.selectbox.css',
    'msgBoxLight.css' => 'msgBoxLight.css',
    'fileuploader.css' => 'fileuploader.css',
    'facebox.css' => 'facebox.css',

    // zenfile css
    'global.zenfile.css' => 'global.zenfile.css',
    'form.zenfile.css' => 'form.zenfile.css',
    'flags.zenfile.css' => 'flags.zenfile.css',
    'files.zenfile.css' => 'files.zenfile.css',
    'datepicker.zenfile.css' => 'datepicker.zenfile.css',
    'autocomplete.zenfile.css' => 'autocomplete.zenfile.css',
    'ezmark_checkbox.zenfile.css' => 'ezmark_checkbox.zenfile.css',
    'template.zenfile.css' => 'template.zenfile.css',
    'styles.zenfile.css' => 'styles.zenfile.css'
);
add_assets($globalStyles,'global');


$globalScripts = array(
    // libraries js
    'jquery.js' => 'jquery.js',
    'jquery-ui.js' => 'jquery-ui.js',
    'jquery.scrollto.js' => 'jquery.scrollto.js',
    'jquery.tmpl.js' => 'jquery.tmpl.js',
    'jquery.cookie.js' => 'jquery.cookie.js',
    'jquery.selectbox.js' => 'jquery.selectbox.js',
    'jquery.placeholder.js' => 'jquery.placeholder.js',
    'jquery.validate.js' => 'jquery.validate.js',
	'jquery.tooltip.js' => 'jquery.tooltip.js',
    'jquery.msgBox.js' => 'jquery.msgBox.js',
    'jquery.ezmark.min.checkbox.js' => 'jquery.ezmark.min.checkbox.js',
    'jquery.detector.js' => 'jquery.detector.js',
    'facebox.js' => 'facebox.js',


    // zenfile js
    'functions.zenfile.js' => 'functions.zenfile.js',
    'global.zenfile.js' => 'global.zenfile.js',
);
add_assets($globalScripts,'global');


?>
<!DOCTYPE HTML>
<html>
<head>

    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/msgBoxLight.css" />

    <noscript><meta http-equiv="Refresh" content="0; URL=<?=site_url('errors/noscript')?>"></noscript>
    <title><?=(isset($page_name)) ? $page_name : ''?></title>
    <meta charset="utf-8" />
    <!--[if IE 9]>
    <?php /* <meta http-equiv="X-UA-Compatible" value="IE=8" /> */ ?>
    <![endif]-->
    <!--[if lt IE 9]>
    <link rel="stylesheet" href="<?=site_url('assets/css/style_ie.zenfile.css')?>" />
    <![endif]-->
    <!--[if IE 7]>
    <link rel="stylesheet" href="<?=site_url('assets/css/style_ie7.zenfile.css')?>" />
    <![endif]-->
    <?=get_styles('global')?>
    <?=get_styles('page')?>
    <?=$this->assets->get_assets('raw', array(), 'css', true)?>

    <script type="text/javascript">
        var base_url = '<?=site_url('/')?>';
                        var base_url_pm = "<?php echo $this->config->item("base_url_pm")?>";
        var Zenfile = { ajaxurl: '' };
        var title_of_the_site = '<?php echo $this->config->item('title_of_the_site') ?>';

    </script>
    <?=get_scripts('global')?>
    <?=get_scripts('page')?>
    <?=$this->assets->get_assets('raw', array(), 'js', true);?>
    <?=$this->notify->initJs(true)?>
    <script type="text/javascript">

    function makeTooltip(object){
		<?php if(!$this -> session -> userdata('client_tooltips_disable')){?>
			object.tooltip({
	    		track: true,
	    		delay: 0,
	    		showURL: false,
	    		fade: 200
			});
		<?php }else{?>
			object.removeClass('my-tooltip').attr('title','');
		<?php }?>
	}
    $( document ).ready(function(){
    	<?php if(!$this -> session -> userdata('client_tooltips_disable')){?>
	    	$('.my-tooltip').tooltip({
	    		track: true,
	    		delay: 0,
	    		showURL: false,
	    		fade: 200
	    	});
    	<?php }else{?>
			$('.my-tooltip').removeClass('my-tooltip').attr('title','');
		<?php }?>



    });

    function get_selected_flags() {
        $('#flag_block').children('div').addClass('disabled');
        $('input[name=is_ref_enabled]').val(0);

        $('.flags.selected input[name=countries\\[\\]]').each(function(key , value) {
            var country_id = $(value).val();
            $('.reference_country_id_' + country_id).removeClass('disabled');



        });
    }

    function change_flags_reference_numbers() {
        var reference_number = $('input[name=reference_number]').val();

        $('.reference_numbers').val(reference_number);
    }

    $(document).ready(function(){
        $(".table_for_sort").tablesorter();

        $(".table_for_sort").bind("sortStart",function() {
            //do your magic here
        }).bind("sortEnd",function() {
                $('.table_for_sort tbody tr:even').removeClass('even').removeClass('odd').addClass('even');
                $('.table_for_sort tbody tr').not(':even').removeClass('even').removeClass('odd').addClass('odd');
        });
    });



    </script>
    <style>
        .table tr th {
            cursor: pointer;
        }
    </style>

    <?php if (!empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'parkipfiling.com' || $_SERVER['HTTP_HOST'] == 'www.parkipfiling.com' || $_SERVER['HTTP_HOST'] == 'zen')  { ?>

    <style type="text/css">
        .centr_1 {
            background: url(/client/assets_park/img/header_background_top.png) no-repeat center 50%;
        }

        .body {
            background-color: #204303;

        }

        .frame {
            background: none repeat scroll 0 0 #449900;
        }

        .header {
            background: none repeat scroll 0 0 #1a3b01;
        }

        .header .welcome {
            background: none repeat scroll 0 0 #1a3b01;
        }

        h3 {
            color: #165b1f;
        }

        .applicationForm a, .applicationForm a:hover, .applicationForm a:focus, .applicationForm a:active {
            color: #2fa822;
        }

        .table thead th {
            background-color: #73A717;
        }

        #case-info .table a {
            color: #338300 !important;
        }

        #pm_name , #bdv_name {
            color: #338300 !important;
        }

        body #tooltip  {
            background-color: #84e733;
        }

        .table {
            color: #444444;
        }

        .table a {
            color: #338300 !important;
        }

        #tooltip {
            background-color: #0088E2;
        }

        #files table a {
            color: #444444 !important;
        }

        .files tbody tr.odd td, .files tbody tr.odd th {
            background: none repeat scroll 0 0 #99d68e;
            border-top: 1px solid #0C417C;
        }

        .card {
            border: 1px solid #005F8F;
            color: #508c33;
        }

        h6 {
            color: #165b1f;

        }

        .applicationForm .p label {
            color: #165b1f;

        }

        .applicationForm {
            color: #165b1f;
        }

        .file-info {
            color: #508c33;
        }

        input:focus, textarea:focus, .selectBox-dropdown:focus {
            border: 1px solid #508c33;

            box-shadow: 0 0 5px #508c33;
        }


        div.expand {
            color: #296620;
        }
    </style>
    <?php } ?>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/case.zenfile.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url() ?>../pm/assets/css/tablesorter.css" />
    <script type="text/javascript" src="<?php echo base_url() ?>../pm/assets/js/jquery.tablesorter.min.js"></script>
</head>
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

<body class="body">
<img class="background-image" src="<?php echo $this->config->item("base_url");?>assets/img/body_background.png" >
<div class="gear_background hidden"></div>
<div class="loader hidden"></div>
<div class="gear hidden"></div>

<?php if($this -> session -> userdata('client_firstname')){
    $welcome_name = $this -> session -> userdata('client_firstname').'&nbsp'. $this -> session -> userdata('client_lastname');
}elseif($this->session->userdata('fa_type')){
    $welcome_name = $this->session->userdata('fa_name');
}else{
    $welcome_name = '';
}?>
<div class="header">
    <div class="in_header">
        <div class="welcome">
            Welcome back <?php echo $welcome_name;?>
            &nbsp;|&nbsp; <?php echo anchor('/auth/logout/', 'Logout'); ?>
        </div>
        <div class="clear"></div>
    </div>
</div>

<div class="cl"></div>

<div class="content">
    <div class="centr_1">
        <a class="logo" href="/"></a>
<?php if(!$this->session->userdata('fa_type')){ ?>
        <div class="menu<?= $this->session->userdata('client_type')=='customer'?' menu_client':''?>">
            <a class="dashboard" href="<?=site_url('dashboard/')?>">Dashboard</a>
            <?php
             if($this-> session ->userdata('client_type') == 'firm'){?>
            <a class="create_user" href="<?=site_url('profile/create_user')?>">Create Users</a>
                <?php }?>
            <a class="profile" href="<?=site_url('profile/')?>">Profile</a>
            <a class="new_case my-tooltip" title="Submit a new case for immediate processing" href="<?=site_url('request/case/')?>">New Case</a>
<!--            <a class="new_estimate my-tooltip" title="Receive an estimate for your regions" href="--><?//=site_url('request/estimate/')?><!--">New Estimate</a>-->
        </div>
        <?php }else{ ?>
        <div class="menu menu_fa">
            <a class="dashboard" href="<?=site_url('fa/')?>">Dashboard</a>
        </div>
        <?php } ?>
    </div>

    <div class="centr_2"></div>

    <div class="centr_3">
        <div class="frame">
            <div class="in_frame">
                <!--CONTENT-->
