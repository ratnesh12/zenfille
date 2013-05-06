<?php
if($form_errors = validation_errors()){
	echo('<div class="form_errors">'.validation_errors().'</div>');
}

echo anchor('/park_fees/', 'Back to list');
echo form_open_multipart('/park_fees/create_edit/'.(isset($fee['id'])?$fee['id']:''));
$tmpl = array (
    'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
);
$this -> table -> set_template($tmpl);

$this -> table -> add_row('Target Language', form_input('target_language',isset($fee['target_language'])?$fee['target_language']:set_value('target_language')));
$this -> table -> add_row('Park Client Rate', form_input('standart_rate', isset($fee['standart_rate'])?$fee['standart_rate']:set_value('standart_rate')));
$this -> table -> add_row('Zenfile Client Rate', form_input('zenfile_client_rate', isset($fee['zenfile_client_rate'])?$fee['zenfile_client_rate']:set_value('zenfile_client_rate')));
$this -> table -> add_row('Zenfile Rate', form_input('zenfile_rate', isset($fee['zenfile_rate'])?$fee['zenfile_rate']:set_value('zenfile_rate')));
$this -> table -> add_row('Cost', form_input('cost', isset($fee['cost'])?$fee['cost']:set_value('cost')));
$this -> table -> add_row('Park Rate Into English', form_input('into_english', isset($fee['into_english'])?$fee['into_english']:set_value('into_english')));
$this -> table -> add_row('Zenfile Rate Into English', form_input('zenfile_into_english', isset($fee['zenfile_into_english'])?$fee['zenfile_into_english']:set_value('zenfile_into_english')));
$this -> table -> add_row('&nbsp;', form_submit('submit', isset($fee['id'])?'Update':'Insert'));
echo $this -> table -> generate();
echo form_close();
?>