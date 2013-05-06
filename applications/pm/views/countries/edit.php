<?php
if($form_errors = validation_errors()){
	echo('<div class="form_errors">'.validation_errors().'</div>');
}

echo anchor('/countries/', 'Back to list');
echo form_open_multipart('/countries/update/'.(isset($country['id'])?$country['id']:''));
$tmpl = array (
    'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
);
$this -> table -> set_template($tmpl);
if(isset($country['id'])){
	$this -> table -> add_row('Country', $country['country']);
}else{
	$this -> table -> add_row('Country', form_input('country',set_value('country')));
}


$this -> table -> add_row('Code', form_input('code', isset($country['code'])?$country['code']:set_value('code')));
$this -> table -> add_row('Currency Code', form_input('currency_code', isset($country['currency_code'])?$country['currency_code']:set_value('currency_code')));
$flag = ( ! empty($country['flag_image'])) ? '<img width="32" align="left" src="/client/'.$country['flag_image'].'" />' : '';
$this -> table -> add_row('Flag Image', $flag.'&nbsp;'.form_upload('flag_image').'&nbsp;<span style="color:#af0202;">Upload 48px X 48px png file</span>');

$this -> table -> add_row('PCT Language', form_input('pct_language', isset($country['pct_language'])?$country['pct_language']:set_value('pct_language')));
$this -> table -> add_row('EP Language', form_input('ep_language', isset($country['ep_language'])?$country['ep_language']:set_value('ep_language')));
$this -> table -> add_row('Direct Filing Language', form_input('direct_language', isset($country['direct_language'])?$country['direct_language']:set_value('direct_language')));
$this -> table -> add_row('Target Language', form_input('target_language', isset($country['target_language']) ? $country['target_language'] : set_value('target_language')));

$this -> table -> add_row('PCT National Phase', form_checkbox('pct', '1', isset($country['pct'])?$country['pct']:''));
$this -> table -> add_row('EP Validation', form_checkbox('ep-validation', '1', isset($country['ep-validation'])?$country['ep-validation']:''));
$this -> table -> add_row('Direct Filing', form_checkbox('direct-filing', '1', isset($country['direct-filing'])?$country['direct-filing']:''));

$this -> table -> add_row('Common in PCT', form_checkbox('common-pct', '1', isset($country['common-pct'])?$country['common-pct']:''));
$this -> table -> add_row('Common in EP', form_checkbox('common-ep-validation', '1', isset($country['common-ep-validation'])?$country['common-ep-validation']:''));
$this -> table -> add_row('Common in Direct', form_checkbox('common-direct-filing', '1', isset($country['common-direct-filing'])?$country['common-direct-filing']:''));

$this -> table -> add_row('Filing Deadline 12 month', form_radio('filling-deadline', '12', $country['country_filing_deadline']==12?true:false));
$this -> table -> add_row('Filing Deadline 30 month', form_radio('filling-deadline', '30', $country['country_filing_deadline']==30?true:false));
$this -> table -> add_row('Filing Deadline 31 month', form_radio('filling-deadline', '31', $country['country_filing_deadline']==31?true:false));

$this -> table -> add_row('&nbsp;', form_submit('submit', isset($country['id'])?'Update':'Insert'));
echo $this -> table -> generate();
echo form_close();
?>