<?php
	$dd_countries = array();
	if (check_array($countries))
	{
		foreach ($countries as $country)
		{
			$dd_countries[$country['id']] = $country['country'];
		}
	}
	echo form_open('/fees/insert');
if($country_id){
    echo '<p style="font-size: 16px;"><strong>Country</strong></p>&nbsp;'.form_dropdown('country_id', $dd_countries, $country_id, 'class="big"');
}else{
    echo '<p style="font-size: 16px;"><strong>Country</strong></p>&nbsp;'.form_dropdown('country_id', $dd_countries, FALSE, 'class="big"');
}
?>
	<p>&nbsp;</p>
	<div style="float: left; width: 350px;">
		<p align="center">PCT National Phase</p>
<?php
	$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table fees-table">');
	$this -> table -> set_template($tmpl);
	$this -> table -> add_row('Filing Fee Level 1', form_input('pct_filing_fee_level_1', FALSE));
	$this -> table -> add_row('Filing Fee Level 2', form_input('pct_filing_fee_level_2', FALSE));
	$this -> table -> add_row('Filing Fee Level 3', form_input('pct_filing_fee_level_3', FALSE));
	$this -> table -> add_row('Translation Rate Level 1', form_input('pct_translation_rate_level_1', FALSE));
	$this -> table -> add_row('Translation Rate Level 2', form_input('pct_translation_rate_level_2', FALSE));
	$this -> table -> add_row('Translation Rate Level 3', form_input('pct_translation_rate_level_3', FALSE));
    $this -> table -> add_row('Official Fee (Large Entity)', form_input('pct_official_fee_large', FALSE));
    $this -> table -> add_row('Official Fee (Small Entity)', form_input('pct_official_fee_small', FALSE));
    $this -> table -> add_row('Official Fee (Individual Entity)', form_input('pct_official_fee_individual', FALSE));
	$this -> table -> add_row('Translation rate for claims', form_input('pct_translation_rates_for_claims', FALSE));
	$this -> table -> add_row('Request for examination', form_input('pct_request_examination', FALSE));
	$this -> table -> add_row('Number of claims above which additional fees are charged', form_input('pct_number_claims_above_additional_fees', FALSE));
	$this -> table -> add_row('Sequence Lising Filing Fee (USD)',							   form_input('pct_sequence_listing_fee', FALSE));
	$this -> table -> add_row('Fee charged for excess claims ',							   form_input('pct_fee_additional_claims', FALSE));
	$this -> table -> add_row('additional fee for claims ',							   form_input('pct_additional_fee_for_claims', FALSE));
	$this -> table -> add_row('Number of Pages above which a fee is charged',							   form_input('pct_number_pages_above_additional_fees', FALSE));
	$this -> table -> add_row('Fee per excess Pages ',							   form_input('pct_fee_additional_pages', FALSE));
	$this -> table -> add_row('Number of Priorities claimed with no additional charge', form_input('pct_number_priorities_claimed_with_no_additional_charge', FALSE));
	$this -> table -> add_row('Charge per additional priority claimed ',form_input('pct_charge_per_additional_claimed', FALSE));
	$this -> table -> add_row('# free pages of drawings',form_input('pct_number_free_pages_drawing', FALSE));
	$this -> table -> add_row('Charge for additional pages of drawings', form_input('pct_charge_per_additional_pages_of_drawing', FALSE));
	$this -> table -> add_row('Claim # threshold for additional fee',form_input('pct_claim_number_threshold_for_additional_fee', FALSE));
	$this -> table -> add_row('Page # Threshold for additional fee', form_input('pct_page_number_treshold_for_additional_fee', FALSE));
	$this -> table -> add_row('Extension Needed Fee', form_input('pct_extension_needed_fee', FALSE));

	echo $this -> table -> generate();
	$this -> table -> clear();
?>
	</div>

	<div style="float: left; width: 350px;">
		<p align="center">EP Validation</p>
<?php
	$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table fees-table">');
	$this -> table -> set_template($tmpl);
	$this -> table -> add_row('Filing Fee Level 1', form_input('ep_filing_fee_level_1', FALSE));
	$this -> table -> add_row('Filing Fee Level 2', form_input('ep_filing_fee_level_2', FALSE));
	$this -> table -> add_row('Filing Fee Level 3', form_input('ep_filing_fee_level_3', FALSE));
	$this -> table -> add_row('Translation Rate Level 1', form_input('ep_translation_rate_level_1', FALSE));
	$this -> table -> add_row('Translation Rate Level 2', form_input('ep_translation_rate_level_2', FALSE));
	$this -> table -> add_row('Translation Rate Level 3', form_input('ep_translation_rate_level_3', FALSE));
	$this -> table -> add_row('Official Fee (Large Entity)', form_input('ep_official_fee_large', FALSE));
    $this -> table -> add_row('Official Fee (Small Entity)', form_input('ep_official_fee_small', FALSE));
    $this -> table -> add_row('Official Fee (Individual Entity)', form_input('ep_official_fee_individual', FALSE));
	$this -> table -> add_row('Translation rate for claims', form_input('ep_translation_rates_for_claims', FALSE));
	$this -> table -> add_row('Request for examination', form_input('ep_request_examination', FALSE));
	$this -> table -> add_row('Number of claims above which additional fees are charged', form_input('ep_number_claims_above_additional_fees', FALSE));
	$this -> table -> add_row('Sequence Lising Filing Fee (USD)',form_input('ep_sequence_listing_fee', FALSE));
	$this -> table -> add_row('Fee charged for excess claims ', form_input('ep_fee_additional_claims', FALSE));
	$this -> table -> add_row('additional fee for claims ', form_input('ep_additional fee for claims', FALSE));
	$this -> table -> add_row('Number of Pages above which a fee is charged', form_input('ep_number_pages_above_additional_fees', FALSE));
	$this -> table -> add_row('Fee per excess Pages ', form_input('ep_fee_additional_pages', FALSE));
	$this -> table -> add_row('Number of Priorities claimed with no additional charge', form_input('ep_number_priorities_claimed_with_no_additional_charge', FALSE));
	$this -> table -> add_row('Charge per additional priority claimed ', form_input('ep_charge_per_additional_claimed', FALSE));
	$this -> table -> add_row('# free pages of drawings', form_input('ep_number_free_pages_drawing', FALSE));
	$this -> table -> add_row('Charge for additional pages of drawings', form_input('ep_charge_per_additional_pages_of_drawing', FALSE));
	$this -> table -> add_row('Claim # threshold for additional fee', form_input('ep_claim_number_threshold_for_additional_fee', FALSE));
	$this -> table -> add_row('Page # Threshold for additional fee', form_input('ep_page_number_treshold_for_additional_fee', FALSE));
	$this -> table -> add_row('Extension Needed Fee', form_input('ep_extension_needed_fee', FALSE));

	echo $this -> table -> generate();
	$this -> table -> clear();
?>
	</div>

	<div style="float: left; width: 350px;">
		<p align="center">Direct Filing</p>
<?php
	$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table fees-table">');
	$this -> table -> set_template($tmpl);
	$this -> table -> add_row('Filing Fee Level 1', form_input('direct_filing_fee_level_1', FALSE));
	$this -> table -> add_row('Filing Fee Level 2', form_input('direct_filing_fee_level_2', FALSE));
	$this -> table -> add_row('Filing Fee Level 3', form_input('direct_filing_fee_level_3', FALSE));
	$this -> table -> add_row('Translation Rate Level 1', form_input('direct_translation_rate_level_1', FALSE));
	$this -> table -> add_row('Translation Rate Level 2', form_input('direct_translation_rate_level_2', FALSE));
	$this -> table -> add_row('Translation Rate Level 3', form_input('direct_translation_rate_level_3', FALSE));
    $this -> table -> add_row('Official Fee (Large Entity)', form_input('direct_official_fee_large', FALSE));
    $this -> table -> add_row('Official Fee (Small Entity)', form_input('direct_official_fee_small', FALSE));
    $this -> table -> add_row('Official Fee (Individual Entity)', form_input('direct_official_fee_individual', FALSE));
	$this -> table -> add_row('Translation rate for claims', form_input('direct_translation_rates_for_claims', FALSE));
	$this -> table -> add_row('Request for examination', form_input('direct_request_examination', FALSE));
	$this -> table -> add_row('Number of claims above which additional fees are charged', form_input('direct_number_claims_above_additional_fees', FALSE));
	$this -> table -> add_row('Sequence Lising Filing Fee (USD)',form_input('direct_sequence_listing_fee', FALSE));
	$this -> table -> add_row('Fee charged for excess claims ', form_input('direct_fee_additional_claims', FALSE));
	$this -> table -> add_row('additional fee for claims ', form_input('direct_additional fee for claims', FALSE));
	$this -> table -> add_row('Number of Pages above which a fee is charged', form_input('direct_number_pages_above_additional_fees', FALSE));
	$this -> table -> add_row('Fee per excess Pages ', form_input('direct_fee_additional_pages', FALSE));
	$this -> table -> add_row('Number of Priorities claimed with no additional charge', form_input('direct_number_priorities_claimed_with_no_additional_charge', FALSE));
	$this -> table -> add_row('Charge per additional priority claimed ', form_input('direct_charge_per_additional_claimed', FALSE));
	$this -> table -> add_row('# free pages of drawings', form_input('direct_number_free_pages_drawing', FALSE));
	$this -> table -> add_row('Charge for additional pages of drawings', form_input('direct_charge_per_additional_pages_of_drawing', FALSE));
	$this -> table -> add_row('Claim # threshold for additional fee', form_input('direct_claim_number_threshold_for_additional_fee', FALSE));
	$this -> table -> add_row('Page # Threshold for additional fee', form_input('direct_page_number_treshold_for_additional_fee', FALSE));
	$this -> table -> add_row('Extension Needed Fee', form_input('direct_extension_needed_fee', FALSE));
	echo $this -> table -> generate();
	$this -> table -> clear();?>
	</div>
	<p>
<?php	echo form_submit('submit', 'Insert'); ?>
	</p>
<?php	echo form_close(); ?>