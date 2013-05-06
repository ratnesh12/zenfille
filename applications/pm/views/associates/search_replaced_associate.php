<script type="text/javascript">
    $(document).ready(function() {
        $("#new_associate_id").tokenInput(<?php echo json_encode($associates); ?>, {
            propertyToSearch: "name",
            preventDuplicates: true
        });
    });
    function runMyFunction(){
        // need to load popap from cases/replace_associate_form
        country_id = '<?= $country_id ;?>';
        case_number = '<?= $case_number ;?>';
        associate_id = '<?= $associate_id ;?>';
        is_replaced = '1';
        $.post("<?php echo base_url(); ?>cases/replace_associate_form/", {country_id:country_id, associate_id:associate_id, case_number:case_number, is_replaced:is_replaced}, function (result) {
            $('.content').html(result);
        });
    }
</script>
<p>Replace Associate</p>
<form name="add_country_to_estimate" method="post" action="<?php echo base_url()?>cases/replace_associate/<?php echo $case_number; ?>">
    <input type="hidden" name="associate_id" value="<?php echo $associate_id; ?>" />
    <input type="hidden" name="country_id" value="<?php echo $country_id ; ?>" />
    <input type="text" id="new_associate_id" name="new_associate_id" /><br/><br/>
    <input type="submit" value="replace" name="submit"/>
    <input type="submit" class="popap" id = "add_new_associate" value="add new associate" name="add_new" onclick="runMyFunction();return false"/>
</form>