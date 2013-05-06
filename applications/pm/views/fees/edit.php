<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery-ui/jquery-ui-original.css" />	
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery-ui/jquery.ui.accordion.css" />	
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.core.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.widget.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.effects.core.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.accordion.min.js"></script>

<script>
	$(function() {
		$( "#accordion" ).accordion();
	});
	$(document).ready(function() {
		$("select[name='country_id']").change(function() {
			var country_id = $(this + "option:selected").val();
			document.location.href = "<?php echo base_url()?>fees/edit/" + country_id;
		});
	});
</script>
<?php
	$dd_countries = array();
	if (check_array($countries))
	{
		foreach ($countries as $country)
		{
			$dd_countries[$country['id']] = $country['country'];
		}
	}
	echo form_open('/fees/update/'.$country_id);
	echo '<p style="font-size: 16px;"><strong>Country</strong></p>&nbsp;'.form_dropdown('country_id', $dd_countries, $country_id, 'class="big"');
?>
    <br/>
    <a href="1" rel="<?php echo $country_id ?>" class="update_fees_for_all_users update_pct_for_all_users"></a>
    <a href="2" rel="<?php echo $country_id ?>" class="update_fees_for_all_users update_ep_for_all_users"></a>
    <a href="3" rel="<?php echo $country_id ?>" class="update_fees_for_all_users update_direct_for_all_users"></a>
	<div class="clear"></div>
	<p>&nbsp;</p>
	<div id="accordion">
		<h3><a href="#">PCT National Phase</a></h3>
		<div>
			<p>
				<?php
					$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table fees-table">');
					$this -> table -> set_template($tmpl);
					$this -> table -> add_row('Filing Fee Level 1', form_input('pct_filing_fee_level_1', $pct_fees['filing_fee_level_1']));
					$this -> table -> add_row('Filing Fee Level 2', form_input('pct_filing_fee_level_2', $pct_fees['filing_fee_level_2']));
					$this -> table -> add_row('Filing Fee Level 3', form_input('pct_filing_fee_level_3', $pct_fees['filing_fee_level_3']));
					$this -> table -> add_row('Translation Rate Level 1', form_input('pct_translation_rate_level_1', $pct_fees['translation_rate_level_1']));
					$this -> table -> add_row('Translation Rate Level 2', form_input('pct_translation_rate_level_2', $pct_fees['translation_rate_level_2']));
					$this -> table -> add_row('Translation Rate Level 3', form_input('pct_translation_rate_level_3', $pct_fees['translation_rate_level_3']));
					$this -> table -> add_row('Official Fee (Large Entity)', form_input('pct_official_fee_large', $pct_fees['official_fee_large']));
                    $this -> table -> add_row('Official Fee (Small Entity)', form_input('pct_official_fee_small', $pct_fees['official_fee_small']));
                    $this -> table -> add_row('Official Fee (Individual Entity)', form_input('pct_official_fee_individual', $pct_fees['official_fee_individual']));
					$this -> table -> add_row('Translation rate for claims', form_input('pct_translation_rates_for_claims', $pct_fees['translation_rates_for_claims']));
					$this -> table -> add_row('Request for examination', form_input('pct_request_examination', $pct_fees['request_examination']));
					$this -> table -> add_row('Number of claims above which additional fees are charged', form_input('pct_number_claims_above_additional_fees',  $pct_fees['number_claims_above_additional_fees']));
					$this -> table -> add_row('Fee charged for excess claims ', form_input('pct_fee_additional_claims', $pct_fees['fee_additional_claims']));
					$this -> table -> add_row('additional fee for claims ', form_input('pct_additional_fee_for_claims', $pct_fees['additional_fee_for_claims']));
					$this -> table -> add_row('Number of Pages above which a fee is charged', form_input('pct_number_pages_above_additional_fees', $pct_fees['number_pages_above_additional_fees']));
					$this -> table -> add_row('Fee per excess Pages ', form_input('pct_fee_additional_pages', $pct_fees['fee_additional_pages']));
					$this -> table -> add_row('Number of Priorities claimed with no additional charge', form_input('pct_number_priorities_claimed_with_no_additional_charge', $pct_fees['number_priorities_claimed_with_no_additional_charge']));
					$this -> table -> add_row('Charge per additional priority claimed ', form_input('pct_charge_per_additional_claimed', $pct_fees['charge_per_additional_claimed']));
					$this -> table -> add_row('# free pages of drawings', form_input('pct_number_free_pages_drawing', $pct_fees['number_free_pages_drawing']));
					$this -> table -> add_row('Charge for additional pages of drawings', form_input('pct_charge_per_additional_pages_of_drawing', $pct_fees['charge_per_additional_pages_of_drawing']));
					$this -> table -> add_row('Claim # threshold for additional fee', form_input('pct_claim_number_threshold_for_additional_fee', $pct_fees['claim_number_threshold_for_additional_fee']));
					$this -> table -> add_row('Page # Threshold for additional fee', form_input('pct_page_number_treshold_for_additional_fee', $pct_fees['page_number_treshold_for_additional_fee']));
					$this -> table -> add_row('Additional fee above threshold', form_input('pct_additional_fee_above_treshold', $pct_fees['additional_fee_above_treshold']));
                    $this -> table -> add_row('Sequence Listing Filing Fee', form_input('pct_sequence_listing_fee', $pct_fees['sequence_listing_fee']));
					echo $this -> table -> generate();
					$this -> table -> clear();
				?>
			</p>
		</div>
	
	<h3><a href="#">EP Validation</a></h3>
		<div>
			<p>
			<?php
				$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table fees-table">');
				$this -> table -> set_template($tmpl);
				$this -> table -> add_row('Filing Fee Level 1', form_input('ep_filing_fee_level_1', $ep_fees['filing_fee_level_1']));
				$this -> table -> add_row('Filing Fee Level 2', form_input('ep_filing_fee_level_2', $ep_fees['filing_fee_level_2']));
				$this -> table -> add_row('Filing Fee Level 3', form_input('ep_filing_fee_level_3', $ep_fees['filing_fee_level_3']));
				$this -> table -> add_row('Translation Rate Level 1', form_input('ep_translation_rate_level_1', $ep_fees['translation_rate_level_1']));
				$this -> table -> add_row('Translation Rate Level 2', form_input('ep_translation_rate_level_2', $ep_fees['translation_rate_level_2']));
				$this -> table -> add_row('Translation Rate Level 3', form_input('ep_translation_rate_level_3', $ep_fees['translation_rate_level_3']));
                $this -> table -> add_row('Official Fee (Large Entity)', form_input('ep_official_fee_large', $ep_fees['official_fee_large']));
                $this -> table -> add_row('Official Fee (Small Entity)', form_input('ep_official_fee_small', $ep_fees['official_fee_small']));
                $this -> table -> add_row('Official Fee (Individual Entity)', form_input('ep_official_fee_individual', $ep_fees['official_fee_individual']));
				$this -> table -> add_row('Translation rate for claims', form_input('ep_translation_rates_for_claims', $ep_fees['translation_rates_for_claims']));
				$this -> table -> add_row('Request for examination', form_input('ep_request_examination', $ep_fees['request_examination']));
				$this -> table -> add_row('Number of claims above which additional fees are charged', form_input('ep_number_claims_above_additional_fees',  $ep_fees['number_claims_above_additional_fees']));
				$this -> table -> add_row('Fee charged for excess claims ', form_input('ep_fee_additional_claims', $ep_fees['fee_additional_claims']));
				$this -> table -> add_row('additional fee for claims ', form_input('ep_additional_fee_for_claims', $ep_fees['additional_fee_for_claims']));
				$this -> table -> add_row('Number of Pages above which a fee is charged', form_input('ep_number_pages_above_additional_fees', $ep_fees['number_pages_above_additional_fees']));
				$this -> table -> add_row('Fee per excess Pages ', form_input('ep_fee_additional_pages', $ep_fees['fee_additional_pages']));
				$this -> table -> add_row('Number of Priorities claimed with no additional charge', form_input('ep_number_priorities_claimed_with_no_additional_charge', $ep_fees['number_priorities_claimed_with_no_additional_charge']));
				$this -> table -> add_row('Charge per additional priority claimed ', form_input('ep_charge_per_additional_claimed', $ep_fees['charge_per_additional_claimed']));
				$this -> table -> add_row('# free pages of drawings', form_input('ep_number_free_pages_drawing', $ep_fees['number_free_pages_drawing']));
				$this -> table -> add_row('Charge for additional pages of drawings', form_input('ep_charge_per_additional_pages_of_drawing', $ep_fees['charge_per_additional_pages_of_drawing']));
				$this -> table -> add_row('Claim # threshold for additional fee', form_input('ep_claim_number_threshold_for_additional_fee', $ep_fees['claim_number_threshold_for_additional_fee']));
				$this -> table -> add_row('Page # Threshold for additional fee', form_input('ep_page_number_treshold_for_additional_fee', $ep_fees['page_number_treshold_for_additional_fee']));
				$this -> table -> add_row('Additional fee above threshold', form_input('ep_additional_fee_above_treshold', $ep_fees['additional_fee_above_treshold']));
                $this -> table -> add_row('Sequence Listing Filing Fee', form_input('ep_sequence_listing_fee', $ep_fees['sequence_listing_fee']));
				
				echo $this -> table -> generate();
				$this -> table -> clear();
			?>
			</p>
		</div>
	
	<h3><a href="#">Direct Filing</a></h3>
		<div>
			<p>
				<?php
					$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table fees-table">');
					$this -> table -> set_template($tmpl);
					$this -> table -> add_row('Filing Fee Level 1', form_input('direct_filing_fee_level_1', $direct_fees['filing_fee_level_1']));
					$this -> table -> add_row('Filing Fee Level 2', form_input('direct_filing_fee_level_2', $direct_fees['filing_fee_level_2']));
					$this -> table -> add_row('Filing Fee Level 3', form_input('direct_filing_fee_level_3', $direct_fees['filing_fee_level_3']));
					$this -> table -> add_row('Translation Rate Level 1', form_input('direct_translation_rate_level_1', $direct_fees['translation_rate_level_1']));
					$this -> table -> add_row('Translation Rate Level 2', form_input('direct_translation_rate_level_2', $direct_fees['translation_rate_level_2']));
					$this -> table -> add_row('Translation Rate Level 3', form_input('direct_translation_rate_level_3', $direct_fees['translation_rate_level_3']));
                    $this -> table -> add_row('Official Fee (Large Entity)', form_input('direct_official_fee_large', $direct_fees['official_fee_large']));
                    $this -> table -> add_row('Official Fee (Small Entity)', form_input('direct_official_fee_small', $direct_fees['official_fee_small']));
                    $this -> table -> add_row('Official Fee (Individual Entity)', form_input('direct_official_fee_individual', $direct_fees['official_fee_individual']));
					$this -> table -> add_row('Translation rate for claims', form_input('direct_translation_rates_for_claims', $direct_fees['translation_rates_for_claims']));
					$this -> table -> add_row('Request for examination', form_input('direct_request_examination', $direct_fees['request_examination']));
					$this -> table -> add_row('Number of claims above which additional fees are charged', form_input('direct_number_claims_above_additional_fees',  $direct_fees['number_claims_above_additional_fees']));
					$this -> table -> add_row('Fee charged for excess claims ', form_input('direct_fee_additional_claims', $direct_fees['fee_additional_claims']));
					$this -> table -> add_row('additional fee for claims ', form_input('direct_additional_fee_for_claims', $direct_fees['additional_fee_for_claims']));
					$this -> table -> add_row('Number of Pages above which a fee is charged', form_input('direct_number_pages_above_additional_fees', $direct_fees['number_pages_above_additional_fees']));
					$this -> table -> add_row('Fee per excess Pages ', form_input('direct_fee_additional_pages', $direct_fees['fee_additional_pages']));
					$this -> table -> add_row('Number of Priorities claimed with no additional charge', form_input('direct_number_priorities_claimed_with_no_additional_charge', $direct_fees['number_priorities_claimed_with_no_additional_charge']));
					$this -> table -> add_row('Charge per additional priority claimed ', form_input('direct_charge_per_additional_claimed', $direct_fees['charge_per_additional_claimed']));
					$this -> table -> add_row('# free pages of drawings', form_input('direct_number_free_pages_drawing', $direct_fees['number_free_pages_drawing']));
					$this -> table -> add_row('Charge for additional pages of drawings', form_input('direct_charge_per_additional_pages_of_drawing', $direct_fees['charge_per_additional_pages_of_drawing']));
					$this -> table -> add_row('Claim # threshold for additional fee', form_input('direct_claim_number_threshold_for_additional_fee', $direct_fees['claim_number_threshold_for_additional_fee']));
					$this -> table -> add_row('Page # Threshold for additional fee', form_input('direct_page_number_treshold_for_additional_fee', $direct_fees['page_number_treshold_for_additional_fee']));
					$this -> table -> add_row('Additional fee above threshold', form_input('direct_additional_fee_above_treshold', $direct_fees['additional_fee_above_treshold']));
                    $this -> table -> add_row('Sequence Listing Filing Fee', form_input('direct_sequence_listing_fee', $direct_fees['sequence_listing_fee']));
					
					echo $this -> table -> generate();
					$this -> table -> clear();
				?>
			</p>
		</div>
	</div>	
	<p>&nbsp;</p>
<?php echo form_submit('submit', 'Update');?>
	</p>
<?php echo form_close();?>