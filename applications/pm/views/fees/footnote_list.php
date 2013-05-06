<script type="text/javascript">
    $(document).ready(function() {
    });
</script>
<?php
echo anchor('/fees/create_footnote/', 'Create Footnote');

if (check_array($footnes))
{
    $tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">');
    $this -> table -> set_template($tmpl);
    $this -> table -> set_heading('#', 'Text', '&nbsp;', '&nbsp;');
    $index = 1;
    foreach ($footnes as $foot)
    {
        $this -> table -> add_row($index,
            $foot['text'],
            anchor('/fees/edit_footnote/'.$foot['id'], '<img src="'.base_url().'assets/images/i/edit.png" alt="Edit"/>'),
            anchor(
                '/fees/delete_footnote/'.$foot['id'],
                '<img src="'.base_url().'assets/images/i/delete.png" alt="Delete"/>',
                'onclick="return confirm(\'Are you sure you want to delete this footnes?\')"'
            )
        );
        $index++;
    }
    echo $this -> table -> generate();
}
?>