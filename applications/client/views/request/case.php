<script type="text/javascript" src="/client/assets/js/uploaders.zenfile.js"></script>
<script type="text/javascript">
    Zenfile.uploadUrlSecure = '<?= site_url('request/upload/?zen_session =' . ( isset($_COOKIE['zen_session']) ? $_COOKIE['zen_session'] : '' )) ?>';
    Zenfile.uploadUrl = Zenfile.uploadUrlSecure;
    Zenfile.uploadedRemoveUrl = '<?= site_url('request/remove_uploaded') ?>';
    Zenfile.flashUploadButtonImage = "<?= site_url("assets/img/upload_sprite_wl2.png") ?>";
    Zenfile.parseRemoteUrl = "<?= site_url("wp_engine/load_case_data") ?>";
    reference_numbers = new Array();
    related = false;

</script>
<div class="content_header new_case">
    <div class="title"><?= $_TEMPLATE['title'] ?></div>
</div>

<div id="form_loadholder">
    <h3 class="center">
        Form processing, please wait...
    </h3>
    <div class="center">
        <img src="<?= site_url('assets/img/loader.gif') ?>" alt="Loading" />
    </div>
</div>

<form id="applicationForm" class="applicationForm validateThisForm" method="post" enctype="multipart/form-data" action="<?= site_url('request/save') ?>">
    <input type="hidden" name="random" value="<?= md5(mt_rand(0, 1000) * microtime()) ?>" />
    <input type="hidden" name="applicationFormPosted" value="1" />
    <input type="hidden" name="action" value="ajaxform_submit" />
    <input type="hidden" name="is_estimate" value="<?= $isEstimate ?>" />

    <input type="hidden" class="customer_token" name="customer_token" value="<?= md5($this->session->userdata('client_user_id')) ?>" />

    <div id="applicationFormSlider">
    <!--STEP 0-->
    <div class="fieldset step step-current" id="step0">
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
        <!--STEP 1-->
        <div class="fieldset step" id="step1">
            <input type="hidden" name="application_type" class="application_type" value="<?= $case_type ?>" />
            <input type="hidden" name="is_intake" class="is_intake" value="<?= $isIntake ?>" />
            <div class="legend"><?= $_TEMPLATE['title'] ?></div>

            <h3>Select your application type:</h3>

            <a href="#" class="countries_activator" data-jslist="direct">
                <div class="p">
                    <div class="in <?= $direct_filing_checked ?>">Direct Filing</div>
                </div>
            </a>
            <a href="#" class="countries_activator" data-jslist="pct">
                <div class="p">
                    <div class="in <?= $pct_checked ?>">PCT National Phase Entry</div>
                </div>
            </a>
            <a href="#" class="countries_activator" data-jslist="ep">
                <div class="p">
                    <div class="in <?= $ep_checked ?>">EP Validation</div>
                </div>
            </a>
            
            <div class="bottom_nav">
                <a href="#back" class="back" onClick="stanlider.Show('back', 0);"><span>Back</span></a>
                <a href="#review" class="back_to_review" style="display: none" onClick="stanlider.Show('next', 10);"><span>Back to Review</span></a>
                <div class="clear"></div>
            </div>
            
        </div>

        <!--STEP 2-->
        <div class="fieldset step" id="step2">
            <div class="legend"><?= $_TEMPLATE['title'] ?></div>
            <h3>Please enter your application number:</h3>
            <div class="p">
                <label>Application # <span class="asterisk">*</span></label>
                <input name="application_number" class="parse_application_activator" type="text" value="<?= $application_number ?>" />
                <div class="additionalCaseInfo"></div>
                <script id="templateAdditionalCaseInfo" type="text/x-jquery-tmpl">
                </script>
            </div>
            <div class="notice">
                <div class="application_number_example direct">Ex: US 13/123,123</div>
                <div class="application_number_example pct">Ex: PCT/US2011/020654 or WO/2011/087979</div>
                <div class="application_number_example ep">Ex: EP10762370</div>
            </div>
            <div class="bottom_nav">
                <a href="#back" class="back" onClick="stanlider.Show('back', 1);"><span>Back</span></a>
                <a href="#review" class="back_to_review" style="display: none" onClick="stanlider.Show('next', 10);"><span>Back to Review</span></a>
                <a href="#next" class="next" onClick="stanlider.Show('next', 3);"><span>Next</span></a>

                <div class="clear"></div>
            </div>
        </div>

        <!--STEP 3-->
        <div class="fieldset step" id="step3">
            <div class="legend"><?= $_TEMPLATE['title'] ?></div>
            <h3>Fill out and review your application information below:</h3>

            <div class="notify info inline hidden pleaseStandByNotify">
                <h4>Collecting information about your application. Please stand by. </h4>
                <div class="message"> Don't want to wait? Click <a style="background: none; padding-left: 0;" href="#" class="close custom">here</a> to enter information yourself.</div>
            </div>

            <div class="p">
                <label>Application title:</label>
                <input name="application_title" class="application_title_field" type="text" value="<?= $application_title ?>"  />
            </div>
            <div class="p">
                <label>Applicant:<span style="display:none;" class="asterisk for_direct">*</span></label>
                <input name="applicant" class="applicant_field" type="text" value="<?= $applicant ?>" />
            </div>
            <div class="p">
                <label>Filing deadline:<span style="display:none;" class="asterisk for_direct">*</span></label>
                <input title="30 months after first priority date" class="datepicker filing_deadline_field my-tooltip" name="deadline" value="<?= $filing_deadline ?>" type="text" autocomplete="off" readonly="readonly" />
            </div>
            <div class="bottom_nav">
                <a href="#back" class="back" onClick="stanlider.Show('back', 2);"><span>Back</span></a>
                <a href="#review" class="back_to_review" style="display: none" onClick="stanlider.Show('next', 10);"><span>Back to Review</span></a>
                <a href="#next" class="next" onClick="stanlider.Show('next', 4);"><span>Next</span></a>

                <div class="clear"></div>
            </div>
        </div>


        <!--STEP 4-->
        <div class="fieldset step" id="step4">
            <div class="legend"><?= $_TEMPLATE['title'] ?></div>
            <h3>What is your reference number? (optional):</h3>
            <div class="p" style="width: 390px;">
                <label>Case Reference #</label>
                <input class="my-tooltip" title="Enter a personal reference number for your own convenience" name="reference_number" type="text" value="<?= $reference_number ?>" />
            </div>
            <div class="bottom_nav">
                <a href="#back" class="back" onClick="stanlider.Show('back', 3);"><span>Back</span></a>
                <a href="#next" class="next" onClick="stanlider.Show('next',5);"><span>Next</span></a>
                <div class="clear"></div>
            </div>
        </div>


        <!--STEP 5-->
        <div class="fieldset step" id="step5">
            <div class="legend"><?= $_TEMPLATE['title'] ?></div>
            <h3 class="my-tooltip" title="welcome emails, intake emails and estimates, filing instructions, and confirmation email">
            	In addition to yourself, who would you like to receive upcoming emails?
            </h3>

            <div class="additional_contacts">
                <?php
                if (!empty($additional_contacts)):
                    foreach ($additional_contacts as $contact):
                        if (isset($contact["email"]) && preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $contact["email"])):
                            ?>
                            <div class="additional_contact">
                                <a href="#" class="remove_additional_contact_activator">
                                    <img src="<?= site_url('assets/img/remove_middle.png') ?>" alt="remove" />
                                </a>
                                <div class="p">
                                    <label>Contact</label>
                                    <input name="addtional_contacts[]" type="text" value="<?php echo $contact["email"]; ?>" />
                                </div>
                            </div>
                            <?php
                        endif;
                    endforeach;
                endif;
                ?>
                <div class="clear"></div>
            </div>
            <div class="additional_contact adder">
                <a href="#" class="add_additional_contact_activator">
                    <img src="<?= site_url('assets/img/add_middle.png') ?>" alt="add" />
                </a>
                <div class="p">
                    <label>Contact</label>
                    <input name="addtional_contacts[]" type="email" value="" />
                </div>
                <div class="clear"></div>
            </div>

            <div class="bottom_nav">
                <a href="#back" class="back" onClick="if(validate_contacts()){stanlider.Show('back', 4);}"><span>Back</span></a>
                <a href="#next" class="next" onClick="if(validate_contacts()){stanlider.Show('next',  <?php if(isset($isEstimate) && $isEstimate==1) echo 7 ; else echo 6; ?>);}"><span>Next</span></a>
                <div class="clear"></div>
            </div>

            <script id="templateAdditionalContacts" type="text/x-jquery-tmpl">
                <div class="additional_contact hidden">
                    <a href="#" class="remove_additional_contact_activator">
                        <img src="<?= site_url('assets/img/remove_middle.png') ?>" alt="remove" />
                    </a>
                    <div class="p">
                        <label>Contact</label>
                        <input name="addtional_contacts[]" type="text" value="${contact}" />
                    </div>
                </div>
                </script>
            </div>


            <!--STEP 6-->
            <div class="fieldset step <?php if(isset($isEstimate) && $isEstimate==1) echo 'HIDDEN_IMPORTANT'; ?>" id="step6">
                <div class="legend"><?= $_TEMPLATE['title'] ?></div>
                <h3>Would you like to receive notification each time a filing
                    receipt or associate confirmation report is uploaded to the
                    portal? If not, a full summary will be sent at the completion
                    of the case.
                </h3>

                <div class="applicationFormRadio_panel">
                    <input name="notification_each_time" id="notification_each_time" type="hidden" value="<?= $notification_each_time ?>"/>
                    <a href="#" class="applicationFormRadio yes <?= $notification_each_time == 'yes' ? 'pressed' : '' ?>">
                        Yes
                    </a>
                    <a href="#" class="applicationFormRadio no  <?= $notification_each_time == 'no' ? 'pressed' : '' ?>">
                        No
                    </a>

                    <div class="clear"></div>
                </div>
                <div class="bottom_nav">
                    <a href="#back" class="back" onClick="stanlider.Show('back', 5);"><span>Back</span></a>
                    <div class="clear"></div>
                </div>
            </div>


            <!--STEP 7-->
            <div class="fieldset step" id="step7">
                <div class="legend"><?= $_TEMPLATE['title'] ?></div>
                <h3 class="hide_on_ep">Select your filing regions by clicking on flags:</h3>
                <h3 class="show_on_ep">Search for filing regions below or click "Select All EP Countries":</h3>
                <h4>Selected regions:<span style="display:none;" class="asterisk for_direct">*</span></h4>
                <div class="flags selected">
                    <?php
                    if (count($selected_countries))
                    {
                        foreach ($selected_countries as $sc)
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
                    } else
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

                <div class="common_regions">
                    <h4>Common regions:</h4>
                    <div>
                        <div class="flags common show_on_pct">
                            <?php
                            foreach ($common_countries['pct'] as $cc)
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
                        <div class="flags common show_on_direct">
                            <?php
                            foreach ($common_countries['direct'] as $cc)
                            {
                                ?>
                                <div class="flag-box" id="direct-<?php echo $cc['country_id'] ?>">
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
                            foreach ($common_countries['ep'] as $cc)
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
                        <div class="clear"></div>
                    </div>
                </div>

                <div class="select_all_regions">
                    <a href="#" class="select_all_countries_activator">
                        <div class="p">
                            <div>Select All EP Countries</div>
                        </div>
                    </a>
                </div>
                <div class="clear"></div>
                <div class="p">
                    <label class="hide_on_ep">Other Regions:</label>
                    <label class="show_on_ep">Regions:</label>
                    <input id="new_country_input" class="flags_autocomplete" placeholder="Search here to add regions" type="text"  />
                </div>
                <div class="bottom_nav">
                    <a href="#back" class="back" onClick="stanlider.Show('back', <?php if(isset($isEstimate) && $isEstimate==1) echo 5 ; else echo 6; ?>);"><span>Back</span></a>
                    <a href="#review" class="back_to_review" style="display: none" onClick="stanlider.Show('next', 10);"><span>Back to Review</span></a>
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

                <!--STEP 8-->
                <div class="fieldset step" id="step8">
                    <div class="legend"><?= $_TEMPLATE['title'] ?></div>
                    <h3>Please upload your application here:</h3>
                    <?php
                    if(preg_match('/msie [2-9]/i',$_SERVER['HTTP_USER_AGENT'])) {?>
                        <div style='align:right;color:red;margin: 10px;'>Note:Uploading of ZIP archives is not supported if you are using Internet Explorer 8,9</div>
                        <?php }?>

                    <div class="uploader_block" style="display: none;">
                        <div id="file-uploader">
                            <input class="nofloat" id="upload_file" name="qqfile" type="file"  />
                        </div>
                        <p class="warning" >
                            You are using an old web browser. You'll need to install a Flash Player in order to continue with this upload. To download the latest flash player, <a target="_blank" href="http://get.adobe.com/flashplayer/">Click here</a>.<br/>
                            You can also upgrade your browser, or switch to <a target="_blank" href="https://www.google.com/intl/en/chrome/browser/">Chrome</a> or <a target="_blank" href="http://www.mozilla.org/en-US/firefox/new/">Firefox</a>.
                        </p>
                    </div>
                    <script type="text/javascript" src="<?php echo site_url("assets/js/flash_detect.js"); ?>"></script>
                    <script type="text/javascript">
                        $("#uploader_block").ready(function(){
                            if(!FlashDetect.installed && !$.browser.mozilla)
                            {
                                $(".uploader_block > #file-uploader").hide();
                                $(".uploader_block,.uploader_block > .warning").fadeIn(400);
                            }
                            else
                            {
                                $(".uploader_block > .warning").hide();
                                $(".uploader_block,.uploader_block > #file-uploader").fadeIn(400);
                            }
                        });
                    </script>
                    <div class="files_table_h">
                        <div class="file-info">File Name</div>
                        <div class="file-category">File Type</div>
                    </div>
                    <div class="files_table">
                    </div>
                    <div class="bottom_nav">
                        <a href="#back" class="back" onClick="stanlider.Show('back', 11);"><span>Back</span></a>
                        <a href="#next" class="next" onClick="stanlider.Show('next', 9);"><span>Next</span></a>

                        <div class="clear"></div>
                    </div>
                    <script id="templateUploader" type="text/x-jquery-tmpl">

                        <div class="qq-uploader">

                            <div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>
                            <div class="qq-upload-button">Upload a File</div>
                            <ul class="qq-upload-list"></ul>
                        </div>
                        <style type="text/css">

                            .qq-upload-button {
                                text-shadow: 1px 1px 2px #666;
                                color: #fff;
                            }
                        </style>
                        </script>


                        <script id="templateUploaderSwf" type="text/x-jquery-tmpl"><span class="swf-upload-button">Upload a file</span></script>
                            <style id="templateUploaderSwfStyles" type="text/css">.swf-upload-button { color: #ffffff; font-size: 19px; text-align: left; margin: 10px 0 0 15px; font-style: italic; font-family: Atial; text-shadow: 0 1px 1px #666; }</style>

                            <script id="templateUploadedFile" type="text/x-jquery-tmpl">
                                <div class="file file-${id} ${ext}">
                                    <div class="file-info">
                                        <span class="file-icon"></span>
                                        ${fileName}
                                    </div>
                                    <div class="file-size">${total}</div>
                                    <div class="file-progress-${id}"></div>

                                    <select class="file-category hidden" name="attachments_categories[${id}]">
                                        <option value="18">Select category</option>
                                        <?php foreach ($fileTypes as $ft)
                                        { ?>
                                            <option value="<?= $ft['id'] ?>"><?= $ft['name'] ?></option>
<?php } ?>
                                    </select>
                                    <a href="#" class="hidden remove file-remove remove_file_activator"></a>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                                </script>
                            </div>


                            <!--STEP 9-->
                            <div class="fieldset step" id="step9">
                                <div class="legend"><?= $_TEMPLATE['title'] ?></div>
                                <h3>Any special instructions for your <?php echo $this->config->item('title_of_the_site') ?> project manager?</h3>

                                <div class="special">
                                    <div><label>Your message</label></div>
                                    <textarea name="special_instructions"><?= $special_instructions ?></textarea>

                                    <div class="clear"></div>
                                </div>

                                <div class="bottom_nav">
                                    <a href="#back" class="back" onClick="stanlider.Show('back', 8);"><span>Back</span></a>
                                    <a href="#review" class="review" onClick="stanlider.Show('next', 10);"><span>Review</span></a>
                                    <div class="clear"></div>
                                </div>

                            </div>



                            <!--STEP 10-->
                            <div class="fieldset step" id="step10">
                                <div class="legend"><?= $_TEMPLATE['title'] ?></div>
                                <h3 class="my-tooltip" title="If you would like to make any changes, click on the field box to return to editing screen.">
                                	You've selected the following parameters for this case. Please review them before submitting.
                                </h3>
                                <div class="stan_p">
                                    <label>Application Type:</label> <span id="PREVIEW_application_type" class="review_text"></span>
                                </div>
                                <div class="stan_p" onClick="stanlider.anyPage('back',10,2);">
                                    <label>Application Number:</label>  <span id="PREVIEW_application_number" class="review_text"></span>
                                </div>
                                <div class="stan_p" onClick="stanlider.anyPage('back',10,3);">
                                    <label>Application Title:</label>  <span id="PREVIEW_application_title" class="review_text"></span>
                                </div>
                                <div class="stan_p" onClick="stanlider.anyPage('back',10,3);">
                                    <label>Applicant:</label>  <span id="PREVIEW_applicant" class="review_text"></span>
                                </div>
                                <div class="stan_p" onClick="stanlider.anyPage('back',10,3);">
                                    <label>Filing Deadline:</label>  <span id="PREVIEW_filing_deadline" class="review_text"></span>
                                </div>
                                <div class="stan_p" onClick="stanlider.anyPage('back',10,7);">
                                    <label>Selected Regions:</label>  <span id="PREVIEW_regions" class="review_text"></span>
                                </div>
                                <div class="stan_p" onClick="stanlider.anyPage('back',10,11);">
                                    <label>Region Reference Numbers</label>  <span id="PREVIEW_regions" class="review_text"></span>
                                </div>

                                <div class="bottom_nav">
                                    <a href="#back" class="back" onClick="stanlider.Show('back', 9);"><span>Back</span></a>
                                    <button class="next" type="submit"><span>Submit</span></button>
                                    <div class="clear"></div>
                                </div>
                                <script type="text/javascript">
                                    /* APPLICATION REGIONS */
                                        function previewRegions(){
                                            var Stak        =   '';
                                            var regions     =   '';
                                            var separator   =   " , ";
                                            var deda   =   $(".flags.selected .flag-box:visible");
                                            if(deda.size()){
                                                $(deda).each(function(i)
                                                {
                                                    var pole = $(this).children().eq(1);
                                                        regions +=  pole.text();
                                                        if(i!=deda.size()-1){
                                                            regions += separator;
                                                        }
                                                });
                                                $("#PREVIEW_regions").text(regions);
                                            }
                                        }
                                    $(function(){
                                        /* APPLICATION TITLE*/
                                        $('input[name="application_title"]').live("change",function(){
                                            $("#PREVIEW_application_title").text($(this).val());
                                        });
                                        $("#PREVIEW_application_title").ready(function(){
                                            $("#PREVIEW_application_title").text($('input[name="application_title"]').val());
                                        });
                                        /***************************************/
                                        /* APPLICANT */
                                        $('input[name="applicant"]').live("change",function(){
                                            $("#PREVIEW_applicant").text($(this).val());
                                        });
                                        $("#PREVIEW_applicant").ready(function(){
                                            $("#PREVIEW_applicant").text($('input[name="applicant"]').val());
                                        });
                                        /***************************************/
                                        /* DEADLINE */
                                        $('input[name="deadline"]').live("change",function(){
                                            $("#PREVIEW_filing_deadline").text($(this).val());
                                        });
                                        $("#PREVIEW_applicant").ready(function(){
                                            $("#PREVIEW_filing_deadline").text($('input[name="deadline"]').val());
                                        });
                                        /***************************************/
                                        /* APPLICATION NUMBER */
                                        $('input[name="application_number"]').live("change",function(){
                                            $("#PREVIEW_application_number").text($(this).val());
                                        });
                                        $("#PREVIEW_application_number").ready(function(){
                                            $("#PREVIEW_application_number").text($('input[name="application_number"]').val());
                                        });
                                        /***************************************/
                                        /* APPLICATION TYPE */
                                        $('input[name="application_type"]').live("change",function(){
                                            var val = $(this).val();
                                            switch(val){
                                                case "pct":
                                                    val = "PCT National Phase Entry";
                                                    break;
                                                case "direct":
                                                    val = "Direct Filing";
                                                    break;
                                                case "ep":
                                                    val = "EP Validation";
                                                    break;
                                            }
                                            $("#PREVIEW_application_type").text(val);
                                        });
                                        $("#PREVIEW_application_type").ready(function(){
                                            var val = $('input[name="application_type"]').val();
                                            switch(val){
                                                case "pct":
                                                    val = "PCT National Phase Entry";
                                                    break;
                                                case "direct":
                                                    val = "Direct Filing";
                                                    break;
                                                case "ep":
                                                    val = "EP Validation";
                                                    break;
                                            }
                                            $("#PREVIEW_application_type").text(val);
                                        });
                                        /* spike */
                                        $(".back_to_review").live("click",function(){$(this).hide();});
                                        $(".next,.back").live("click",function(){
                                            $(".back_to_review").hide();
                                        });
                                        /* end spike */
                                    });
                                </script>
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
                <a href="#back" class="back" onClick="stanlider.Show('back', 7);"><span>Back</span></a>
                <a href="#review" class="back_to_review" style="display: none" onClick="stanlider.Show('next', 10);"><span>Back to Review</span></a>
                <a href="#next" class="next" onClick="stanlider.Show('next', 8);"><span>Next</span></a>
            </div>
        </div>

                            <!--FINISH SUCCESS STEP-->
                            <div class="fieldset step" id="final">
                                <div class="legend"><?= $_TEMPLATE['title'] ?></div>
                                <h3>
									<?php if($isEstimate){?>
										Your case has been sent to our project management team,
	                                    and your <?php echo $this->config->item('title_of_the_site') ?> reference number is <span class="new_case_id"></span>.
										We will be in touch shortly with your estimate!
									<?php }else{?>
	                                	Your case has been sent to our project management team,
	                                    and your <?php echo $this->config->item('title_of_the_site') ?> reference number is <span class="new_case_id"></span>.
	                                    We will be in touch shortly to confirm receipt of these instructions!
	                                <?php }?>
                                </h3>
								<div class="img success"></div>
                            </div>
                        </div>
                    </form>


