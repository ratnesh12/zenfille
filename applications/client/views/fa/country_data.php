<?php //var_dump($document_requirements_show);exit; ?>
<script type="text/javascript">

    $.fn.serializeObject = function () {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    $(document).ready(function () {

        <?php if ($associate_data->fa_invoice_status == 'rejected') { ?>
            $('.info_close').click(function(){
                $('#messages').hide();
                $('.country_invoice').show();
            });
        <?php } ?>

        $('#doc-requirements_link').click(function(){
        show();
        });
        function show(){
            var show = $('input[name="is_required"]:checked').val();
            if (show == 'no') {
                jQuery('#doc-not-required').show();
                jQuery('#upload_section').hide();
            } else {
                jQuery('#doc-not-required').hide();
                jQuery('#upload_section').show();
            }
        }
        fa_id = '<?php echo $this->session->userdata('fa_user_id'); ?>';
        $(".datepicker").datepicker({
            "dateFormat":"M dd, yy"
        });

        $('.confirm_docs').click(function(){
            var id = $('input[name = country_id_<?php echo $country_id; ?>]').val();
            var case_id = '<?= $case['id']?>';
            var files_id_arr = [];
            $('.file_array').each(function(){
                files_id_arr[files_id_arr.length] = $(this).val();
            });
            $.post("<?php echo base_url(); ?>fa/document_required/", {country_id: id, case_id: case_id, value: '1',fa_id: fa_id, files_id_arr: files_id_arr});
        });

        $('.confirm_filing_confirmation').click(function(){
            var id = $('input[name = country_id_<?php echo $country_id; ?>]').val();
            var case_id = '<?= $case['id']?>';
            var fc_filing_date = $('input[name = "fc_filing_date"]').val();
            var examenation = $('input[name = "examenation"]').val();
            var fc_application_number = $('input[name = "fc_application_number"]').val();
            var doc_pending = $('select.doc_pending option:selected').val();
            $.post("<?php echo base_url(); ?>fa/filing_confirmation/", {country_id: id, case_id: case_id, fa_id: fa_id, examenation:examenation,fc_filing_date:fc_filing_date, doc_pending:doc_pending});
        });

        $(".fil_ded").live('change', (function(){
            var fil_ded = $(this).val();
            var parent = $(this).parent().parent();
            var tmp = parent.attr('id');
            var file_id = tmp.replace(/doc_req_/,'');
            $.post("<?php echo base_url(); ?>fa/document_requirements/", {fa_id: fa_id, file_id: file_id, file_filing_deadline: fil_ded});
        }));

        $(".extansion").live('change', (function(){
            var extansion = $(this).val();
            var parent = $(this).parent().parent();
            var tmp = parent.attr('id');
            var file_id = tmp.replace(/doc_req_/,'');
            $.post("<?php echo base_url(); ?>fa/document_requirements/", {fa_id: fa_id, file_id: file_id, extansion: extansion});
        }));

        $(".filing_fee").change(function(){
            var valtmp = $(this).val();
            var filing_fee = valtmp.replace(/[$€]/,'');
            var parent = $(this).parent().parent().parent();
            var tmp = parent.attr('id');
            var file_id = tmp.replace(/doc_req_/,'');
            <?php if($fa_fee_currency == 'usd'){?>
                    $(this).val('$'+ valtmp);
                 <?php }
                        if($fa_fee_currency  == 'euro'){ ?>
                            $(this).val('€'+ valtmp);
                     <?php   } ?>
            $.post("<?php echo base_url(); ?>fa/document_requirements/", {fa_id: fa_id, file_id: file_id, filing_fee: filing_fee});
        });

        $(".hardcopy").click(function(){
            //alert(tmp);
            //console.log(test);
            var hardcopy = $(this).attr('rel');
            var parent = $(this).parent().parent().parent();
            var tmp = parent.attr('id');
            var file_id = tmp.replace(/doc_req_/,'');
            $.post("<?php echo base_url(); ?>fa/document_requirements/", {fa_id: fa_id, file_id: file_id, hardcopy: hardcopy});
        });

        $(".notarization").click(function(){
            var notarization = $(this).attr('rel');
            var parent = $(this).parent().parent().parent();
            var tmp = parent.attr('id');
            var file_id = tmp.replace(/doc_req_/,'');
            $.post("<?php echo base_url(); ?>fa/document_requirements/", {fa_id: fa_id, file_id: file_id, notarization: notarization});
        });

        $(".remove_doc_file").click(function(){
            var parent = $(this).parent().parent();
            var tmp = parent.attr('id');
            var file_id = tmp.replace(/doc_req_/,'');
            var file_id = file_id.replace(/fil_con_/,'');
            $.post("<?php echo base_url(); ?>fa/remove_file/", {fa_id: fa_id, file_id: file_id});
            $('#'+tmp).remove();
        });

        $(".legalization").click(function(){
            var legalization = $(this).attr('rel');
            var parent = $(this).parent().parent().parent();
            var tmp = parent.attr('id');
            var file_id = tmp.replace(/doc_req_/,'');
            $.post("<?php echo base_url(); ?>fa/document_requirements/", {fa_id: fa_id, file_id: file_id, legalization: legalization});
        });

        $(".legalization_choice").click(function(){
            var legalization_by = $(this).val();
            var parent = $(this).parent().parent().parent().parent().parent();
            var tmp = parent.attr('id');
            var file_id = tmp.replace(/doc_req_/,'');
            $.post("<?php echo base_url(); ?>fa/document_requirements/", {fa_id: fa_id, file_id: file_id, legalization_by: legalization_by});
        });

        jQuery(".tb_switch").each(function () {
            jQuery('#' + jQuery(this).attr('ref')).hide();
        });

        jQuery(".tb_switch").live('click', (function () {
            jQuery('#' + jQuery(this).attr('ref')).toggle('slow');
        }));

        jQuery('#upload_section').hide();
        $(".tabs_container").tabs({expires: 30, path: '/', secure: true});
<?php if ($show_info == '1') { ?>
        if (tab_to_load == 'invoice') {
            $(".tabs_container").tabs("select", 5);
        }if (tab_to_load == 'doc-requirements') {
            $(".tabs_container").tabs("select", 3);
            show();
            $('#doc-requirements-files').css('display','block');
        }if (tab_to_load == 'filing-conf') {
            $(".tabs_container").tabs("select", 4);
            $('#filing-conf-files').css('display','block');
        }
<?php }else{?>
    if (tab_to_load == 'invoice') {
        $(".tabs_container").tabs("select", 4);
    }if (tab_to_load == 'doc-requirements') {
        $(".tabs_container").tabs("select", 2);
            show();
        $('#doc-requirements-files').css('display','block');

    }if (tab_to_load == 'filing-conf') {
        $(".tabs_container").tabs("select", 3);
        $('#filing-conf-files').css('display','block');
    }
    <?php } ?>
        /*Toggle of docs*/
        jQuery('input[name=is_required]').change(function () {
            if (jQuery(this).val() == 'no') {
                jQuery('#doc-not-required').show('slow');
                jQuery('#upload_section').hide('slow', function () {
                    jQuery.scrollTo('#doc_toggle', 800);
                });

            } else {
                jQuery('#doc-not-required').hide('slow');
                jQuery('#upload_section').show('slow', function () {
                    jQuery.scrollTo('#doc_toggle', 800);
                });

            }
        });

        var additional_fee_block = $('.additional_fee_block').remove().clone();

        $('.add_additional_fee').click(function () {
            $('#additional_fees').append(additional_fee_block.clone());
            return false;
        });

        $('.remove_additional_fee').live('click', function () {
            $(this).parent().remove();
            return false;
        });

        $('.delete_additional_fee').click(function(){
            var additional_fee_id = $(this).attr('href');
            var temp_object = this;
            $.ajax({
                type: "POST",
                cache: false,
                url: "<?php echo base_url() ?>fa/ajax_delete_additional_fee" ,
                data: {
                    ajax_delete_additional_fee: additional_fee_id
                } ,
                datatype: 'json' ,
                success: (function(html) {
                    $(temp_object).parent().parent().remove();
                })
            });

            return false;
        });

        jQuery('td.late_fee input[type=text]').keyup(function () {
            var value = $(this).val();
            value = value.replace(/[^\d]/g, '');
//            console.log(value);
            $(this).val(value);
        });

        $('.update_ref').live('click', function () {
            value = $('input[name = your_reference_number_<?= $country_id?>]').val();
            id = $('input[name = country_id_<?php echo $country_id; ?>]').val();
            case_id = '<?= $case['id']?>';
            $.post("<?php echo base_url(); ?>fa/add_fa_reference_number/", {country_id: id, value: value, case_id: case_id});
        });
        $('#confirm_required').live('click', function () {
            var id = $('input[name = country_id_<?php echo $country_id; ?>]').val();
            var case_id = '<?= $case['id']?>';
            $.post("<?php echo base_url(); ?>fa/document_required/", {country_id: id, case_id: case_id, value: '2'});
        });

        $('.confirm_instructions').live('click', function () {
            var id = $('input[name = country_id_<?php echo $country_id; ?>]').val();
            var case_number = '<?= $case['case_number']?>';
            var fa_note = $('textarea#fa_note').val();
            var case_id = '<?= $case['id']?>';
            $.post("<?php echo base_url(); ?>fa/document_required/", {country_id: id, fa_id:fa_id, fa_note: fa_note, case_id: case_id, case_number: case_number, value: '1'});
        });

        jQuery('.checker').click(function () {
            $(this).parents('.checkers').find('div.notice').hide();
            if ($(this).hasClass('legalization') && $(this).hasClass('checker_yes')) {
                $(this).parents('.checkers').find('div.notice').show();
                return false;
            }
            if ($(this).hasClass('checker_yes')) {
                $(this).removeClass('checker_yes').addClass('checker_yes_active');
                $(this).parents('.checkers').find('.checker_no_active')
                    .addClass('checker_no').removeClass('checker_no_active');
            } else if ($(this).hasClass('checker_no')) {
                $(this).removeClass('checker_no').addClass('checker_no_active');
                $(this).parents('.checkers').find('.checker_yes_active')
                    .addClass('checker_yes').removeClass('checker_yes_active');
            }
            return false;
        });

        jQuery('input.legalization_choice').change(function () {
            $(this).parents('.checkers').find('div.notice').hide();
            $(this).parents('.checkers').find('.checker_no_active')
                .addClass('checker_no').removeClass('checker_no_active');
            $(this).parents('.checkers').find('.checker_yes')
                .addClass('checker_yes_active').removeClass('checker_yes');
        });

//        jQuery('#confirm_required').click(function () {
//            if (jQuery('input[name=is_required]:checked').val() == 'yes') {
//                jQuery('#upload_section').show();
//            } else {
//                jQuery('#upload_section').hide();
//            }
//            return false;
//        });

        jQuery('input.currency_choice').change(function () {
            $(this).parents('.cell_content').find('div.notice').hide();
        });

        jQuery('td.late_fee input').click(function () {
            $(this).parents('.cell_content').find('div.notice').show();
        });

        jQuery('.additional_instructions textarea').placeholder();
    });
</script>

<div class="tabs_container" id="country_content">
<ul class="tabs">
    <?php if ($show_info == '1') { ?>
        <li><a href="#client-info" class="first">Client Info</a></li>
    <?php }?>
    <li><a href="#case-info">Case Info</a></li>
    <li><a href="#files">Case Files</a></li>
    <li><a id="doc-requirements_link" href="#doc-requirements">Document Requirements</a></li>
    <li><a href="#filing-conf">Filing Confirmation</a></li>
    <li><a href="#invoice" class="last">Invoice</a></li>
</ul>
<?php if ($show_info == '1') { ?>
    <!-- FIRST TAB -->
    <div id="client-info">
        <table class="table info">
            <tr class="even">
                <th>Email</th>
                <td><?= isset($customer['customer_email']) ? $customer['customer_email'] : 'N/A';?></td>
            </tr>
            <tr class="odd">
                <th>First Name</th>
                <td><?= isset($customer['customer_firstname']) ? $customer['customer_firstname'] : 'N/A'; ?></td>
            </tr>
            <tr class="even">
                <th>Last Name</th>
                <td><?= isset($customer['customer_lastname']) ? $customer['customer_lastname'] : 'N/A'; ?></td>
            </tr>
            <tr class="odd">
                <th>Company</th>
                <td><?= isset($customer['customer_company_name']) ? $customer['customer_company_name'] : 'N/A'; ?></td>
            </tr>
            <tr class="even">
                <th>Address</th>
                <td><?= isset($customer['customer_address']) ? $customer['customer_address'] : 'N/A'; ?></td>
            </tr>
            <tr class="odd">
                <th>Address2</th>
                <td><?= isset($customer['customer_address2']) ? $customer['customer_address2'] : 'N/A'; ?></td>
            </tr>
            <tr class="even">
                <th>City</th>
                <td><?= isset($customer['customer_city']) ? $customer['customer_city'] : 'N/A'; ?></td>
            </tr>
            <tr class="odd">
                <th>State</th>
                <td><?= isset($customer['customer_state']) ? $customer['customer_state'] : 'N/A'; ?></td>
            </tr>
            <tr class="even">
                <th>Zip Code</th>
                <td><?= isset($customer['customer_zip_code']) ? $customer['customer_zip_code'] : 'N/A'; ?></td>
            </tr>
            <tr class="odd">
                <th>Country</th>
                <td><?= isset($customer['customer_country']) ? $customer['customer_country'] : 'N/A'; ?></td>
            </tr>
            <tr class="even">
                <th>Phone</th>
                <td><?= isset($customer['phone_number']) ? $customer['phone_number'] : 'N/A'; ?></td>
            </tr>
            <tr class="odd">
                <th>Ext</th>
                <td><?= isset($customer['customer_ext']) ? $customer['customer_ext'] : 'N/A'; ?></td>
            </tr>
            <tr class="even">
                <th>Fax</th>
                <td><?= isset($customer['customer_fax']) ? $customer['customer_fax'] : 'N/A'; ?></td>
            </tr>
            <tr class="odd">
                <th>CC (separated by semicolon)</th>
                <td><? if (isset($customer['contacts'])) {
                        echo $customer['contacts'];
                    }?></td>
            </tr>
            <tr class="even">
                <th>Client Ref#</th>
                <td id="client_ref_number"><?= $current_country['reference_number'] ? $current_country['reference_number'] : $case['reference_number']?></td>
            </tr>
        </table>
    </div>
<?php }?>
<!-- SECOND TAB -->
<div id="case-info">
    <table class="table info">
        <tr class="even">
            <th>Zenfile Case Number</th>
            <td><?= $case['case_number']?></td>
        </tr>
        <tr class="odd">
            <th>Client Reference Number</th>
            <td id="case_info_ref_number"><?= $current_country['reference_number'] ? $current_country['reference_number'] : $case['reference_number']?></td>
        </tr>
        <tr class="even">
            <th>Your Reference Number</th>
            <td class="your_reference_number">
                <input type="text" name="your_reference_number_<?php echo $country_id; ?>" value="<?php echo $fa_ref_number ?>"/>
                <input type="hidden" id="country_id" name="country_id_<?php echo $country_id; ?>" value="<?php echo $country_id; ?>"/>
                <a href="javascript:void(0)" class="update_ref blue_button">Update</a>

                <div class="clear"></div>
            </td>
        </tr>
        <tr class="odd">
            <th>Case Type</th>
            <td><?= $case['case_type']?></td>
        </tr>
        <tr class="even">
            <th>Application Number</th>
            <td><?= $case['application_number']?></td>
        </tr>
        <tr class="odd">
            <th>Application Title</th>
            <td><?= $case['application_title']?></td>
        </tr>
        <tr class="even">
            <th>Applicant</th>
            <td><?= $case['applicant']?></td>
        </tr>
<?php if ($case['case_type_id'] != '2') { ?>
        <tr class="odd">
            <th>First Priority Date</th>
            <td><?php if(isset($case['first_priority_date']) && $case['first_priority_date']!='0000-00-00'){ echo date($this->config->item('client_date_format') , strtotime($case['first_priority_date']));}else{ echo "N/A";}?></td>
        </tr>
    <?php if ($case['case_type_id'] != '3') { ?>
        <tr class="even">
            <th>International Filing Date</th>
            <td><?php if(isset($case['international_filing_date'])  && $case['international_filing_date']!='0000-00-00'){ echo date($this->config->item('client_date_format') , strtotime($case['international_filing_date']));}else{ echo "N/A";}?></td>
        </tr>
        <?php }}else{?>
        <tr class="odd">
            <th>Publication Date</th>
            <td><?=date($this->config->item('client_date_format') , strtotime($case['publication_date']))?></td>
            <td><?php if(isset($case['publication_date'])  && $case['publication_date']!='0000-00-00'){ echo date($this->config->item('client_date_format') , strtotime($case['publication_date']));}else{ echo "N/A";}?></td>
        </tr>
        <?php }if ($case['case_type_id'] == '1') { ?>
            <tr class="odd">
                <th>30 month filing deadline</th>
                <td><?=date($this->config->item('client_date_format') , strtotime($case['30_month_filing_deadline'])) ?></td>
            </tr>
            <tr class="even">
                <th>31 month filing deadline</th>
                <td><?=date($this->config->item('client_date_format') , strtotime($case['31_month_filing_deadline'])) ?></td>
            </tr>
        <?php } else { ?>
            <tr class="even">
                <th>Filing Deadline</th>
                <td><?= date($this->config->item('client_date_format') , strtotime($case['filing_deadline']))?></td>
            </tr>
        <?php }?>
        <tr class="odd">
            <th>Zenfile Project Manager</th>
            <td><?= $manager['fullname'] ?></td>
        </tr>
        <tr class="even">
            <th>Zenfile Project Manager's Email</th>
            <td><?= $manager['email'] ?></td>
        </tr>
        <tr class="odd">
            <th>Zenfile Project Manager's Phone</th>
            <td><?= $manager['phone'] ?></td>
        </tr>
    </table>
</div>

<!-- THIRD TAB -->
<div id="files">
    <div class="expand tb_switch my-tooltip" ref="switch_client_files" title="Expand tab to view relevant case files">
        Client Files
    </div>
    <table class="table files" id="switch_client_files">
        <tbody class="files_table">
        <?php if (check_array($client_files)): ?>
            <?php foreach ($client_files as $k => $file): ?>
                <?php
                $row_class = ($k & 1) ? 'odd' : 'even';
                $ext = strtolower(substr($file['filename'], strrpos($file['filename'], '.') + 1));
                if (file_exists(FCPATH . 'assets/images/file_types/type_' . $ext . '.png')) {
                    $type_class = 'type_' . $ext;
                } else {
                    $type_class = 'type_def';
                }
                ?>
                <tr class="<?php echo $row_class ?>">
                    <td class="b p90 without-border-r <?= $type_class ?>">
                        <a href="<?= base_url() ?>cases/view_file/<?php echo $file['file_id_link'] ?>">
                            <?php
                            if (strlen($file['filename']) > 53) {
                                $file['filename'] = substr($file['filename'], 0, 53) . '...';
                            }
                            ?>
                            <?php echo $file['filename'] ?>
                        </a>
                    </td>
                    <td class="b without-border-l"><?php echo $file['name'] ?></td>
                    <td class="b right without-border-l file_download">
                        <a href="<?= base_url() ?>cases/view_file/<?php echo $file['file_id_link'] ?>">
                            <?= format_bytes($file['filesize']) ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach ?>
        <?php endif ?>
        </tbody>
    </table>

    <div class="expand tb_switch my-tooltip" ref="switch_case_files"
         title="Expand tab to view relevant case files">
        Documents
    </div>

    <table class="table files" id="switch_case_files">
        <tbody>
        <?php if (check_array($document_files)):

             foreach ($document_files as $k => $file):

                $row_class = ($k & 1) ? 'odd' : 'even';
                $ext = strtolower(substr($file['filename'], strrpos($file['filename'], '.') + 1));
                if (file_exists(FCPATH . 'assets/images/file_types/type_' . $ext . '.png')) {
                    $type_class = 'type_' . $ext;
                } else {
                    $type_class = 'type_def';
                }
                ?>
                <tr class="<?php echo $row_class ?>">
                    <td class="b p90 without-border-r <?= $type_class ?>">
                        <?php
                        if (strlen($file['filename']) > 53) {
                            $file['filename'] = substr($file['filename'], 0, 53) . '...';
                        }
                        ?>
                        <a href="<?= base_url() ?>cases/view_file/<?php echo $file['file_id_link'] ?>"><?php echo $file['filename'] ?></a>
                    </td>
                    <td class="b without-border-l"><?php echo $file['name'] ?></td>
                    <td class="b right without-border-l file_download">
                        <a href="<?= base_url() ?>cases/view_file/<?php echo $file['file_id_link'] ?>">
                            <?= format_bytes($file['filesize']) ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if ($case['common_status'] == 'active' || $case['common_status'] == 'completed') { ?>
        <div class="expand">
			<span class="tb_header tb_switch my-tooltip" ref="filling_confirmation_tbl"
                  title="Expand tab to view relevant case files">
				Filing Confirmation
			</span>
            <?php if ($filing_files) { ?>
                <span class="file_download">
					<a href="/client/cases/create_zip/<?= $case_id; ?> ">Download all confirmation reports</a>
				</span>
            <?php } ?>
            <div class="clear"></div>
        </div>
        <table class="table countries" id="filling_confirmation_tbl">
            <tbody>
            <?php if (check_array($case_countries)): ?>
                <?php foreach ($case_countries as $k => $country): ?>
                    <?php
                    $row_class = ($k & 1) ? 'odd' : 'even';
                    if ($country['files']) {
                        $switch_class = "tb_switch";
                        $ref = "ref='con_files_" . $country['id'] . "'";
                    } else {
                        $switch_class = $ref = "";
                    }
                    ?>
                    <tr class="<?php echo $row_class ?>">
                        <td class="flag flags">
                            <img src="/client/<?= $country['flag_image'] ?>">
                        </td>
                        <td
                            class="con_name <?= $switch_class ?>"
                            <?= $ref ?>
                            >
                            <? echo $country['country']; ?>
                        </td>
                        <td class="b right my-tooltip without-border-l <?php echo($country['files'] ? 'file_download' : 'file_download_not_ready') ?>"
                            title="<?= $country['files'] ? 'Filing confirmation is available for download' : 'Filing confirmation for this region is not yet available. Please be patient' ?>">
                            <a href="/client/cases/create_zip/<? echo $case_id; ?>/<? echo $country['id']; ?> ">
                                &nbsp;</a>
                        </td>
                    </tr>
                    <?php if ($country['files']): ?>
                        <tr class="attachments">
                            <td colspan="3" id="con_files_<? echo $country['id']; ?>">
                                <table class="table files country_files">
                                    <tbody>
                                    <?php foreach ($country['files'] as $file) { ?>
                                        <?php
                                        $ext = strtolower(substr($file['filename'], strrpos($file['filename'], '.') + 1));
                                        if (file_exists(FCPATH . 'assets/images/file_types/type_' . $ext . '.png')) {
                                            $type_class = 'type_' . $ext;
                                        } else {
                                            $type_class = 'type_def';
                                        }
                                        ?>
                                        <tr class="even">
                                            <td class="b p90 without-border-r <?= $type_class ?>">
                                                <?php
                                                if (strlen($file['filename']) > 53) {
                                                    $file['filename'] = substr($file['filename'], 0, 53) . '...';
                                                }
                                                ?>
                                                <a href="<?= base_url() ?>cases/view_file/<?php echo $file['file_id_link'] ?>"><?php echo $file['filename'] ?></a>
                                            </td>
                                            <td class="b without-border-l"><?php echo $file['name'] ?></td>
                                            <td class="b right without-border-l file_download">
                                                <a href="<?= base_url() ?>cases/view_file/<?php echo $file['file_id_link'] ?>">
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
        <tr class="file file-${id} ${ext}">
            <td class="b" colspan="3">
                <div class="file-info" style="width:55%">
                    <span class="file-icon"></span>
                    ${fileName}
                </div>
                <select class="file-category" name="attachments_categories[${id}]">
                    <option value="18">Select category</option>
                    <?php foreach ($fileTypes as $ft) { ?>
                        <option value="<?= $ft['id'] ?>"><?= $ft['name'] ?></option>
                    <?php } ?>
                </select>
                <a href="#" class="remove file-remove remove_file_activator my-tooltip"
                   title="Remove Case File">
                </a>

                <div class="file-progress-${id}"></div>
                <div class="file-size">${total}</div>
            </td>
        </tr>
    </script>
</div>

<!-- FOURTH TAB -->
<div id="doc-requirements">
    <div class="doc-requirements-info">
        <p>Please select from the following options:</p>

        <div class="info-left" id="doc_toggle">
            <div class="required_choice">
                <input type="radio"  name="is_required" id="is_required_no"<?php if($document_requirements_show['doc_required'] =='2'){?>checked="checked" <?php }?> value="no"/>&nbsp;
                <label for="is_required_no">Documents not required</label>
            </div>
            <div class="required_choice">
                <input type="radio" <?php if($document_requirements_show['doc_required'] !='2'){?>checked="checked" <?php }?> name="is_required" id="is_required_yes" value="yes"/>&nbsp;
                <label for="is_required_yes">Documents are required</label>
            </div>
        </div>
        <div class="info-right" id="doc-not-required">
            <a class="blue_button" id="confirm_required" href="javascript:void(0)">Confirm</a>

            <p>Note: You MUST click "Confirm" button in order to save this information</p>
        </div>
        <div class="clear"></div>
    </div>
    <div id="upload_section">
        <p>Please upload the required documents and fill out the requested information:</p>
        <!-- div class="uploader_block_position">
            <div id="uploader_block">
                <div id="file-uploader">
                    <input class="nofloat" id="upload_file" name="upload_file" type="file"  />
                </div>
            </div>
        </div -->
        <form class="invoice_form" action="<?php echo base_url() ?>fa/case_fees/<?php echo $this->uri->segment(4) ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="tab_to_load" value="doc-requirements">
            <input type="hidden" name="country_to_load" value="<?php echo $current_country['country_id'] ?>">
            <input type="hidden" name="case_country_id" value="<?php echo $current_country['id'] ?>">
            <input type="file" name="doc_requir_file" />
            <input type="submit" class="blue_button submit_invoice" value="Upload" name="upload" style="height: 40px;">
        </form>
        <div class="expand tb_switch my-tooltip" ref="doc-requirements-files"
             title="Expand tab to view relevant case files">
            Documents
        </div>

        <div id="doc-requirements-files">
            <table class="fa-table">
                <tr>
                    <th width="250">File Name</th>
                    <th>Deadline for Filing</th>
                    <th>Hardcopy Required</th>
                    <th>Notarization Required</th>
                    <th>Legalization Certification</th>
                    <th>Final deadline</th>
                    <th>Late Filing Fee</th>
                    <th class="last">Delete</th>
                </tr>
<?php if (check_array($document_requirements_files)){
     foreach ($document_requirements_files as $k => $file){

        $row_class = ($k & 1) ? 'odd' : 'even';
        $ext = strtolower(substr($file['filename'], strrpos($file['filename'], '.') + 1));
        if (file_exists(FCPATH . 'assets/images/file_types/type_' . $ext . '.png')) {
            $type_class = 'type_' . $ext;
        } else {
            $type_class = 'type_def';
        }
        ?>
                <tr id="doc_req_<?php echo $file['file_id_link'] ?>" class="<?php echo $row_class ?>">
                    <td class="doc_file_name">
                        <a href="<?= base_url() ?>cases/view_file/<?php echo $file['file_id_link'] ?>"><?php echo $file['filename'] ?></a>
                    </td>
                    <td class="deadline_for_filling">
                        <input type="text" class="datepicker fil_ded" name ='file_filing_deadline' value="<?php if(isset($file['file_filing_deadline'])) echo date($this->config->item('client_date_format') , strtotime($file['file_filing_deadline'])); ?>">
                    </td>
                    <td>
                        <?php if(isset($file['hardcopy']) && $file['hardcopy'] != '0'){
                        if($file['hardcopy'] == '1'){
                            $checker_no = 'checker_no';
                            $checker_yes = 'checker_yes_active';
                        }else{
                            $checker_no = 'checker_no_active';
                            $checker_yes = 'checker_yes';
                        }
                               }else{
                        $checker_no = 'checker_no';
                        $checker_yes = 'checker_yes';
                    } ?>
                        <div class="checkers">
                            <a class="hardcopy checker <?=$checker_yes?>"  rel="1" href="#"></a>
                            <a class="checker hardcopy <?=$checker_no?>" rel="2" href="#"></a>

                            <div class="clear"></div>
                        </div>
                    </td>
                    <td>
                        <?php if(isset($file['notarization']) && $file['notarization'] != '0'){
                        if($file['notarization'] == '1'){
                            $checker_no = 'checker_no';
                            $checker_yes = 'checker_yes_active';
                        }else{
                            $checker_no = 'checker_no_active';
                            $checker_yes = 'checker_yes';
                        }
                    }else{
                        $checker_no = 'checker_no';
                        $checker_yes = 'checker_yes';
                    } ?>
                        <div class="checkers">
                            <a class="checker notarization <?=$checker_yes?>"  rel="1" href="#"></a>
                            <a class="checker notarization <?=$checker_no?>" rel="2" href="#"></a>

                            <div class="clear"></div>
                        </div>
                    </td>
                    <td>
                        <?php if(isset($file['legalization']) && $file['legalization'] != '0'){
                        if($file['legalization'] == '1'){
                            $checker_no = 'checker_no';
                            $checker_yes = 'checker_yes_active';
                        }else{
                            $checker_no = 'checker_no_active';
                            $checker_yes = 'checker_yes';
                        }
                    }else{
                        $checker_no = 'checker_no';
                        $checker_yes = 'checker_yes';
                    } ?>
                        <div class="checkers">
                            <a class="checker legalization <?=$checker_yes?>"  rel="1" href="#"></a>
                            <a class="checker legalization <?=$checker_no?>" rel="2" href="#"></a>

                            <div class="clear"></div>
                            <div class="notice">
                                <div>
                                    <input type="radio" class="legalization_choice" name="legalization_choice"
                                           id="legalization_apostille" value="apostille" checked="checked"/>&nbsp;
                                    <label for="legalization_apostille">By Apostille</label>
                                </div>
                                <div>
                                    <input type="radio" class="legalization_choice" name="legalization_choice"
                                           id="legalization_consultant" value="consultant"/>&nbsp;
                                    <label for="legalization_consultant">By the Consultant</label>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="extension">
                        <input type="text" class="datepicker extansion" name ='extansion' value="<?php if(isset($file['extansion'])) echo date($this->config->item('client_date_format') , strtotime($file['extansion'])); ?>">
                    </td>
                    <td class="late_fee">
                        <div class="cell_content">
                            <input name="filing_fee" class= "filing_fee" type="text" value="<?php if($fa_fee_currency =='usd') echo '$'; if($fa_fee_currency =='euro') echo '€'; if(isset($file['filing_fee'])) echo $file['filing_fee']; ?>">

<!--                            <div class="notice">-->
<!--                                <div>-->
<!--                                    <input type="radio" class="currency_choice" name="currency" id="currency_euro_--><?//=$k ?><!--"-->
<!--                                           value="euro"/>&nbsp;-->
<!--                                    <label for="currency_euro_--><?//=$k ?><!--">EUR</label>-->
<!--                                </div>-->
<!--                                <div>-->
<!--                                    <input type="radio" class="currency_choice" name="currency" id="currency_usd_--><?//=$k ?><!--"-->
<!--                                           value="usd"/>&nbsp;-->
<!--                                    <label for="currency_usd_--><?//=$k ?><!--">USD</label>-->
<!--                                </div>-->
<!--                            </div>-->
                        </div>
                    </td>
                    <td class="last"><a class="remove_doc_file" href="#"></a></td>
                </tr>
                    <input type="hidden" class="file_array" name="file_id_array[]" value ="<?php echo $file['file_id_link'] ?>">
                    <?php }} ?>
            </table>
            <a class="blue_button confirm_docs" href="#">Confirm</a>

            <div class="clear"></div>
        </div>
        <div class="additional_instructions">
            <p>Please provide any additional instructions below</p>
            <textarea id="fa_note" placeholder="You message go here"></textarea>
            <a class="blue_button_long confirm_instructions" href="javascript:void(0)">Submit & Confirm</a>
        </div>
    </div>
</div>

<!-- FIFTH TAB -->
<div id="filing-conf">

    <p>Please fill out the requested fields bellow:</p>

    <div>
        <div class="float-left filing-lable">
            Filing Date
        </div>
        <div class="float-left filing-input">
            <input name="fc_filing_date" class="datepicker" value="" type="text"/>
        </div>
        <div class="clear"></div>
    </div>
    <div>
        <div class="float-left filing-lable">
            Documents Pending
        </div>
        <div class="float-left filing-input">
            <div style="width: 200px">
                <?= form_dropdown('doc_pending', array('yes' => 'Yes', 'no' => 'No'), 'yes', 'id="d" class="doc_pending"');?>
                <div class="clear"></div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div>
        <div class="float-left filing-lable">
            Date for Requesting Examination
        </div>
        <div class="float-left filing-input">
            <input name="examenation" class="datepicker" value="" type="text"/>

            <div>If not required, or has been already requested, leave blank</div>
        </div>
        <div class="clear"></div>
    </div>
    <div>
        <div class="float-left filing-lable">
            Application Number Assigned
        </div>
        <div class="float-left filing-input">
            <input name="fc_application_number" value="" type="text"/>

            <div>If not available or applicable, leave blank</div>
        </div>
        <div class="clear"></div>
    </div>


    <p>Please upload your filing report and/or filing recept here:</p>

    <div class="float-left">
        <form class="invoice_form" action="<?php echo base_url() ?>fa/case_fees/<?php echo $this->uri->segment(4) ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="tab_to_load" value="filing-conf">
            <input type="hidden" name="country_to_load" value="<?php echo $current_country['country_id'] ?>">
            <input type="hidden" name="case_country_id" value="<?php echo $current_country['id'] ?>">
            <input type="file" name="fc_file" />
            <input type="submit" class="blue_button submit_invoice" value="Upload" name="upload" style="height: 40px;">
        </form>
<!--        <div id="uploader_block">-->
<!--            <div id="filing-uploader">-->
<!--                <input class="nofloat" id="upload_filing" name="upload_filing" type="file"/>-->
<!--            </div>-->
<!--        </div>-->
    </div>
    <div class="float-left warning-notice">DO NOT ATTACH YOUR INVOICE IN THIS SECTION</div>
    <div class="clear"></div>
    <div class="expand tb_switch my-tooltip" ref="filing-conf-files" title="Expand tab to view relevant case files">
        Filing Confirmation
    </div>
    <div id="filing-conf-files">
        <table class="fa-table">
            <tr>
                <th>File Name</th>
                <th width="47" class="last">Delete</th>
            </tr>
<?php if (check_array($filing_files)){
     foreach ($filing_files as $k => $file){

         $row_class = ($k & 1) ? 'odd' : 'even';
         $ext = strtolower(substr($file['filename'], strrpos($file['filename'], '.') + 1));
         if (file_exists(FCPATH . 'assets/images/file_types/type_' . $ext . '.png')) {
             $type_class = 'type_' . $ext;
         } else {
             $type_class = 'type_def';
         }
         ?>
            <tr id="fil_con_<?php echo $file['file_id_link'] ?>" class=" <?=$row_class?>">
                <td class="doc_file_name">
                    <a href="<?= base_url() ?>cases/view_file/<?php echo $file['file_id_link'] ?>"><?php echo $file['filename'] ?></a>
                </td>
                <td class="last"><a class="remove_doc_file" href="#"></a></td>
            </tr>
                <?php }}?>
        </table>
        <a class="blue_button float-right confirm_filing_confirmation" href="#">Confirm</a>

        <div class="fa_note float-right">Note: You MUST click “Confirm” button in order to save this information</div>
        <div class="clear"></div>
    </div>
</div>

<!-- SIXTH TAB -->
<div id="invoice">
    <?php if(!empty($is_passed_country) && empty($associate_data->fa_invoice_status)) { ?>
        <div id="messages" style="display:block;">
            <div class="message_box message_box_passed message_box_error">
                <div class="info_part"></div>
                <div class="info_close">&nbsp;</div>
                <div class="info_center" style="width: 450px;">
                    <div style="font-size: 17px;" class="info_Information">The deadline to submit your invoice has passed</div>
                    <div class="info_message">Please click here to contact your ZenFile project manager</div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    <?php } elseif($associate_data->fa_invoice_status == 'pending-approval') {  ?>
        <div id="messages" style="display:block;">
            <div class="message_box message_box_warning">
                <div class="info_part"></div>
                <div class="info_close"></div>
                <div class="info_center">
                    <div class="info_Information">Your <?php if ($associate_data->fa_invoice_status == 'pending-approval') echo 'invoice'; else echo 'request'; ?> is being reviewed</div>
                    <div class="info_message"></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    <?php } elseif($associate_data->fa_invoice_status == 'rejected') { ?>
        <div id="messages" style="display:block;">
            <div class="message_box message_box_error">
                <div class="info_part"></div>
                <div class="info_close">&nbsp;</div>
                <div class="info_center" style="width: 450px;">
                    <div class="info_Information">Your invoice has been rejected</div>
                    <div class="info_message">Close this box to re-enter your fees and upload your invoice</div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    <?php } elseif($associate_data->fa_invoice_status == 'approved') { ?>
        <div id="messages" style="display:block;">
            <div class="message_box message_box_success">
                <div class="info_part"></div>
                <div class="info_close"></div>
                <div class="info_center">
                    <div class="info_Information">Your invoice has been approved</div>
                    <div class="info_message"></div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php
    if($associate_data->fa_invoice_status == 'approved') {
        $disabled = 'disabled="true"';
    } else {
        $disabled = '';
    }
    ?>

    <?php if((empty($associate_data->fa_invoice_status) || $associate_data->fa_invoice_status == 'pending-unlock' || $associate_data->fa_invoice_status == 'rejected' || $associate_data->fa_invoice_status == 'approved') && !(empty($associate_data->fa_invoice_status) && !empty($is_passed_country)) ) { ?>
    <div class="country_invoice country_invoice_<?php echo $current_country['id'] ?>" <?php if($associate_data->fa_invoice_status == 'rejected') {echo 'style="display:none;"'; } ?>>
        <p class="invoice-warning">Please enter your fees below and click SUBMIT before leaving the page.</p>

        <form class="invoice_form" action="<?php echo base_url() ?>fa/case_fees/<?php echo $this->uri->segment(4) ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="tab_to_load" value="invoice">
            <input type="hidden" name="country_to_load" value="<?php echo $current_country['country_id'] ?>">
            <input type="hidden" name="case_country_id" value="<?php echo $current_country['id'] ?>">

            <div>
                <div class="float-left invoice-lable">
                    Professional Fee:
                </div>
                <div class="float-left invoice-input">
                    <input readonly="true" type="text" name="fa_invoice_professional_fee" value="<?php echo $associate_data->fee ?>"/>
                </div>
                <div class="clear"></div>
            </div>
            <div>
                <div class="float-left invoice-lable">
                    Official Fees:
                </div>
                <div class="float-left invoice-input">
                    <input <?php echo $disabled ?> name="fa_invoice_official_fee" type="text" value="<?php echo $associate_data->fa_invoice_official_fee ?>" />
                </div>
                <div class="clear"></div>
            </div>
            <div id="additional_fees">
                <div class="additional_fee_block">
                    <div class="float-left invoice-lable">
                        <span class="invoice-required">*</span>Additional Fees:
                    </div>
                    <div class="float-left invoice-input">
                        <input <?php echo $disabled ?> name="additional_fee_by_fa[]" type="text"/ >
                    </div>
                    <div class="float-left invoice-additional">
                        <input <?php echo $disabled ?> name="additional_fee_description_by_fa[]" type="text" placeholder="Please provide a short description of this cost"/>
                    </div>
                    <a class="remove_additional_fee" href="#"><img height="30px"
                                                                   src="/client/assets/img/edit_remove.png"></a>

                    <div class="clear"></div>
                </div>
                <?php foreach($invoice_additional_fees as $key => $additional_fee) { ?>
                    <div>
                        <div class="float-left invoice-lable">
                            <span class="invoice-required">*</span>Additional Fees:
                        </div>
                        <div class="float-left invoice-input">
                            <input <?php echo $disabled ?> name="additional_fee_by_fa_update[]" type="text" value="<?php echo $additional_fee->additional_fee_by_fa ?>" />
                        </div>
                        <div class="float-left invoice-additional">
                            <input <?php echo $disabled ?> name="additional_fee_description_by_fa_update[]" type="text" value="<?php echo $additional_fee->additional_fee_description_by_fa ?>" />
                            <input name="additional_fee_id[]" type="hidden" value="<?php echo $additional_fee->additional_fee_id ?>" />
                        </div>
                        <div class="float-left">
                            <?php if($associate_data->fa_invoice_status != 'approved') { ?>
                            <a class="delete_additional_fee" href="<?php echo $additional_fee->additional_fee_id ?>"><img height="30px"
                                                                        src="/client/assets/img/delete_main_image.png"></a>
                            <?php } ?>
                        </div>
                        <?php if ($key == 0) { ?>
                        <div>
                            <?php if($associate_data->fa_invoice_status != 'approved') { ?>
                            <a class="add_additional_fee" href="#"><img height="30px"
                                                                        src="/client/assets/img/Nuvola_Green_Plus.svg.png"></a>
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <div class="clear"></div>
                    </div>
                <?php } ?>
                <?php if(empty($invoice_additional_fees)) { ?>
                <div>
                    <div class="float-left invoice-lable">
                        <span class="invoice-required">*</span>Additional Fees:
                    </div>
                    <div class="float-left invoice-input">
                        <input <?php echo $disabled ?> name="additional_fee_by_fa[]" type="text"/ >
                    </div>
                    <div class="float-left invoice-additional">
                        <input <?php echo $disabled ?> name="additional_fee_description_by_fa[]" type="text" placeholder="Please provide a short description of this cost"/>
                    </div>
                    <div>
                        <?php if($associate_data->fa_invoice_status != 'approved') { ?>
                        <a class="add_additional_fee" href="#"><img height="30px"
                                                                    src="/client/assets/img/Nuvola_Green_Plus.svg.png"></a>
                        <?php } ?>
                    </div>
                    <div class="clear"></div>
                </div>
                <?php } ?>
            </div>

        <div>
            <div class="float-left invoice-lable">
                Total:
            </div>

            <div class="float-left invoice-input">
                <input type="text"/>
            </div>

            <div class="clear"></div>
        </div>
        <?php if($associate_data->fa_invoice_status != 'approved') { ?>
            <p>Please upload your invoice for reference.</p>
            <input type="file" name="invoice_file" />
        <?php } ?>
        <?php if($associate_data->filename) { ?>
                <a href="<?php echo base_url() ?>fa/download_invoice/<?php echo $associate_data->country_id . '/' . $associate_data->case_id ?>">Attached invoice</a>
        <?php } ?>
        <?php if($associate_data->fa_invoice_status != 'approved') { ?>
            <input type="submit" class="blue_button submit_invoice" value="Upload" name="upload" style="height: 40px;">
            <input type="submit" class="blue_button submit_invoice" value="Submit" style="margin-bottom: 10px; height: 40px;">
        <?php } ?>
        </form>
    </div>
    <?php }  ?>



    </div>
</div>
</div>