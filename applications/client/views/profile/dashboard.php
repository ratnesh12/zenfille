<script type="text/javascript">
$(document).ready(function(){
	<?php 
		if($inform_message = $this->session->userdata('inform_message')){
			$this->session->set_userdata('inform_message','');
	?>
	stanOverlay.setTITLE('User created');
    stanOverlay.setMESSAGE('<?=$inform_message?>');
    stanOverlay.SHOW();
	
	<?php }?>
});
</script>

<style>
    .active_1 , .completed_1 {
        width: 90px;
    }

    .active_2 , .completed_2 {
        width: 150px;
    }

    .active_3 , .completed_3 {

    }

    .active_4 , .completed_4 {
        width: 145px;
    }

    .active_5 , .completed_5 {

    }

    .active_6 , .completed_6 {
        width: 120px;
    }

    .pending_1 {
        width: 55px;
    }

    .pending_2 {
        width: 105px ;
    }

    .pending_3 {

    }

    .pending_4 {
        width: 140px;
    }

    .pending_5 {

    }

    .pending_6 {
        width: 95px;
    }

    .pending_7 {
        width: 100px;
    }




</style>

<div class="content_header dashboard">
		<div class="title">Dashboard</div>
	</div>
	
	<div class="tabs_container">
		<ul class="tabs">
			<li><a href="#active" class="first">Active</a></li>
			<li><a href="#pending" class="">Pending</a></li>
			<li><a href="#completed" class="last">Completed</a></li>
		</ul>
		<div id="active">
			<table class="table table_for_sort">
				<thead>
					<tr>
						<th><?php echo $this->config->item('title_of_the_site') ?> Case #</th>
						<th>Client Ref #</th>
						<th>Applicant</th>
						<th>Application#</th>
						<?php if($this->session->userdata('client_type')=='firm'){?>
							<th>Case Contact</th>
						<?php }?>
                        <th>Date added</th>
					</tr>
				</thead>
				<tbody>
				<?php if (check_array($active_cases)){?>
					<?php foreach($active_cases as $k => $case) {
                        $firstname = '';
                        $lastname = '';
                        $username = '';
                        if(isset($case['firstname'])){
                            $firstname = $case['firstname'];
                        }
                        if(isset($case['lastname'])){
                            $lastname = $case['lastname'];
                        }
                        if(isset($case['username'])){
                            $username = $case['username'];
                        }
					if ($k & 1)
						$rowClass = 'odd';
					else
						$rowClass = 'even';
					?>
					<tr class="<?=$rowClass?>">
						<td class="active_1"><a href="<?php echo site_url('cases/view/'.$case['case_number'])?>"><?php echo $case['case_number']?></a></td>
						<td class="active_2"><?php echo $case['reference_number']?></td>
						<td class="active_3"><?php echo $case['applicant']?></td>
						<td class="active_4"><?php echo $case['application_number']?></td>
						<?php if($this->session->userdata('client_type')=='firm'){?>
                        <td class="active_5">
                            <?php echo $firstname  .' '. $lastname ?>
                        </td>
						<?php }?>
                        <td class="td_4 active_6"><?php echo date($this->config->item('client_date_format') , strtotime($case['created_at'])) ?></td>
					</tr>
					<?php } ?>
				<?php }?>
				</tbody>
			</table>
		</div>
		
		<div id="pending">
			<table class="table table_for_sort">
				<thead>
					<tr>
						<th><?php echo $this->config->item('title_of_the_site') ?> Case #</th>
						<th>Client Ref #</th>
						<th>Applicant</th>
						<th>Application#</th>
						<?php if($this->session->userdata('client_type')=='firm'){?>
							<th>Case Contact</th>
						<?php }?>
<!--						<th class="td_5">Approved at</th>-->
                        <th>Approve by</th>
                        <th>Date added</th>
					</tr>
				</thead>
				<tbody>
				<?php if (check_array($pending_cases)){
                    //var_dump($pending_cases);exit;
                    foreach($pending_cases as $k => $case) {

$firstname = '';
$lastname = '';
$username = '';
        if(isset($case['firstname'])){
            $firstname = $case['firstname'];
        }
        if(isset($case['lastname'])){
            $lastname = $case['lastname'];
        }
        if(isset($case['username'])){
            $username = $case['username'];
        }
					
					if ($k & 1)
						$rowClass = 'odd';
					else
						$rowClass = 'even';
					?>
					<tr class="<?=$rowClass?>">
						<td class="pending_1"><a href="<?php echo site_url('cases/view/'.$case['case_number'])?>"><?php echo $case['case_number']?></a></td>
						<td class="pending_2"><?php echo $case['reference_number']?></td>
						<td class="pending_3"><?php echo $case['applicant']?></td>
						<td class="pending_4"><?php echo $case['application_number']?></td>
						<?php if($this->session->userdata('client_type')=='firm'){?>
							<td class="td_5 pending_5">
                                <?php echo $firstname  .' '. $lastname ?>
                            </td>
						<?php }?>
						<!-- <td class="td_5">
                            <?php // echo isset($case['approved_at']) ? gmdate($this->config->item('client_date_format') ,strtotime($case['approved_at'].'UTC')) : ''?>
                        </td> -->
                        <td class="pending_6"><?php if (!empty($case['approve_by'])) {echo date($this->config->item('client_date_format') , strtotime($case['approve_by']));}  ?></td>
                        <td class="pending_7"><?php echo date($this->config->item('client_date_format') , strtotime($case['created_at'])) ?></td>
					</tr>
					<?php } ?>
				<?php }?>
				</tbody>
			</table>
		</div>
		
		<div id="completed">
			<table class="table table_for_sort">
				<thead>
					<tr>
						<th><?php echo $this->config->item('title_of_the_site') ?> Case #</th>
						<th>Client Ref #</th>
						<th>Applicant</th>
						<th>Application#</th>
						<?php if($this->session->userdata('client_type')=='firm'){?>
							<th>Case Contact</th>
						<?php }?>
                        <th>Date added</th>
					</tr>
				</thead>
				<tbody>
				<?php if (check_array($completed_cases)):?>
					<?php foreach($completed_cases as $k => $case):
                        $firstname = '';
                        $lastname = '';
                        $username = '';
                        if(isset($case['firstname'])){
                            $firstname = $case['firstname'];
                        }
                        if(isset($case['lastname'])){
                            $lastname = $case['lastname'];
                        }
                        if(isset($case['username'])){
                            $username = $case['username'];
                        }
                        if ($k & 1)
						$rowClass = 'odd';
					else
						$rowClass = 'even';
					
					?>
					<tr class="<?=$rowClass?>">
						<td class="completed_1"><a href="<?php echo site_url('cases/view/'.$case['case_number'])?>"><?php echo $case['case_number']?></a></td>
						<td class="completed_2"><?php echo $case['reference_number']?></td>
						<td class="completed_3"><?php echo $case['applicant']?></td>
						<td class="completed_4"><?php echo $case['application_number']?></td>
						<?php if($this->session->userdata('client_type')=='firm'){?>
                        <td class="completed_5">
                            <?php echo $firstname  .' '. $lastname ?>
                        </td>
						<?php }?>
                        <td class="td_4 completed_6"><?php echo date($this->config->item('client_date_format') , strtotime($case['created_at'])) ?></td>
					</tr>
					<?php endforeach?>
				<?php endif?>
				</tbody>
			</table>
		</div>
	</div>