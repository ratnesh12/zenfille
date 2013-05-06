<script type="text/javascript">
    Zenfile.uploadUrl = "<?= site_url('cases/upload') ?>/<?= $case['id']; ?>/?zenfile_sessid=";
    Zenfile.uploadUrl = Zenfile.uploadUrlSecure;
    Zenfile.uploadedRemoveUrl = '<?=site_url('request/remove_uploaded')?>';
    Zenfile.flashUploadButtonImage = "<?=site_url("assets/img/upload_sprite_wl2.png")?>";
$('.file-category').live('change', function() {
    var parent = $(this).parents(".file");
    var file_id = $("input",parent).val();
    var file_type_id = $("select",parent).val();
    $.post("<?php echo base_url(); ?>cases/set_file_type/", {file_id: file_id, file_type_id: file_type_id});

});
    related = true;
</script>
<div class="content_header new_case">
    <div class="title"><?=$_TEMPLATE['title']?></div>
</div>

<div id="form_loadholder">
    <h3 class="center">
        Form processing, please wait...
    </h3>
    <div class="center">
        <img src="<?=site_url('assets/img/loader.gif')?>" alt="Loading" />
    </div>
</div>

<form id="applicationForm" class="applicationForm validateThisForm" method="post" enctype="multipart/form-data" action="<?=site_url('request/save')?>">
    <input type="hidden" name="random" value="<?=md5(mt_rand(0,1000)*microtime())?>" />
    <input type="hidden" name="applicationFormPosted" value="1" />
    <input type="hidden" name="action" value="ajaxform_submit" />
    <input type="hidden" name="is_estimate" value="<?=$isEstimate?>" />
    <input type="hidden" name="application_type" class="application_type" value="<?=$case_type?>" />
    <input type="hidden" name="parent_case" value="<?=$parent_case?>" />
    <input type="hidden" name="case_number" value="<?=$case['case_number']?>" />
    <input type="hidden" name="case_id" value="<?=$case['id']?>" />
    <input type="hidden" class="customer_token" name="customer_token" value="<?=md5($this->session->userdata('client_user_id'))?>" />

    <div id="applicationFormSlider">

        <!--STEP 0-->
        <div class="fieldset step step-current" id="step1">
            <input type="hidden" name="is_intake" class="is_intake" id="is_intake" value="<?=$isIntake?>" />

            <div class="legend"><?=$_TEMPLATE['title']?></div>
            <h3>Is this for immediate processing or estimate?</h3>

            <a href="#" class="intake_estimate_activator" data-value="0">
                <div class="p">
                    <div class="in">Estimate</div>
                </div>
            </a>
            <a href="#" class="intake_estimate_activator" data-value="1">
                <div class="p">
                    <div class="in">Immediate Processing</div>
                </div>
            </a>
            <div class="bottom_nav">
                <a href="#review" class="back_to_review" style="display: none" onClick="stanlider.Show('next', 5);"><span>Back to Review</span></a>
            </div>
        </div>
        <!--STEP 2-->
        <div class="fieldset step" id="step2">
            <div class="legend"><?=$_TEMPLATE['title']?></div>
            <h3>What is your reference number? (optional):</h3>
            <div class="p" style="width: 390px;">
                <label>Case Reference #</label>
                <input name="reference_number" type="text" value="<?=$reference_number?>" />
            </div>
            <div class="bottom_nav">
                <a href="#back" class="back" onClick="stanlider.Show('back', 1);"><span>Back</span></a>
                <a href="#review" class="back_to_review" style="display: none" onClick="stanlider.Show('next', 5);"><span>Back to Review</span></a>
                <a href="#next" class="next" onClick="stanlider.Show('next', 3);"><span>Next</span></a>
                <div class="clear"></div>
            </div>
        </div>
        <!--STEP 3-->
        <div class="fieldset step" id="step3">
            <div class="legend"><?=$_TEMPLATE['title']?></div>
            <h3 class="hide_on_ep">Select your filing regions by clicking on flags:</h3>
            <h3 class="show_on_ep">Search for filing regions below or click "Select All EP Countries":</h3>
            <h4>Selected regions:</h4>
            <div class="flags selected">
                <?php
                if (count($selected_countries))
                {
                    foreach( $selected_countries as $sc)
                    {
                        ?>
                        <div class="flag-box">
                                <div class="flag-img">
                                    <img src="/client/<?=$sc['flag_image'] ?>">
                                    <a class="remove flag_remove_activator" href="#"></a>
                                    <input type="hidden" name="countries[]" value="<?= $sc['country_id'] ?>" />
                                </div>
                                <div><?= $sc['country'] ?></div>
                            </div>
                        <?php
                    }
                }
                else
                {
                    ?>
                    <div class="flag-box-noflag">
                        <div class="flag-noflag flag-img">
							<img src="/client/assets/images/flags/noflag-default.png">
                        </div>
                        <div>Select country...</div>
                    </div>
                    <?php
                }
                ?>

            </div>
            <div class="clear"></div>

            <div class="select_all_regions">
                <a href="#" class="select_all_countries_activator">
                    <div class="p">
                        <div>Select All EP Countries</div>
                    </div>
                </a>
            </div>
            <div class="common_regions">
                <h4>Common regions:</h4>
                <div>
                    <div class="flags common show_on_pct">
                        <?php
                        foreach( $common_countries['pct'] as $cc)
                        {
                            ?>
                            <div class="flag-box" id="pct-<?php echo $cc['country_id'] ?>">
                                    <div class="flag-img">
                                        <img src="/client/<?=$cc['flag_image']?>">
                                        <a data-id="<?= $cc['country_id'] ?>" class="add flag_add_activator" href="#"></a>
                                    </div>
                                    <div><?= $cc['country'] ?></div>
                                </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="flags common show_on_ep">
                        <?php
                        foreach( $common_countries['ep'] as $cc)
                        {
                            ?>
                            <div class="flag-box" id="ep-<?php echo $cc['country_id'] ?>">
                                    <div class="flag-img">
                                        <img src="/client/<?=$cc['flag_image']?>">
                                        <a data-id="<?= $cc['country_id'] ?>" class="add flag_add_activator" href="#"></a>
                                    </div>
                                    <div><?= $cc['country'] ?></div>
                                </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="flags common show_on_direct">
                        <?php
                        foreach( $common_countries['direct'] as $cc)
                        {
                            ?>
                            <div class="flag-box" id="pct-<?php echo $cc['country_id'] ?>">
                                    <div class="flag-img">
                                        <img src="/client/<?=$cc['flag_image']?>">
                                        <a data-id="<?= $cc['country_id'] ?>" class="add flag_add_activator" href="#"></a>
                                    </div>
                                    <div><?= $cc['country'] ?></div>
                                </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="p">
                <label class="hide_on_ep">Other Regions:</label>
                <label class="show_on_ep">Filing Regions:</label>
                <input class="flags_autocomplete" placeholder="Search here to add regions" type="text"  />
            </div>
            <div class="bottom_nav">
                <a href="#back" class="back" onClick="stanlider.Show('back', 2);"><span>Back</span></a>
                <a href="#review" class="back_to_review" style="display: none" onClick="stanlider.Show('next', 5);"><span>Back to Review</span></a>
                <a href="#next" class="next" onClick="stanlider.Show('next', 11);"><span>Next</span></a>
                <div class="clear"></div>
            </div>

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
        </div>
        <!--STEP 4-->
        <div class="fieldset step" id="step4">
            <div class="legend"><?=$_TEMPLATE['title']?></div>
            <h3>Any special instructions for your <?php echo $this->config->item('title_of_the_site') ?> project manager?</h3>

            <div class="special">
                <div><label>Your message</label></div>
                <textarea name="special_instructions"><?=$special_instructions?></textarea>

                <div class="clear"></div>
            </div>
            <div class="bottom_nav">
                <a href="#back" class="back" onClick="stanlider.Show('back', 3);"><span>Back</span></a>
                <a href="#review" class="review" onClick="stanlider.Show('next', 5);"><span>Review</span></a>
                <div class="clear"></div>
            </div>
        </div>
                            <!--STEP 5-->
                            <div class="fieldset step" id="step5">
                                <div class="legend"><?= $_TEMPLATE['title'] ?></div>
                                <h3>You've selected the following parameters for this case. Please review them before submitting.</h3>
                                <div class="stan_p">
                                    <label>Case Type:</label> <span id="PREVIEW_application_type" class="review_text" onClick="stanlider.anyPage('back',5,1)"></span>
                                </div>
                                <div class="stan_p">
                                    <label>Reference Number:</label>  <span id="PREVIEW_application_number" class="review_text" onClick="stanlider.anyPage('back',5,2);"></span>
                                </div>
                                <div class="stan_p">
                                    <label>Selected Regions:</label>  <span id="PREVIEW_regions" class="review_text" onClick="stanlider.anyPage('back',5,3);"></span>
                                </div>


                                <div class="stan_p" onClick="stanlider.anyPage('back',5,11);">
                                    <label>Region Reference Numbers</label>  <span id="PREVIEW_regions" class="review_text"></span>
                                </div>
                                <div class="bottom_nav">
                                    <a href="#back" class="back" onClick="stanlider.Show('back', 4);"><span>Back</span></a>
                                    <button class="next" type="submit"><span>Submit</span></button>
                                    <div class="clear"></div>
                                </div>
                            </div>

        <!--STEP 11-->
        <div class="fieldset step" id="step11">
            <div class="legend">New Estimate</div>
            <h3>Please provide the reference numbers you'd like to use for each of your chosen filing regions</h3>
            <div id="flag_block">
                <?php foreach($all_countries as $country) { ?>
                    <div class="reference_country_id_<?php echo $country['id'] ?> disabled" style="margin-left: 10px;">
                        <div style="width:80px; float: left; font-size: 14px;">
                            <img src="/client/<?php echo $country['flag_image'] ?>" style="display: block; float: left;" width="50px">
                            <div style="clear: both;"></div>
                            <?php echo $country['country'] ?>
                        </div>
                        <div class="p" style="clear: none; float: left; clear: none; margin: 8px 0 0 10px;"><label style="margin-right: 0px;">Reference number</label><input class="reference_numbers" type="text" value="" name="reference_number_for_country_<?php echo $country['id'] ?>">
                        </div>
                        <div class="clear"></div>
                    </div>
                <?php } ?>
            </div>
            <div class="clear"></div>
            <div class="bottom_nav">
                <a href="#back" class="back" onClick="stanlider.Show('back', 3);"><span>Back</span></a>
                <a href="#review" class="back_to_review" style="display: none" onClick="stanlider.Show('next', 5);"><span>Back to Review</span></a>
                <a href="#next" class="next" onClick="stanlider.Show('next', 4);"><span>Next</span></a>
            </div>
        </div>

        <!--FINISH SUCCESS STEP-->
        <div class="fieldset step" id="final">
            <div class="legend"><?=$_TEMPLATE['title']?></div>
            <h3>Your case has been sent to our project management team,
                and your <?php echo $this->config->item('title_of_the_site') ?> reference number is <span class="new_case_id"></span>. We will be in
                touch shortly to confirm receipt of these instructions!</h3>
            <div class="img success"></div>
        </div>
    </div>
</form>

