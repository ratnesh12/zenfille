<div class="content_header dashboard">
		<div class="title">Dashboard</div>
		<div class="case_search">
            <?php echo form_open('/fa');?>
			<input type="text" value="" name="search" placeholder="Search..." id="case_search"/>
            <?php echo form_close();?>
		</div>
	</div>
	
	<div class="tabs_container">
		<ul class="tabs">
			<li><a href="#active" class="first">Active</a></li>
			<li><a href="#completed" class="last">Completed</a></li>
		</ul>
		<div id="active">
			<table class="table">						
				<thead>
					<tr>
						<th class="td_1_2"><?php echo $this->config->item('title_of_the_site') ?> Case #</th>
						<th class="td_1_2">Your Ref #</th>
						<th class="td_3">Applicant</th>
						<th class="td_4">Application#</th>
						<th class="td_5">Filing Deadline</th>
					</tr>
				</thead>
				<tbody>
				<?php if (check_array($active_cases)):?>
					<?php foreach($active_cases as $k => $case):
					
					if ($k & 1)
						$rowClass = 'odd';
					else
						$rowClass = 'even';
                        if($case['case_type_id'] =='1'){
                          $filing_deadline = $case[$case['country_filing_deadline'].'_month_filing_deadline'];
                        }else{
                            $filing_deadline = $case['filing_deadline'];
                        }
                        if (strtotime(date('Y-m-d H:i:s')) >=  (strtotime($filing_deadline) - 4*86400))
                        {
                            $rowClass = 'alert';
                        }

					?>
					
					<tr class="<?=$rowClass?>">
						<td class="td_1_2"><a href="<?php echo site_url('fa/case_fees/'.$case['case_number'])?>"><?php echo $case['case_number'].' '.$case['country']?></a></td>
						<td class="td_1_2"><?php echo $case['fa_reference_number']?></td>
						<td class="td_3"><?php echo $case['applicant']?></td>
						<td class="td_4"><?php echo $case['application_number']?></td>
						<td class="td_5"><?php echo date($this->config->item('client_date_format') , strtotime($filing_deadline))?></td>
					</tr>
					<?php endforeach?>
				<?php endif?>
				</tbody>
			</table>
		</div>
		
		
		<div id="completed">
			<table class="table">						
				<thead>
					<tr>
						<th class="td_1_2"><?php echo $this->config->item('title_of_the_site') ?> Case #</th>
						<th class="td_1_2">Client Ref #</th>
						<th class="td_3">Applicant</th>
						<th class="td_4">Application#</th>
					</tr>
				</thead>
				<tbody>
				<?php if (check_array($completed_cases)):?>
					<?php foreach($completed_cases as $k => $case):
					
					if ($k & 1)
						$rowClass = 'odd';
					else
						$rowClass = 'even';
					?>
					<tr class="<?=$rowClass?>">
						<td class="td_1_2"><a href="<?php echo site_url('fa/case_fees/'.$case['case_number'])?>"><?php echo $case['case_number'].' '.$case['country']?></a></td>
						<td class="td_1_2"><?php echo $case['fa_reference_number'] ?></td>
						<td class="td_3"><?php echo $case['applicant']?></td>
						<td class="td_4"><?php echo $case['application_number']?></td>
					</tr>
					<?php endforeach?>
				<?php endif?>
				</tbody>
			</table>
		</div>
	</div>