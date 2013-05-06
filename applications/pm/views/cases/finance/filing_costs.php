<script type="text/javascript">
    $(document).ready(function(){
        $('.custom_accordion').click(function(){
            $('.finance_country_content').slideUp();
                $('#' + $(this).attr('ref')).slideDown();
        });
    });
</script>
<div id="finance-filing-costs">
    <?php foreach ($invoices as $invoice) { ?>
        <?php
            switch($invoice->fa_invoice_status) {
                case 'pending-approval':
                    $status = 'pending';
                    break;
                case 'rejected':
                    $status = 'rejected';
                    break;
                case 'approved':
                    $status = 'approved';
                    break;
                case 'pending-unlock':
                    $status = 'pending';
                    break;
                default:
                    $status = 'not_touched';
                    break;
            }

        if ($invoice->fee_currency == 'euro') {
            $currency_sign = '&euro;';
        } else {
            $currency_sign = '$';
        }

        ?>
        <div class="finance_country <?php echo $status ?> custom_accordion" ref="filing_cost_country_<?php echo $invoice->id ?>"><?php echo $invoice->country ?></div>
    <?php } ?>
    <?php foreach($invoices as $invoice) { ?>
        <div id="filing_cost_country_<?php echo $invoice->id ?>" class="finance_country_content" style="display: none;">
            <form action="" method="post">
                <div>
                    <h2><?php echo $invoice->country ?> - <?php echo $invoice->fa_invoice_status ?></h2>
                </div>
                <div class="clear"></div>
            <div class="float_left">
                Submitted by FA on <?php echo date('m/d/y' ,  strtotime($invoice->fa_invoice_sent)) ?>
                <div>
                    <div class="finance_label float_left">
                        Filing Fee:
                    </div>
                    <div class="finance_input float_left">

                        <?php echo $currency_sign ?><input readonly="true" value="<?php echo $invoice->fa_invoice_professional_fee ?>" type="text"/>
                    </div>
                    <div class="clear"></div>
                </div>
                <div>
                    <div class="finance_label float_left">
                        Official Fees:
                    </div>
                    <div class="finance_input float_left">
                        <?php
                            if ($invoice->fa_corrected_invoice_official_fee) {
                                // if PM corrected this value - show this
                                $fa_invoice_official_fee = $invoice->fa_corrected_invoice_official_fee;
                            } else {
                                // if not - let's show what was saved by FA
                                $fa_invoice_official_fee = $invoice->fa_invoice_official_fee ;
                            }
                        ?>
                        <?php echo $currency_sign ?><input name="fa_corrected_invoice_official_fee" value="<?php echo $fa_invoice_official_fee ?>" type="text"/>
                        <?php $summ = $invoice->fa_invoice_professional_fee + $fa_invoice_official_fee; ?>
                        <input name="associate_data_id" value="<?php echo $invoice->id ?>" type="hidden"/>

                    </div>
                    <div class="clear"></div>
                </div>
                <?php foreach($invoice->additional_fees as $additional_fee) { ?>
                    <?php
                        if ($additional_fee->additional_fee_corrected_by_pm) {
                            $additional_fee_value = $additional_fee->additional_fee_corrected_by_pm;
                        } else {
                            $additional_fee_value = $additional_fee->additional_fee_by_fa;
                        }
                    $summ += $additional_fee_value;
                    ?>
                    <div>
                        <div class="finance_label float_left">
                            <span class="required">*</span>Additional Fees:
                        </div>
                        <div class="finance_input float_left">
                            <input type="hidden" name="additional_fee_id[]" value="<?php echo $additional_fee->additional_fee_id ?>">
                            <?php echo $currency_sign ?><input name="additional_fee_corrected_by_pm[]" value="<?php echo $additional_fee_value ?>" type="text"/>
                        </div>
                        <div class="finance_input float_left" style="width: 170px;">
                            <input disabled="true" name="additional_fee_description_by_fa[]" value="<?php echo $additional_fee->additional_fee_description_by_fa ?>">
                        </div>
                        <div class="clear"></div>
                    </div>
                <?php } ?>
                <div>
                    <div class="finance_label float_left">
                        Total:
                    </div>
                    <div class="finance_input float_left">
                        <?php echo $currency_sign ?><input readonly="true" value="<?php echo $summ ?>" type="text"/>
                    </div>
                    <div class="clear"></div>
                </div>
                <div>
                    <div class="finance_label float_left">
                        <a href="<?php echo base_url() ?>cases/download_invoice/<?php echo $invoice->country_id . '/' . $invoice->case_id ?>">Attached invoice</a>
                    </div>
                </div>
            </div>
            <div class="float_left">
                Estimated by PM on <?php echo date('m/d/y' , strtotime($case['estimate_saved_by_pm'])) ?>

                <?php
                    if ($invoice->estimated_by_pm_filing_fee) {
                        $estimated_by_pm_filing_fee = $invoice->estimated_by_pm_filing_fee;
                    } else {
                        $estimated_by_pm_filing_fee = $filing_cost_result[$invoice->country_id]['result_filing_fee'];
                    }

                    if ($invoice->estimated_by_pm_official_fee) {
                        $estimated_by_pm_official_fee = $invoice->estimated_by_pm_official_fee;
                    } else {
                        $estimated_by_pm_official_fee = $filing_cost_result[$invoice->country_id]['result_official_fee'];
                    }

                    if ($invoice->estimated_by_pm_additional_fee) {
                        $estimated_by_pm_additional_fee = $invoice->estimated_by_pm_additional_fee;
                    } else {
                        $estimated_by_pm_additional_fee = $filing_cost_result[$invoice->country_id]['additional_summ'];
                    }

                $estimate_summ = $estimated_by_pm_filing_fee + $estimated_by_pm_official_fee + $estimated_by_pm_additional_fee;
                ?>
                <div>
                    <div class="finance_label float_left">
                        Filing Fee:
                    </div>
                    <div class="finance_input float_left">
                        <?php echo $currency_sign ?><input name="estimated_by_pm_filing_fee" value="<?php echo $estimated_by_pm_filing_fee ?>" readonly="true" type="text"/><br/>

                    </div>
                    <div class="clear"></div>
                </div>
                <div>
                    <div class="finance_label float_left">
                        Official Fees:
                    </div>
                    <div class="finance_input float_left">
                        <?php echo $currency_sign ?><input name="estimated_by_pm_official_fee" value="<?php echo $estimated_by_pm_official_fee ?>" readonly="true" type="text"/>
                    </div>
                    <div class="clear"></div>
                </div>
                <div>
                    <div class="finance_label float_left">
                        <span class="required">*</span>Additional Fees:
                    </div>
                    <div class="finance_input float_left">
                        <?php echo $currency_sign ?><input name="estimated_by_pm_additional_fee" value="<?php echo $estimated_by_pm_additional_fee ?>" readonly="true" type="text"/>
                    </div>
                    <div class="clear"></div>
                </div>
                <div>
                    <div class="finance_label float_left">
                        Total:
                    </div>
                    <div class="finance_input float_left">
                        <?php echo $currency_sign ?><input readonly="true" value="<?php echo $estimate_summ ?>" type="text"/>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
                <div class="finance_actions">

                <?php if($invoice->fa_invoice_status != 'approved') { ?>
                    <button  value="approved" name="fa_invoice_status" class="green">APPROVE</button>
                <?php } ?>
                    <button value="rejected" name="fa_invoice_status" class="light-red">Not Approved</button>
                    <button value="pending-unlock" name="fa_invoice_status" class="blue">Unlock Submit for FA</button>
                    <input type="hidden" name="invoice_edit" value="1">
                </div>
            </div>
            <div class="clear"></div>
            </form>
        </div>
    <?php } ?>
</div>