<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        var CKEDITOR_BASEPATH = 'assets/js/ckeditor/';
        $("#template_content").ckeditor( {
            toolbar : 'Zen',
            uiColor : '#CCCCCC'
        });
        
        $(".popup").facebox();
    });
</script>
<p><?php echo anchor('/emails/variables/', 'Available Variables', 'class="popup"'); ?></p>
<?php
    echo form_open('/emails/insert/');
    $tmpl = array (
            'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
    );
    $this -> table -> set_template($tmpl); 
    $this -> table -> add_row('Title', form_input('title', FALSE, 'id="title" style="width: 100%;"'));
    $this -> table -> add_row('Description', form_input('description', FALSE, 'id="description" style="width: 100%;"'));
    $this -> table -> add_row('Subject', form_input('subject', FALSE, 'id="subject" style="width: 100%;"'));
    $this -> table -> add_row('Content', form_textarea('content', FALSE, 'id="template_content" style="width: 100%;"'));
    $this -> table -> add_row('&nbsp;', form_submit('submit', 'Insert'));
    echo $this -> table -> generate();
    echo form_close();
?>