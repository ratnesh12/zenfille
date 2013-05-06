
<script type="text/javascript">

    Zenfile.submitUrl = "<?= site_url('cases/approve_estimate_form_submit/' . $case['case_number']) ?>/";
    Zenfile.jsonSearchCountriesUrl = "<?= site_url('countries/json_search_countries') ?>/";
    Zenfile.uploadUrl = "<?= site_url('cases/upload') ?>/<?= $case_id ?>/?zenfile_sessid=";
    Zenfile.uploadUrlSecure = Zenfile.uploadUrl;
    Zenfile.uploadedRemoveUrl = "<?= site_url('cases/remove_uploaded') ?>/";
    Zenfile.flashUploadButtonImage = "<?= site_url("assets/img/upload_sprite_wl2.png") ?>";
    Zenfile.case_number = "<?= $case['case_number'] ?>";
    Zenfile.case_type = "<?= $case['case_type_id'] ?>";
    Zenfile.base_url = "<?= base_url() ?>";
    $('.file-category').live('change', function() {
        var parent = $(this).parents(".file");
        var file_id = $("input:last",parent).val();
        var file_type_id = $("select",parent).val();
        $.post("<?php echo base_url(); ?>cases/set_file_type/", {file_id: file_id, file_type_id: file_type_id});
    });

    $(document).ready(function(){
        jQuery(".tb_switch").each(function(){
            jQuery('#'+jQuery(this).attr('ref')).hide();
        });
        jQuery(".tb_switch").live('click',(function(){
            jQuery('#'+jQuery(this).attr('ref')).toggle('slow');
        }));
        jQuery(".ui-state-default a").click(function(){
            check_tab();
        });
        check_tab();

		$('a#check_all').live('click',function(){
			$('#estimate-form input[type=checkbox]').each(function(){
				if ($(this).attr("checked") != "checked") {
					$(this).attr("checked",true);
		            $(this).parent(".ez-checkbox-blue").addClass("ez-checked-blue");
		        }

			});
			
			return false;
		});

        $('.estimate_entry').change(function(){
            var country_id = $(this).attr('rel');

            if ($(this).attr('checked')) {
                $('.sub_country_' + country_id).attr('checked' , true).parent(".ez-checkbox-blue").addClass("ez-checked-blue");
            } else {
                $('.sub_country_' + country_id).attr('checked' , false).parent(".ez-checkbox-blue").removeClass("ez-checked-blue");
            }
        });

        $('.send_deadline_notification').click(function(){
            var id = $(this).attr('href');
            $.ajax({
                type: "POST",
                cache: false,
                url: "<?php echo base_url() ?>cases/ajax_send_notification_email" ,
                data: {
                    id: id
                } ,
                datatype: 'json' ,
                success: (function(html) {

                })
            });
            $(this).unbind('click').removeClass('send_deadline_notification').addClass('already_sent');
            $(this).click(function(){
                already_sent();
                return false;
            });
            return false;
        });

        $('.already_sent').click(function(){
            already_sent();
            return false;
        });

        function already_sent() {
            alert('Your representative will get back to you at their earliest convenience. Please be patient.');
        }

        function iteration() {

            $.ajax({
                type: "POST",
                cache: false,
                url: "/client/cases/is_estimate_available_for_client_ajax/<?php echo $case['case_number'] ?>" ,
                data: {

                } ,
                datatype: 'json' ,
                success: (function(data) {
                    if (data == 'true') {
                        window.location = '/client/cases/view/<?php echo $case['case_number'] ?>';
                    }
                })
            });

            setTimeout(iteration, 5000); // Wait 10 ms to let the UI update.
        }
<?php
if (!$case['estimate_available_for_client'])
{?>
                iteration();
<?php } ?>
    });
	
    function check_tab(){
        if(jQuery("#files").hasClass('ui-tabs-hide')){
            jQuery("#uploader_block").hide();
        }else{
            jQuery("#uploader_block").show();
        }
    }


</script>

<!--[if lte IE 8]>
<style type="text/css">
.countries .flag{width:32px;}
.countries .file_download{width:130px;}
</style>
<![endif]-->

<script id="templateSelectedFlag" type="text/x-jquery-tmpl">
    <div class="flag-box" id="selected-${type}-${id}">
        <div class="flag-img">
            <img src="${flag_src}">
            <a class="remove flag_remove_activator" href="#"></a>
            <input type="hidden" name="countries[]" value="${id}" />
        </div>
        <div>{{html value}}</div>
    </div>
</script>

<div class="content_header case">
    <div class="title">Case View</div>
    <div class="uploader_block_position">
        <div id="uploader_block">
            <div id="file-uploader">
                <input class="nofloat" id="upload_file" name="upload_file" type="file"  />
            </div>
        </div>
    </div>
    <?php
    $days = 0;

    if (!empty($case['filing_deadline']))
    {
        $time_left = strtotime($case['filing_deadline']) - time();
        $days = $time_left / 86400;
    }
    ?>
<?php if ($days > 0 && $case['common_status'] == 'active' && !empty($countries_for_related)): ?>
        <a href="<?php echo base_url() ?>request/create_related_case/<?php echo preg_replace('/[^\d]/', '', $case['case_number']); ?>"
        	class="button my-tooltip"
        	title="Add additional regions to this application.">
            <p class="p15">Add a country</p>
            <p class="p11">You have <?php echo (int) $days ?> days left</p>
        </a>
<?php endif ?>
</div>

<div class="tabs_container">
    <ul class="tabs">
        <li><a href="#case-info" class="first">Case Info</a></li>
        <?php if (!$case['is_intake']): ?>
            <li><a href="#estimate" >Estimate</a></li>
        <?php endif ?>
        <li>
        	<a href="#files"
        		class="<?php if (!$case['is_associates_visible_to_client']) echo "last"; ?>">
        		Case Files
        	</a>
        </li>
        <?php
        if ($case['is_associates_visible_to_client'])
        {?>
            <li><a href="#associates" class="last">Associates</a></li>
<?php } ?>
    </ul>

    <!-- FIRST TAB -->
    <div id="case-info">
        <table class="table info">
            <tr class="even">
                <th>Your Reference Number</th>
                <td><?php echo $case['reference_number'] ?></td>
            </tr>
            <?php
            // Links for related cases
            $related_cases_links = array();
            if (check_array($related_cases))
            {
                foreach ($related_cases as $related_case)
                {
                    $related_cases_links[] = anchor('/cases/view/' . $related_case['case_number'], $related_case['case_number'], 'class="related_case_link"');
                }
            }
            $related_cases_string = '';
            if (count($related_cases_links) > 0)
            {
                $related_cases_string = '(' . implode(', ', $related_cases_links) . ')';
            }
            ?>
            <tr class="odd">
                <th><?php echo $this->config->item('title_of_the_site') ?> Case Number</th>
                <td><?php echo $case['case_number'] ?>&nbsp;<?php echo $related_cases_string ?></td>
            </tr>
            <tr class="even">
                <th>Application Number</th>
                <td><?php echo $case['application_number'] ?></td>
            </tr>
            <tr class="odd">
                <th>Application Title</th>
                <td><?php echo $case['application_title'] ?></td>
            </tr>
            <tr class="even">
                <th>Applicant</th>
                <td><?php echo $case['applicant'] ?></td>
            </tr>

            <tr class="odd">
                <th>Case Type</th>
                <td><?php echo $case['case_type'] ?></td>
            </tr>
            <?php
            if ($case['case_type_id'] != '1')
            {
                ?>
                <tr class="even">
                    <th>Filing deadline</th>
                    <td><?php echo date($this->config->item('client_date_format') , strtotime($case['filing_deadline'])) ?></td>
                </tr>
<?php
} else
{
    ?>
                <tr class="even">
                    <th>30 month filing deadline</th>
                    <td><?php echo date($this->config->item('client_date_format') , strtotime($case['30_month_filing_deadline'])); ?></td>
                </tr>

                <tr class="odd">
                    <th>31 month filing deadline</th>
                    <td><?php echo date($this->config->item('client_date_format') , strtotime($case['31_month_filing_deadline'])); ?></td>
                </tr>
                    <?php } ?>
            <tr>

                <?php $estimate_filling = 'Estimated regions';
                if($case['is_intake'] =='1'){
   $estimate_filling = 'Filing regions';
}?>
                <th><?php echo $estimate_filling; ?></th>
                <td class="flags">
<?php if (check_array($case_countries)): ?>
    <?php foreach ($case_countries as $country): ?>
                            <div class="flag-box">
                                <div class="flag-image-block">
                                    <img src="/client/<?= $country['flag_image'] ?>">
                                </div>
                                <div><?= $country['country'] ?></div>
                            </div>
    <?php endforeach ?>
<?php endif ?>
                    <div class="clear"></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- SECOND TAB IS HIDDEN FOR INTAKE  -->
    <?php if (!$case['is_intake']):
        $GROUP = array(
            "allowed" => array(
                'estimating-estimate',
                'estimating',
                'pending-approval',
                'estimating-reestimate'
            ),
            "approved" => array(
                'active',
                'pending-intake' ,
                'completed'
            )
        );?>
        <style>
            <?php if (is_int(array_search($case['common_status'], $GROUP["allowed"])) && $case["estimate_available_for_client"] == 1) { ?>
            .estimate_0 {
                width: ;
            }
            .estimate_1 {
                width: 220px;
            }
            .estimate_2 {
                width: 100px;
            }
            .estimate_3 {
                width: 70px;
            }
            .estimate_4 {
                width: ;
            }
            .estimate_5 {
                width: 110px;
            }
            .estimate_6 {
                width: 140px;
            }
            <?php } else {  ?>
            .estimate_1 {
                width: 320px;
            }
            .estimate_2 {
                width: 130px;
            }
            .estimate_3 {
                width: 130px;
            }
            .estimate_4 {
                width: 130px;
            }
            .estimate_5 {
                width: 210px;
            }

            <?php } ?>
        </style>
        <div id="estimate">
                        <?php $estimate_total = 0; // Estimate total value ?>
            <table class="table" id="estimate-form">
                <thead>
                    <tr>
    <?php if (is_int(array_search($case['common_status'], $GROUP["allowed"])) && $case["estimate_available_for_client"] == 1): ?>
                            <th class="estimate_0"></th>
    <?php endif; ?>
                        <th class="estimate_1">Country</th>
                        <th class="estimate_2">Filing Fee</th>
                        <th class="estimate_3">Official Fee</th>
                        <th class="estimate_4">Translation Fee</th>
                        <th class="estimate_5">Total</th>
    <?php if (is_int(array_search($case['common_status'], $GROUP["allowed"])) && $case["estimate_available_for_client"] == 1): ?>
                            <th class="estimate_6">Approve by</th>
                    <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (check_array($countries_fees['countries'])): ?>
                        <?php
                            if ($case['case_currency'] == 'euro') {
                                $currency_sign = '&euro;';
                            } else {
                                $currency_sign = '$';
                            }
                        ?>

                        <?php foreach ($countries_fees['countries'] as $k => $fee): ?>
                            <?php

                            $country_total = 0;
                            $row_class = ($k & 1) ? 'odd' : 'even';
                            if ($fee['parent_id']) {
                                $disabled = 'disabled="disabled"';
                            } else {
                                $disabled = '';
                            }

                            $checkbox_class = 'blue';
                            // Check country filing deadline
                            if (!empty($fee['country_filing_deadline']))
                            {
                                if (time() > strtotime($fee['country_filing_deadline']))
                                {
                                    $row_class = 'dark_grey';
                                    $disabled = 'disabled="disabled"';
                                    $checkbox_class = 'gray';
                                    $is_deadline_past_already = true;
                                }
                            }
                            ?>

                            <tr class="<?php echo $row_class ?>">

                                <?php
                                if (
                                        is_int(array_search($case['common_status'], $GROUP["allowed"]))
                                        && $case["estimate_available_for_client"] == 1
                                ):
                                    ?>
                                    <td class="td_checkbox td_1_1">
                                        <input class="estimate_entry <?php echo $checkbox_class ?> <?php if($fee['parent_id']) {echo 'sub_country_' . $fee['parent_id'] ;} ?>" name="Name" rel="<?php echo $fee['id'] ?>"  type="checkbox" value="<?php echo $fee['country_id'] ?>" <?php echo $disabled ?> />
                                    </td>
                                <?php endif; ?>
                                <?php
                                if (
                                        (is_int(array_search($case['common_status'], $GROUP["allowed"])) && $case["estimate_available_for_client"] == 1)
                                        || is_int(array_search($case['common_status'], $GROUP["approved"]))
                                ):
                                    ?>
                                    <td><?php echo $fee['country'] ?></td>
                                    <?php if(empty($is_deadline_past_already)) { ?>
                                    <td><?php echo $currency_sign . number_format($fee['result_filing_fee']) ?></td>
                                    <td><?php echo $currency_sign . number_format($fee['result_official_fee']) ?></td>
                                    <td><?php echo $currency_sign . number_format($fee['result_translation_fee']) ?></td>
                                    <td><?php echo $currency_sign . number_format($country_total = $fee['result_total']) ?></td>
                                    <?php } else { ?>
                                    <td colspan="4" style="padding: 2px;">
                                            <a href="<?php echo $fee['id'] ?>" class="<?php if($fee['past_deadline_notification_sent']) { echo 'already_sent';} else { echo 'send_deadline_notification';} ?>">This region is past the approval deadline. Click here to contact your <?php echo $this->config->item('title_of_the_site') ?> Representative</a>
                                    </td>
                                    <?php } ?>
            <?php else: ?>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                <?php endif; ?>

                            <?php if (is_int(array_search($case['common_status'], $GROUP["allowed"])) && $case["estimate_available_for_client"] == 1): ?>
                                    <td><?php echo ($fee['country_filing_deadline']?gmdate($this->config->item('client_date_format'),strtotime($fee['country_filing_deadline'].'UTC')):''); ?></td>
                            <?php endif; ?>

                            </tr>
            <?php $estimate_total += $country_total ?>
            <?php unset($is_deadline_past_already); ?>
        <?php endforeach ?>
    <?php endif; ?>

                    <tr class="search">
                        <td colspan=7 class="td_search">
                        
                        
                            <?php
						    if ($case["estimate_available_for_client"] != 1 && !is_int(array_search($case['common_status'], $GROUP["approved"]))):
						        ?>
						                <div class="notify inline warning">
						                    <h4>Processing</h4>
						                    <div class="message">
						                        We are reviewing your application.
						                        Your <?php echo $this->config->item('title_of_the_site') ?> case number is <?php echo $case['case_number'] ?>.
						                        You will be notified when the estimate is completed.
						                    </div>
						                </div>
						            <?php elseif (is_int(array_search($case['common_status'], $GROUP["approved"]))): ?>
						                <div class="notify success inline">
						                    <h4>Approved!</h4>
						                    <div class="message">The above estimate was approved on <?php echo date('l F dS, Y', strtotime($case['approved_at'])) ?></div>
						                </div>
						    <?php endif ?>
                            <?php if (is_int(array_search($case['common_status'], $GROUP["allowed"])) && $case["estimate_available_for_client"] == 1): ?>
						        <a href="#" id="check_all" class="check_all"></a>
						    <?php endif; ?>
                            
                            <div class="container">
                                <div class="total">TOTAL:</div>
                                <div class="input">
    <?php if ($case["estimate_available_for_client"] != 1 && !is_int(array_search($case['common_status'], $GROUP["approved"]))) $estimate_total = ""; ?>
                                    <input class="input_little" name="Name" type="text" value="<?= ($estimate_total ? $currency_sign . number_format($countries_fees['bottom_all_total']) : '') ?>" disabled="disabled">
                                </div>
                            </div>
                        </td>
                    </tr>
    <?php if ($case["estimate_available_for_client"] == 1 && !is_int(array_search($case['common_status'], $GROUP["approved"]))): ?>
                        <tr class="buttons">
                            <td colspan=7 class="td_buttons">
                                <div class="container">
                                    <button class="button white my-tooltip"
                                    	id="edit-estimate"
                                    	title="Modify existing estimated costs by adding or removing filing regions for re-estimate">
                                    	Edit
                                    </button>
                                    <button class="button  my-tooltip"
                                    	id="estimate-submit"
                                    	title="Finalize the estimated costs by selecting the regions you wish to file for">
                                    	Approve
                                    </button>
                                </div>
                            </td>
                        </tr>
            <?php endif; ?>
                </tbody>
            </table>

            <!-- Reestimate form -->
            <div id="reestimate-form" style="display: none;">
                    <?php $case_countries_arr = array() ?>
    <?php if (check_array($case_countries)): ?>
    				<h4 class="my-tooltip" title="Remove unwanted regions in current regions for re-estimate">Current regions</h4>
                    <div class="flags case selected">
        <?php foreach ($case_countries as $country): ?>

                                    <?php $case_countries_arr[] = $country['id'] ?>
                            <div class="flag flag-box" id="selected-store-<?php echo $country['id'] ?>">
                                <div class="flag-img">
                                    <a class="remove flag_remove_activator" href="#"></a>
                                    <img src="/client/<?= $country['flag_image'] ?>">
                            <?php echo form_hidden('countries[]', $country['id']) ?>
                                </div>
                                <div class="a-center"><?php echo $country['country'] ?></div>
                            </div>
        <?php endforeach ?>
                        <div class="flag-box-noflag" style="display: none;">
                            <div class="flag-noflag flag-img">
                                <img src="/client/assets/images/flags/noflag-default.png">
                            </div>
                            <div>Select country...</div>
                        </div>
                    </div>
    <?php endif ?>
                <div class="clear"></div>

                <div class="container buttons">
                    <div class="search_container">
                        <label>Additional Regions</label>
                        <input id="new_country_input"  class="flags_autocomplete" placeholder="Search..." class="search" />
                    </div>
                    <div class="buttons_container">
                        <button class="button white my-tooltip"
	                        id="back-estimate"
	                        title="Omit any changes made and return to previous screen">
                        	Back
                        </button>
                        <button class="button my-tooltip"
	                        id="reestimate-submit"
	                        title="Submit the proposed regions for estimation">
                        	Re-estimate
                        </button>
                    </div>
                    <div class="clear"></div>
                </div>


                <!-- Common countries list -->
                <div class="clear"></div>
                <h4 class="my-tooltip" title="Select Additional regions for estimation">Common Countries</h4>
                    <?php if (check_array($common_countries)): ?>
                    <div class="flags store">
        <?php $case_countries_arr = array_unique($case_countries_arr) ?>
        <?php foreach ($common_countries as $country): ?>
            <?php if (!in_array($country['id'], array_unique($case_countries_arr))): ?>
                                <div class="flag flag-box common-country" id="store-<?php echo $country['id'] ?>">
                                    <div class="flag-img">
                                        <a data-id="<?= $country['id'] ?>" class="add flag_add_activator" href="#"></a>
                                        <img src="/client/<?= $country['flag_image'] ?>">
                                    </div>
                                    <div><?php echo $country['country'] ?></div>
                                </div>
                        <?php endif ?>
        <?php endforeach ?>
                        <div class="clear"></div>
                    </div>
    <?php endif ?>
            </div>
        </div>
<?php endif;
if($case['is_intake']){?>
<input style="display: none;" id="new_country_input"  class="flags_autocomplete" placeholder="Search..." class="search" />
<?php }?>

    <!--  THIRD TAB -->
    <div id="files">
    
		<div class="expand tb_switch my-tooltip"
			ref="switch_client_files"
			title="Expand tab to view relevant case files">
			Client Files
		</div>
    
        <table class="table files" id="switch_client_files">

            <tbody class="files_table" >

                <?php if (check_array($client_files)): ?>
                    <?php foreach ($client_files as $k => $file): ?>
                        <?php
                        $row_class = ($k & 1) ? 'odd' : 'even';
                        $ext = strtolower(substr($file['filename'], strrpos($file['filename'], '.') + 1));
                        if (file_exists(FCPATH . 'assets/images/file_types/type_' . $ext . '.png'))
                        {
                            $type_class = 'type_' . $ext;
                        } else
                        {
                            $type_class = 'type_def';
                        }
                        ?>
                        <tr class="<?php echo $row_class ?>">
                            <td class="b p90 without-border-r <?= $type_class ?>">
                                <a href="<?= base_url() ?>cases/view_file/<?php echo $file['id']; ?>">
                                <?php 
                                	if(strlen($file['filename'])>53){
                                		$file['filename'] = substr($file['filename'], 0, 53).'...';
                                	}
                                ?>
        <?php echo $file['filename'] ?>
                                </a>
                            </td>
                            <td class="b without-border-l" ><?php echo $file['name'] ?></td>
                            <td class="b right without-border-l file_download">
                                <a href="<?= base_url() ?>cases/view_file/<?php echo $file['id']; ?>">
                        <?= format_bytes($file['filesize']) ?>
                                </a>
                            </td>
                        </tr>
    <?php endforeach ?>
<?php endif ?>
            </tbody>
        </table>
		
		<div class="expand tb_switch my-tooltip" ref="switch_case_files" title="Expand tab to view relevant case files">
			Documents
		</div>
		
        <table class="table files"  id="switch_case_files">
            <tbody>
                <?php if (check_array($document_files)): ?>
                    <?php foreach ($document_files as $k => $file): ?>
                        <?php
                        $row_class = ($k & 1) ? 'odd' : 'even';
                        $ext = strtolower(substr($file['filename'], strrpos($file['filename'], '.') + 1));
                        if (file_exists(FCPATH . 'assets/images/file_types/type_' . $ext . '.png'))
                        {
                            $type_class = 'type_' . $ext;
                        } else
                        {
                            $type_class = 'type_def';
                        }
                        ?>
                        <tr class="<?php echo $row_class ?>">
                            <td class="b p90 without-border-r <?= $type_class ?>">
                            	<?php 
                                	if(strlen($file['filename'])>53){
                                		$file['filename'] = substr($file['filename'], 0, 53).'...';
                                	}
                                ?>
                                <a href="<?= base_url() ?>cases/view_file/<?php echo $file['id']; ?>"><?php echo $file['filename'] ?></a>

                            </td>
                            <td class="b without-border-l" ><?php echo $file['name'] ?></td>
                            <td class="b right without-border-l file_download">
                                <a  href="<?= base_url() ?>cases/view_file/<?php echo $file['id']; ?>">
                        <?= format_bytes($file['filesize']) ?>
                                </a>
                            </td>
                        </tr>
            <?php endforeach; ?>
        <?php endif; ?>
            </tbody>
        </table>

<?php
if ($case['common_status'] == 'active' || $case['common_status'] == 'completed')
{
    ?>
                        <div class="expand">
                            <span class="tb_header tb_switch my-tooltip" ref="filling_confirmation_tbl" title="Expand tab to view relevant case files">
                            	Filing Confirmation
                            </span>
                            <?php
                            if ($filing_files && !empty($is_have_files)) { ?>

                                <span class="file_download">
                                    <a href="/client/cases/create_zip/<? echo $case_id; ?> ">Download all confirmation reports</a>
                                </span>
                    <?php } ?>
                    		<div class="clear"></div>
                        </div>
            
            
            <table class="table countries"  id="filling_confirmation_tbl">
                
                <tbody>
                    <?php if (check_array($case_countries)): ?>
                        <?php foreach ($case_countries as $k => $country): ?>
                            <?php
                            $row_class = ($k & 1) ? 'odd' : 'even';
                            if ($country['files'])
                            {
                                $switch_class = "tb_switch";
                                $ref = "ref='con_files_" . $country['id'] . "'";
                            } else
                            {
                                $switch_class = $ref = "";
                            }
                            ?>
                            <tr class="<?php echo $row_class ?>">
                                <td class="flag flags">
                                    <img src="/client/<?= $country['flag_image'] ?>">
                                </td>

                                <td class="con_name <?= $switch_class ?>" <?= $ref ?>>
            <? echo $country['country']; ?>
                                </td>

                                <td class="b right my-tooltip without-border-l <?php echo($country['files'] ? 'file_download' : 'file_download_not_ready') ?>"
                                	title="<?= $country['files'] ? 'Filing confirmation is available for download' : 'Filing confirmation for this region is not yet available. Please be patient' ?>">

                                    <a <?php if(!$country['files']) { ?> onclick="return false;" <?php } ?>href="/client/cases/create_zip/<? echo $case_id; ?>/<? echo $country['id']; ?> ">&nbsp;</a>

                                </td>

                            </tr>
                                            <?php if ($country['files']): ?>
                                <tr class="attachments">
                                    <td colspan="3" id="con_files_<? echo $country['id']; ?>" >
                                        <table class="table files country_files">
                                            <tbody>
                                                <?php
                                                foreach ($country['files'] as $file)
                                                {
                                                    $ext = strtolower(substr($file['filename'], strrpos($file['filename'], '.') + 1));
                                                    if (file_exists(FCPATH . 'assets/images/file_types/type_' . $ext . '.png'))
                                                    {
                                                        $type_class = 'type_' . $ext;
                                                    } else
                                                    {
                                                        $type_class = 'type_def';
                                                    }
                                                    ?>
                                                    <tr class="even">
                                                        <td class="b p90 without-border-r <?= $type_class ?>">
                                                        	<?php 
							                                	if(strlen($file['filename'])>53){
							                                		$file['filename'] = substr($file['filename'], 0, 53).'...';
							                                	}
							                                ?>
                                                            <a href="<?= base_url() ?>cases/view_file/<?php echo $file['id']; ?>"><?php echo $file['filename'] ?></a>
                                                        </td>
                                                        <td class="b without-border-l" ><?php echo $file['name'] ?></td>
                                                        <td class="b right without-border-l file_download">
                                                            <a href="<?= base_url() ?>cases/view_file/<?php echo $file['id']; ?>">
                    <?php echo format_bytes($file['filesize']); ?>
                                                            </a>
                                                        </td>
                                                    </tr>
                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
            <?php endif ?>
        <?php endforeach; ?>
    <?php endif; ?>
                </tbody>
            </table>
<?php } ?>

        <script id="templateUploader" type="text/x-jquery-tmpl">
            <div class="qq-uploader">
                <div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>
                <div class="qq-upload-button">
                    <div class="middle">Upload</div>
                    <div class="b small">Additional Files</div>
                </div>
                <ul class="qq-upload-list"></ul>
            </div>
            </script>

            <script id="templateUploadedFile" type="text/x-jquery-tmpl">
                <tr  class="file file-${id} ${ext}">
                    <td class="b" colspan="3">
                        <div class="file-info" style="width:55%">
                            <span class="file-icon"></span>
                            ${fileName}
                        </div>
                        <select style="display:none;" class="file-category" name="attachments_categories[${id}]">
                            <option value="18">Select category</option>
<?php
foreach ($fileTypes as $ft)
{
    ?>
                                <option value="<?= $ft['id'] ?>"><?= $ft['name'] ?></option>
<?php } ?>
                        </select>
						<a href="#" class="remove file-remove remove_file_activator my-tooltip" title="Remove Case File">
						</a>
                        <div class="file-progress-${id}"></div>
                        <div class="file-size">${total}</div>
                    </td>
                </tr>
                </script>
            </div>
<?php
if ($case['is_associates_visible_to_client'])
{
    ?>
                <div id="associates">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Contact</th>
                                <th>Associate Reference Number</th>
                            </tr>
                        </thead>
                        <tbody class="flags">
                            <?php if (check_array($case_associates)):

                            $k = 1;
                                 foreach ($case_associates as $associate):

                                    $row_class = ($k & 1) ? 'odd' : 'even';
                                    if (isset($associate['flag_image']))
                                    {
                                        $img_class = "";
                                        $img_path = $associate['flag_image'];
                                    } else
                                    {
                                           $img_class = "flag-noflag";
                                        $img_path = "assets/images/flags/noflag-default.png";
                                    }
//                                     $fa_data ='';
//                                     if( !empty($associate['name'])){
//                                         $fa_data = $associate['name'].'</br>';
//                                     }
//                                     if(!empty($associate['firm'])){
//                                         $fa_data .= $associate['firm'].'</br>';
//                                     }
//                                     if(!empty($associate['address'])){
//                                         $fa_data .= $associate['address'].'</br>';
//                                     }
//                                     if(!empty($associate['address2'])){
//                                         $fa_data .= $associate['address2'].'</br>';
//                                     }if(!empty($associate['phone'])){
//                                     $fa_data .= 'Tel:'.$associate['phone'].'</br>';
//                                     }
//                                     if(!empty($associate['fax'])){
//                                         $fa_data .= 'Fax:'.$associate['fax'].'</br>';
//                                     }
//                                     if(!empty($associate['email'])){
//                                         $fa_data .= 'Email:'.$associate['email'].'</br>';
//                                     }
//                                     if(!empty($associate['website'])){
//                                         $fa_data .= $associate['website'].'</br>';
//                                     }
                                    ?>
                                    <tr class="<?php echo $row_class ?>">
                                        <td>
                                            <div class="flag-box">
                                                <div class="flag-img <?= $img_class ?>">
                                                    <img src="/client/<?= $img_path ?>">
                                                </div>
                                                <div><?php echo isset($associate['country']) ? $associate['country'] : "" ?></div>
                                            </div>
                                        </td>
                                                                                <td><?php echo isset($associate['associate']) ? nl2br($associate['associate']) : "" ?></td>
<!--                                        <td>--><?php //echo $fa_data;?><!--</td>-->


                                        <td class="b"><?php echo isset($associate['reference_number']) ? nl2br($associate['reference_number']) : "" ?></td>
                                    </tr>
            <?php $k++ ?>
        <?php endforeach ?>
    <?php endif ?>
                        </tbody>
                    </table>
                </div>
<?php } ?>
        </div>
