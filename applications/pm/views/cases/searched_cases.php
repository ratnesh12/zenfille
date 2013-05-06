<script type="text/javascript">
$("#cases_switch_checkbox").attr('checked',false);
</script>

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
			if (check_array($searched_cases))
			{
				foreach ($searched_cases as $searched_case)
				{
					$filing_deadline = '';
					if ($searched_case['case_type_id'] == '1')
					{						
						$_30_month_filing_deadline = new Datetime($searched_case['30_month_filing_deadline_orig']);
						$_31_month_filing_deadline = new Datetime($searched_case['31_month_filing_deadline_orig']);
						$today = new Datetime(date('Y-m-d'));
						$filing_deadline = ($today > $_30_month_filing_deadline) ? $searched_case['31_month_filing_deadline'] : $searched_case['30_month_filing_deadline'];
						if (empty($searched_case['31_month_filing_deadline']) || $searched_case['31_month_filing_deadline'] == '00/00/0000')
						{
							$filing_deadline = $searched_case['30_month_filing_deadline'];
						}
					}
					elseif ($searched_case['case_type_id'] == '2')
					{
						$filing_deadline = $searched_case['filing_deadline'];
					}
					elseif ($searched_case['case_type_id'] == '3')
					{
						$filing_deadline = $searched_case['filing_deadline'];
					}
					?>

					<tr class="<?= ($this->session->userdata('manager_user_id')==$searched_case['manager_id']?'my_case':'other_case') ?>">
						<td>
						<?= anchor('/cases/view/'.$searched_case['case_number'], 
							$searched_case['case_number'], 
							'class="'.$searched_case['link_class'].'"'
						)?>
						</td>
						<td><?= $filing_deadline?></td>
						<td><?= implode(', ', $searched_case['regions'])?></td>
						<td><?= $searched_case['reference_number']?></td>
						<td><?= $searched_case['client_name']?></td>
						<td><?= $searched_case['manager_name']?></td>
					</tr>
					<?php 
				}
			}
		?>
				</tbody>
			</table>
