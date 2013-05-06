<?php

$foot = '';
$id = '';
if(isset($footnes)){
    $foot = $footnes['text'];
    $id = $footnes['id'];
}?>
<p><?php echo $action ?> Footnote</p>

<?php echo form_open('/fees/update_footnote/'.$action);
echo form_textarea('text', $foot);
echo form_hidden('id', $id);
echo form_submit('do_action', $action, 'class="button"');
?>