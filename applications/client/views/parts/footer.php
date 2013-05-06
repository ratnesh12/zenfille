				<!--CONTENT-->
			</div><!--in_frame-->
		</div><!--frame-->
	</div><!--centr_3-->
</div><!--content-->

<script type="text/javascript">
	$(document).ready(function(){

		$.post(
			"<?= base_url(); ?>cases/ajax_get_user_managers/<?= $this -> session -> userdata('client_user_id')?>", 
			{},
			function(result){
				$('#pm_name').html(result.pm);
				$('#bdv_name').html(result.bdv);
			},
			"json"
		);

		$('#support_submit').click(function(){

			var message = $('.support_form textarea[name=message]').val();
			var level = $('.support_form select[name=level]').val();
			if(!message){
				stanOverlay.setTITLE("Error!");
	            stanOverlay.setMESSAGE("Please, fill the message field!");
	            stanOverlay.SHOW();
	            return false;
			}
			$.post(
				"<?= base_url(); ?>cases/ajax_send_support_email/", 
				{message:message, level:level},
				function(result){
					if(result.result == 'ok'){
						stanOverlay.setTITLE("Information!");
			            stanOverlay.setMESSAGE("Your message has been sent!");
			            stanOverlay.SHOW();
					}else{
						stanOverlay.setTITLE("Error!");
			            stanOverlay.setMESSAGE("Your message has not been sent!");
			            stanOverlay.SHOW();
					}
				},
				"json"
			);
			return false;
		});

		function form_show(){
			$('#support_form').show();
			$('#support_form').attr('ref','hide');
			$('#support_form').animate(
					{width : '598px'},
					1000
			);
			$('#support_form').animate(
					{height : '339px'},
					1000
			);
		};

		function form_hide(){
			$('#support_form').attr('ref','show');
			$('#support_form').animate(
					{height : '40px'},
					1000
			);
			$('#support_form').animate(
					{width : '0px'},
					1000,
					function(){$('#support_form').hide();}
			);
		};
		
		$('#support_action').click(function(){
			$('#support_form').stop();
			$('#support_form').stop();
			if($('#support_form').attr('ref')=='show'){
				form_show();
			}else{
				form_hide();
			}
			return false;
		});
	});
</script>

<div class="support">
	<a href="#" id="support_action" class="support_action"></a>
	<div class="support_form" id="support_form" ref="show">
		<div class="support_form_header" id="support_form_header">Contact Form</div>
		<div class="support_form_content">
			<div>
				<div class="support_form_content_managers">
					<div>You Project Manager is:</div>
					<div> <span id="pm_name"></span></div>
					<div>You Account manager is:</div>
					<div> <span id="bdv_name"></span></div>
				</div>
				<div class="support_form_content_level">
					<div class="support_form_content_column">Urgency Level</div>
					<div class="support_form_content_dropdown ">
						<select name="level">
							<option value="low">Low</option>
							<option value="medium">Medium</option>
							<option value="high">High</option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
			</div>
			<div>
				<div class="support_form_content_label">Your message</div>
				<div class="support_form_content_message">
					<textarea name="message"></textarea>
				</div>
				<div class="clear"></div>
			</div>
			<div class="support_form_actions">
				<a href="#" class="support_submit" id="support_submit"></a>
				<div class="clear"></div>
			</div>
			
		</div>
	</div>
	<div class="clear"></div>
</div>

<div class="cl"></div>	
		<div class="footer">
			<a href="/" class="logo"></a>
		</div>
	</body>
</html>