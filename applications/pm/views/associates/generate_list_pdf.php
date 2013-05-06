<html>
<head>
<link href="<?php echo base_url()?>assets/css/associates_list_pdf.css" media="screen" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="top-header">
	<div class="black-line"></div>
	<div id="logo"><img src="<?php echo base_url()?>assets/images/skin/zen-logo.png" /></div>
	<div class="black-line"></div>
</div>
<div id="content">
<?php
	if (check_array($associates))
	{
		$tmpl = array (
                'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" width="80%" align="center">',
        );
		$this -> table -> set_template($tmpl);
		$table_data = array();
		foreach ($associates as $associate)
		{
//            $fa_data ='<strong>'.$associate['country'].'</strong><br/>';
//            if( !empty($associate['name'])){
//                $fa_data .= $associate['name'].'<br/>';
//            }
//            if(!empty($associate['firm'])){
//                $fa_data .= $associate['firm'].'<br/>';
//            }
//            if(!empty($associate['address'])){
//                $fa_data .= $associate['address'].'<br/>';
//            }
//            if(!empty($associate['address2'])){
//                $fa_data .= $associate['address2'].'<br/>';
//            }if(!empty($associate['phone'])){
//            $fa_data .= $associate['phone'].'<br/>';
//        }
//            if(!empty($associate['fax'])){
//                $fa_data .= $associate['fax'].'<br/>';
//            }
//            if(!empty($associate['email'])){
//                $fa_data .= $associate['email'].'<br/>';
//            }
//            if(!empty($associate['website'])){
//                $fa_data .= $associate['website'].'<br/>';
//            }
//	        $table_data[] = $fa_data;
            $table_data[] = '<strong>'.$associate['country'].'</strong><br/>'.nl2br($associate['associate']);
		}
		$table = $this -> table -> make_columns($table_data, 2);
		if (count($table_data) > 0)
		{
			echo $this -> table -> generate($table);
		}
	}
?>
</div>
<div class="clear"></div>
<div id="footer">
<hr/>
<center>
244 Fifth Avenue &middot; Suite 2200 &middot; New York, N.Y. 10001 &middot; P: 212-967-9240 &middot; F: 212-967-9242
</center>
</div>
</body>
</html>