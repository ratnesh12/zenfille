<!--/*/*<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery.autocomplete.css-->
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery-ui/jquery-ui-1.8.16.custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery-ui/jquery.ui.tabs.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/tablesorter.css" />
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.core.min.js"></script>
<!--<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.autocomplete.js"></script>-->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ajaxupload.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.widget.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.tabs.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.cookie.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/typewatch.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.tablesorter.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#tabs").tabs({cookie: { expires: 30}  });

		$.tablesorter.addParser({ 
	        id: 'country_counts', 
	        is: function(s) { 
	            return false; 
	        }, 
	        format: function(s) { 
	            if(s.length==0)return 0;
            	return s.split(",").length++; 
	        }, 
	        type: 'numeric' 
	    }); 

		
		function run_tablesorter() {			
			$("table.tablesorter").each(function() {
				if (($(this).find("tbody").find("tr").length != undefined) && ($(this).find("tbody").find("tr").length > 0)){
					switch($(this).attr('id')){
					case 'active-cases-table':
					case 'pending-cases-table':
						var sortarray = [[6,1],[7,1],[1,0]];
						break;
					default:
						var sortarray = [[0,1]];
					}

					$(this).tablesorter({
						widgets: ['zebra'],
						sortList: sortarray,
						headers: {  
				         0 : { sorter: "digit"  },
				         1 : { sorter: "date"  },
				         2 : { sorter: "country_counts"  },
				         3 : { sorter: "text"  },
				         4 : { sorter: "complex"  }
				       }
					});	
				}	
			});
		}
		run_tablesorter();
		$(".sorter_hide").hide();
		$("#cases_switch_checkbox").attr('checked',false);		

		$("#cases_switch_checkbox").change(function() {
			if($(this).attr('checked')=='checked'){
				$('.data-table tr.other_case').hide();
			}else{
				$('.data-table tr.other_case').show();
			}
		});
		
		$(".input_search_case_number").click(function() {
			if ($(this).val() == "Case #") {
				$(this).val("");
			}
		});
		$(".input_search_case_number").focusout(function() {
			if ($(this).val() == "") {
				$(this).val("Case #");
			}
		});
		
		$(".cases_search_button").click(function() {
			var type = $(this).attr("id");
			var search_string = $("input#" + type).val();
			$("#" + type +"-cases-table-box").html("<center><img src='<?php echo base_url(); ?>assets/images/i/loading-blue.gif' /></center>");
			$.post("<?php echo base_url(); ?>dashboard/search_cases/", {search_string: search_string, type: type}, function(result) {
				$("#" + type +"-cases-table-box").html(result);
				run_tablesorter();
				result = null;
			});
		});
		
		var options = {
		    callback: function() { 
				var search_string = $(this.el).val();
				var type = $(this.el).attr("id");
				
				$("#" + type +"-cases-table-box").html("<center><img src='<?php echo base_url(); ?>assets/images/i/loading-blue.gif' /></center>");
				$.post("<?php echo base_url(); ?>dashboard/search_cases/", {search_string: search_string, type: type}, function(result) {
					$("#" + type +"-cases-table-box").html(result);
					run_tablesorter();
					result = null;					
				});
			},
		    wait: 850,
		    captureLength: 0
		}

		$(".input_search_case_number").typeWatch(options);
	});
</script>
<div id="tabs">
	<ul>
		<li><a href="#active-cases">Active Cases</a></li>
		<li><a href="#pending-cases">Pending Cases</a></li>
		<li><a href="#completed-cases">Completed Cases</a></li>
		
	</ul>
	<!-- Active Cases -->
	<div id="active-cases">
        <div class="cases_switch">
            <input type="checkbox" id="cases_switch_checkbox">
            <label for="cases_switch_checkbox">Show only My cases</label>
        </div>
	<p>
		<input type="text" name="input_search_case_number" id="active" class="big input_search_case_number" value="Case #" />
		<button class="cases_search_button light-red" id="active">Search</button>
		</p>
		<div id="active-cases-table-box">
		<table border="0" cellpadding="4" cellspacing="0" class="data-table tablesorter" id="active-cases-table">
			<colgroup>
				<col width="100px"/>
				<col width="80px"/>
				<col width="320px"/>
				<col width="190px"/>
				<col width="190px"/>
				<col width="95px"/>
				<col width="0px"/>
				<col width="0px"/>
			</colgroup>
			<thead>
				<tr>
					<th>ZenFile Reference #</th>
					<th>Filing Deadline</th>
					<th>Countries</th>
					<th>Client Reference #</th>
					<th>Client Name</th>
					<th>Manager</th>
					<th class="sorter_hide"></th>
					<th class="sorter_hide"></th>
				</tr>
			</thead>
			<tbody>
		<?php
			if (check_array($active_cases))
			{
				// Custom sorting functions to make entries with red box first
				
				function cmp($a, $b) 
				{
				}
				foreach ($active_cases as $active_case)

				{
					if ($active_case['highlight'] == '1' && strtotime(date('Y-m-d H:i:s')) >=  (strtotime($active_case['filing_deadline']) - 5*86400))
					{
                            $link_class = 'red-box';
                            $active_case['hot'] = 1;

					}else{
                        $link_class = 'empty-box';
                        $active_case['hot'] = 0;
                    }
					$regions = (isset($cases_regions[$active_case['id']])) ? $cases_regions[$active_case['id']] : array();
					// if case is intaken then show just approved countries
					if ($active_case['manager_id'] > 0)
					{
						$regions = (isset($approved_regions[$active_case['id']])) ? $approved_regions[$active_case['id']] : array();
					}

					$unread_email_link = ($active_case['new_email_sign'] == '1') ? anchor_popup('/emails/open_cases_email_box/'.$active_case['case_number'], '<img src="'.base_url().'assets/images/i/inbox-email.png" title="New Email" alt="New Email" /><span class="new-email-sign">1</span>') : '';
					if($active_case['filing_deadline']=='00/00/0000'){
						$order = 2;
					}elseif(trim($active_case['filing_deadline'])==''){
						$order = 1;
					}else{
						$order = 3;
					}
					?>
					
					<tr class="<?= ($this->session->userdata('manager_user_id')==$active_case['manager_id']?'my_case':'other_case') ?>">
						<td>
						<?= anchor(
							'/cases/view/'.$active_case['case_number'],
						 	$active_case['case_number'], 'class="'.$link_class.'"'
					 	)?>
					 	</td>
						<td><?= $active_case['filing_deadline']?></td>
						<td><?= implode(', ', $regions)?></td>
						<td><?= $active_case['reference_number']?></td>
						<td><?= $active_case['client_name']?></td>
						<td><?= $active_case['manager_name']?></td>
						<td class="sorter_hide"><?= $active_case['hot']?></td>
						<td class="sorter_hide"><?= $order?></td>
					</tr>
					<?php 
				}
			}
		?>
				</tbody>
			</table>
		</div>
	</div>
	<!-- End Active Cases -->
	
	<!-- Pending Cases -->
	<div id="pending-cases">
		<p>
		<input type="text" name="input_search_case_number" id="pending" class="big input_search_case_number" value="Case #" />
		<button class="cases_search_button light-red" id="pending">Search</button>
		</p>
		<div id="pending-cases-table-box">
		<table border="0" cellpadding="4" cellspacing="0" class="data-table tablesorter" id="pending-cases-table">
			<colgroup>
				<col width="100px"/>
				<col width="80px"/>
				<col width="320px"/>
				<col width="190px"/>
				<col width="190px"/>
				<col width="95px"/>
				<col width="0px"/>
				<col width="0px"/>
			</colgroup>
			<thead>
				<tr>
					<th>ZenFile Reference #</th>
					<th>Filing Deadline</th>
					<th>Countries</th>
					<th>Client Reference #</th>
					<th>Client Name</th>
					<th>Manager</th>
					<th class="sorter_hide"></th>
					<th class="sorter_hide"></th>
				</tr>
			</thead>
			<tbody>
		<?php
			if (check_array($pending_cases))
			{
				foreach ($pending_cases as $pending_case)
				{
					$link_class = '';
					if ($pending_case['common_status'] == 'estimating-reestimate')
					{
						$link_class = 'yellow-box';
						$pending_case['hot'] = 1;
					}
					elseif ($pending_case['common_status'] == 'pending-intake')
					{
						$link_class = 'green-box';
						$pending_case['hot'] = 2;
					}
					elseif ( ! empty($pending_case['approved_at']))
					{
						$link_class = 'green-box';
						$pending_case['hot'] = 2;
					}
					if (empty($link_class))
					{
						$link_class = 'empty-box';
						$pending_case['hot'] = 0;
					}
					
					$regions = (isset($cases_regions[$pending_case['id']])) ? $cases_regions[$pending_case['id']] : array();
					// if case is intaken then show just approved countries
					if ($pending_case['manager_id'] > 0)
					{
						$regions = (isset($approved_regions[$pending_case['id']])) ? $approved_regions[$pending_case['id']] : array();
					}
					$unread_email_link = ($pending_case['new_email_sign'] == '1') ? anchor_popup('/emails/open_cases_email_box/'.$pending_case['case_number'], '<img src="'.base_url().'assets/images/i/inbox-email.png" title="New Email" alt="New Email" /><span class="new-email-sign">1</span>') : '';
					if($pending_case['filing_deadline']=='00/00/0000'){
						$order = 2;
					}elseif(trim($pending_case['filing_deadline'])==''){
						$order = 1;
					}else{
						$order = 3;
					}
					?>
                    <tr>
						<td>
						<?= anchor('/cases/view/'.$pending_case['case_number'], 
							$pending_case['case_number'], 
							'class="'.$link_class.'"'
						)?>
						</td>
						<td><?= $pending_case['filing_deadline']?></td>
						<td><?= implode(', ', $regions)?></td>
						<td><?= $pending_case['reference_number']?></td>
						<td><?= $pending_case['client_name']?></td>
						<td><?= $pending_case['manager_name']?></td>
						<td class="sorter_hide"><?= $pending_case['hot']?></td>
						<td class="sorter_hide"><?= $order?></td>
					</tr>
					<?php 
				}
			}
		?>
				</tbody>
			</table>
		</div>
	</div>
	<!-- End Pending Cases -->
	
	<!-- Completed Cases -->
	<div id="completed-cases">
		<p>
		<input type="text" name="input_search_case_number" id="completed" class="big input_search_case_number" value="Case #" />
		<button class="cases_search_button light-red" id="completed">Search</button>
		</p>
		<div id="completed-cases-table-box">
		<table border="0" cellpadding="4" cellspacing="0" class="data-table tablesorter" id="complete-cases-table">
			<colgroup>
				<col width="100px"/>
				<col width="80px"/>
				<col width="320px"/>
				<col width="190px"/>
				<col width="190px"/>
				<col width="95px"/>
			</colgroup>
			<thead>
				<tr>
					<th>ZenFile Reference #</th>
					<th>Filing Deadline</th>
					<th>Countries</th>
					<th>Client Reference #</th>
					<th>Client Name</th>
					<th>Manager</th>
				</tr>
			</thead>
			<tbody>
		<?php
			if (check_array($completed_cases))
			{
				foreach ($completed_cases as $completed_case)
				{
					$regions = array();
//					if (empty($link_class))
//					{
						$link_class = 'empty-box';
					//}
					$regions = (isset($cases_regions[$completed_case['id']])) ? $cases_regions[$completed_case['id']] : array();
					
					// if case is intaken then show just approved countries
					if ($completed_case['manager_id'] > 0)
					{
						$regions = (isset($approved_regions[$completed_case['id']])) ? $approved_regions[$completed_case['id']] : array();
					}
					$unread_email_link = ($completed_case['new_email_sign'] == '1') ? anchor_popup('/emails/open_cases_email_box/'.$completed_case['case_number'], '<img src="'.base_url().'assets/images/i/inbox-email.png" title="New Email" alt="New Email" /><span class="new-email-sign">1</span>') : '';
					$filing_deadline = '';
					if ($completed_case['case_type_id'] == '1')
					{						
						$_30_month_filing_deadline = new Datetime($completed_case['30_month_filing_deadline_orig']);
						$_31_month_filing_deadline = new Datetime($completed_case['31_month_filing_deadline_orig']);
						$today = new Datetime(date('Y-m-d'));
						
						$filing_deadline = ($today > $_30_month_filing_deadline) ? $completed_case['31_month_filing_deadline'] : $completed_case['30_month_filing_deadline'];
						
if (empty($completed_case['31_month_filing_deadline']) || $completed_case['31_month_filing_deadline'] == '00/00/0000')
						{
							$filing_deadline = $completed_case['30_month_filing_deadline'];
						}
					}
					elseif ($completed_case['case_type_id'] == '2')
					{
						$filing_deadline = $completed_case['case_filing_deadline'];
					}
					elseif ($completed_case['case_type_id'] == '3')
					{
						$filing_deadline = $completed_case['case_filing_deadline'];
					}
					
					?>
					<tr>
						<td>
						<?= anchor('/cases/view/'.$completed_case['case_number'], 
							$completed_case['case_number'], 
							'class="'.$link_class.'"'
						)?>
						</td>
						<td><?= $filing_deadline?></td>
						<td><?= implode(', ', $regions)?></td>
						<td><?= $completed_case['reference_number']?></td>
						<td><?= $completed_case['client_name']?></td>
						<td><?= $completed_case['manager_name']?></td>
					</tr>
					<?php 
				}
			}
		?>
				</tbody>
			</table>
		</div>
	</div>
	<!-- End Completed Cases -->
</div>