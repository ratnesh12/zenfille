<?php
	// Setting 'N/A' for empty values
	$empty_sign = 'N/A';
	if (empty($case['reference_number']))
	{
		$case['reference_number'] = $empty_sign;
	}
	
	if (empty($case['applicant']))
	{
		$case['applicant'] = $empty_sign;
	}
	
	if (empty($case['number_pages']))
	{
		$case['number_pages'] = $empty_sign;
	}
	
	if (empty($case['application_title']))
	{
		$case['application_title'] = $empty_sign;
	}
	
	if (empty($case['number_claims']))
	{
		$case['number_claims'] = $empty_sign;
	}
	
	if (empty($case['number_pages_drawings']))
	{
		$case['number_pages_drawings'] = $empty_sign;
	}
	
	if (empty($case['first_priority_date']) || $case['first_priority_date'] =='00/00/00')
	{
		$case['first_priority_date'] = $empty_sign;
	}
    if(empty($case['30_month_filing_deadline']) || $case['30_month_filing_deadline'] == '00/00/00') {
		$case['30_month_filing_deadline'] = $empty_sign;
	}

    if(empty($case['31_month_filing_deadline']) || $case['31_month_filing_deadline'] == '00/00/00') {
		$case['31_month_filing_deadline'] = $empty_sign;
	}	
	
	if (empty($case['case_filing_deadline']))
	{
		$case['case_filing_deadline'] = $empty_sign;
	}	
	
	if (empty($case['filing_deadline']))
	{
		$case['filing_deadline'] = $empty_sign;
	}
    if(empty($case['international_filing_date']) || $case['international_filing_date'] == '00/00/00'){
        $case['international_filing_date'] = $empty_sign;
    }
if(empty($case['publication_date']) || $case['publication_date'] == '00/00/00'){
    $case['publication_date'] = $empty_sign;
}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd"> 
 
<html> 
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="<?= base_url();?>assets/css/estimate_pdf.css" rel="stylesheet" type="text/css" /> 
</head>
<body>
<div id="top-header">
	<p style="margin-left: 20px;"><strong>Estimate Type</strong> <?php echo $case['case_type']; ?></p>
</div>
<div id="header">
	<div id="big-logo">
        <img id ="headerimg" src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/pm/assets/images/skin/logo_big1.png"/>
	</div>
	<div id="estimate-label">
		<img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/pm/assets/images/skin/estimate-label.png" />
		<p><?php echo date('m/d/Y')?></p>
	</div>
</div>
<h1>Case Details</h1>
<div><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/pm/assets/images/skin/pdf-line-divider.png" /></div>
<table cellpadding="4" cellspacing="0" align="center" width="100%" class="data-table">
	<tr>
		<td class="strong"><strong>Client Reference Number:</strong></td>
		<td><?php echo (empty($case['reference_number'])) ? 'N/A' : $case['reference_number'];?></td>
	</tr>
	<tr>
		<td class="strong"><strong>ZenFile Reference Number:</strong></td>
		<td><?php echo $case['case_number']?></td>
	</tr>
	<tr>
		<td class="strong"><strong>Application Number:</strong></td>
		<td><?php echo $case['application_number']?></td>
	</tr>
</table>
<div><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/pm/assets/images/skin/pdf-line-divider.png" /></div>
<h1>Patent Details</h1>
<div><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/pm/assets/images/skin/pdf-line-divider.png" /></div>
<table cellpadding="4" cellspacing="0" align="center" width="100%" class="data-table">
	<tr>
		<td class="strong"><strong>Applicant:</strong></td>
		<td><?php echo $case['applicant']?></td>
		<td class="strong"><strong>Specification Pages:</strong></td>
		<td><?php echo $case['number_pages']?></td>
	</tr>
	<tr>
		<td class="strong"><strong>Title:</strong></td>
		<td><?php echo $case['application_title']?></td>
		<td class="strong"><strong>Number of Claims:</strong></td>
		<td><?php echo $case['number_claims']?></td>
	</tr>
	<tr>
		<td class="strong"><strong>Language:</strong></td>
		<td><?php echo $publication_language?></td>
		<?php if ($case['case_type_id'] == '2'){?>
		<td></td>
		<td></td>
		<?php }else{ ?>
		<td class="strong"><strong>Pages of Drawings:</strong></td>
		<td><?php echo $case['number_pages_drawings']?></td>
		<?php } ?>
	</tr>
	<tr>
		<?php if ($case['case_type_id'] == '2'):?>
		<td class="strong"><strong>Publication Date:</strong></td>
		<td><?php echo $case['publication_date']?></td>
		<?php else: ?>
		<td class="strong"><strong>First Priority Date:</strong></td>
		<td><?php echo $case['first_priority_date']?></td>
		<?php endif;?>
		
		<?php if ($case['case_type_id'] == '3'):?>
		<td class="strong"><strong>Number of Priorities Claimed:</strong></td>
		<td><?php echo ($case['number_priorities_claimed']);?></td>
		<?php elseif ($case['case_type_id'] == '1'):?>
		<td class="strong"><strong>Sequence Listing:</strong></td>
		<td><?php echo ($case['sequence_listing'] == '1') ? 'Yes': 'No';?></td>
		<?php else:?>
		<td class="strong">&nbsp;</td>
		<td>&nbsp;</td>
		<?php endif;?>
	</tr>
	<tr>
		<?php if ($case['case_type_id'] == '1'):?>
		<td class="strong"><strong>International Filing Date:</strong></td>
		<td><?php echo $case['international_filing_date']?></td>
		<?php else: ?>
		<td class="strong"><strong>Filing Deadline:</strong></td>
		<td><?php echo $case['filing_deadline']?></td>
		<?php endif;?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<?php if ($case['case_type_id'] == '1'):?>
	<tr>
		<td class="strong"><strong>30-Month Filing Deadline:</strong></td>
		<td><?php echo $case['30_month_filing_deadline']?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="strong"><strong>31-Month Filing Deadline:</strong></td>
		<td><?php echo $case['31_month_filing_deadline']?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<?php endif;?>
</table>
<div><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/pm/assets/images/skin/pdf-line-divider.png" /></div>
<?php
	$all_totals = 0;

	if (isset($countries_fees['countries']) && check_array($countries_fees['countries']))
	{ 
		foreach ($countries_fees['countries'] as $index => $fee)
		{
			$row_total = $fee['result_total'];
			$all_totals += $row_total;
			switch($case['case_type_id']){
				case 1:
                    if(!empty($fee['pct_language'])) {
					    $language = $fee['pct_language'];
                    } else {
                        $language = false;
                    }
					break;
				case 2:
                    if(!empty($fee['ep_language'])) {
					    $language = $fee['ep_language'];
                    } else {
                        $language = false;
                    }
					break;
				case 3:
                    if(!empty($fee['direct_language'])) {
					    $language = $fee['direct_language'];
                    } else {
                        $language = false;
                    }
					break;
				default:
                    if(!empty($fee['direct_language'])) {
					    $language = $fee['direct_language'];
                    }
			}
			$countries_fees['countries'][$index]['language'] = $language;
		}
	}
	$case_currency_sign = '$';
	if ($case['case_currency'] == 'euro')
	{
		$case_currency_sign = '€';
	}
?>
<h1>Total Cost:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $case_currency_sign.number_format($all_totals, 0, '.', ',')?></h1>
<div><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/pm/assets/images/skin/pdf-line-divider.png" /></div>
<table cellpadding="4" cellspacing="0" align="center" width="100%" id="fees-table">
<tr>
	<?php if ($case['case_type_id'] == '2'){?>
		<th>Country</th>
	<?php }else{ ?>
		<th>Country/Organization</th>
	<?php } ?>
	<th>Language</th>
	<th>Filing Fee</th>
	<th>Official Fee</th>
	<th>Translation Fee</th>
	<th>Total</th>
</tr>
<?php
	$all_totals = 0;
	if (check_array($countries_fees['countries']))
	{

		$number_footnotes = 1;
		foreach ($countries_fees['countries'] as $fee)
		{
			$filing_footnote = '';
			$official_footnote = '';
			$translation_footnote = '';
		
			// Looking for footnote for current country
			$filing_fee_value 		= $fee['result_filing_fee'];
			$official_fee_value 	= $fee['result_official_fee'];
			$translation_fee_value 	= $fee['result_translation_fee'];
			
			if (isset($footnotes[$fee['country_id']]))
			{
				foreach ($footnotes[$fee['country_id']] as $footnote_item)
				{					
					$var_name = $footnote_item['fee_type'].'_footnote';
					$$var_name = '<sup>'.$number_footnotes.'</sup>';
					$number_footnotes++;
				}
			}
			
			$row_total = $fee['result_total'];
			$all_totals += $row_total;
			echo '<tr>
					<td class="left"><strong>'.$fee['country'].'</strong></td>
					<td class="left">'.$fee['language'].'</td>
					<td class="left">'.number_format($filing_fee_value).$filing_footnote.'</td>
					<td class="left">'.number_format($official_fee_value).$official_footnote.'</td>
					<td class="left">'.number_format($translation_fee_value).$translation_footnote.'</td>
					<td class="left">'.number_format($row_total).'</td>
				  </tr>';
		}
	}
?>
<tr>
	<td colspan="6">&nbsp;</td>
</tr>
<tr>
	<td colspan="5" class="right"><strong>Total:</strong></td>
<?php
	$case_currency_sign = '$';
	if ($case['case_currency'] == 'euro')
	{
		$case_currency_sign = '€';
	}
?>
	<td><strong><?php echo $countries_fees['bottom_currency_sign'].number_format($countries_fees['bottom_all_total'], 0, '.', ',')?></strong></td>
</tr>
</table>
<p>&nbsp;</p>
<div>
	<img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/pm/assets/images/skin/pdf-divider.png" />
</div>
<div id="top-footnote">
	<?php echo $case['top_footnote']?>
</div>
<div>
	<img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/pm/assets/images/skin/pdf-divider.png" />
</div>
<?php
	if (check_array($footnotes))
	{
?>
	<table cellpadding="10" cellspacing="0" align="center" width="100%" id="footnotes-table">
<?php
		$index = 1;
		foreach ($footnotes as $country_footnotes)
		{
			if (check_array($country_footnotes))
			{
				foreach ($country_footnotes as $footnote)
				{					
					echo '<tr>
							<td valign="top" align="center" class="number">'.$index.'</td>
							<td><a name="footnote'.$index.'">'.$footnote['footnote'].'</a></td>
						  </tr>';
					$index++;
				}
			}
			
		}
?>
	</table>
	<p>&nbsp;</p>
<?php
	}
?>
<div id="footer">
	<div id="small-logo">
		<img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/pm/assets/images/skin/pdf_logo_small.png"/>
	</div>
	<div id="company-info">
		<p><strong>ZenFile Inc.</strong></p>
		<p>244 Fifth Avenue, Suite 2200, New York, NY 10001 / Phone: (212) 967-9240</p>
		<p><a href="mailto:info@zenfile.com">info@zenfile.com</a>&nbsp;/&nbsp;<a href="http://zenfile.com/">zenfile.com</a></p>
	</div>
</div>
</body>
</html>