<script type="text/javascript">
	function showAlert(type){
		stanOverlay.setTITLE(type);
		if(type){
		stanOverlay.setTYPE(type);
		}
	    stanOverlay.setMESSAGE(type);
	    stanOverlay.SHOW();
	}

	$(document).ready(function(){
		$("#countries_tabs").tabs({
			expires: 30, 
			path: '/', secure: true,
			beforeLoad: function( event, ui ) {
                return false;
            } ,
             select: function(event, ui) {
                $('.ui-tabs-panel').empty();
             }
		});


        <?php
     foreach($countries as $key => $country){ ?>
            <?php if ($country['country_id'] == $this->input->post('country_to_load')) { ?>
                $("#countries_tabs").tabs("select", <?php echo $key ?>);
            <?php } ?>
        <?php } ?>

        <?php if ($this->input->post('tab_to_load') == 'invoice') { ?>
            tab_to_load = 'invoice';
        <?php }elseif ($this->input->post('tab_to_load') == 'doc-requirements') { ?>
        tab_to_load = 'doc-requirements';
        <?php }elseif ($this->input->post('tab_to_load') == 'filing-conf') { ?>
        tab_to_load = 'filing-conf';
        <?php } else { ?>
            tab_to_load = false;
        <?php } ?>

	});
</script>
<div class="content_header case">
	<input style="display:none;"  class="flags_autocomplete" />
    <div class="title"><?= $case['case_number']?></div>
</div>
<div id="countries_tabs">
	<ul class="tabs">
	    <?php
	    	$i=1;
	    	foreach($countries as $key => $country){
	    ?>
	    <li>
	    	<a 
	    		href="<?php echo base_url(); ?>fa/get_country_data/<?php echo $country['country_id'];?>/<?= $case['case_number']?>/"
	    		id="<?php echo $country['country_id'];?>"
	    		rel="<?php echo $country['reference_number'];?>" 
	    		class="<?= $i==1?'first':($i==count($countries)?'last':'')?>"
	    	>
	    		<?php echo $country['country']; ?>
	    	</a>
	    </li>
	    <?php $i++;} ?>
	</ul>
</div>