<div class="Websms_wrapper">
	<hr />
	<strong>Signup For Get API Details - <a href="http://newsletters.lk/">Get Your API</a></strong>	
	<hr />
	<table class="form-table">
		<tr valign="top">
			
			<th scrope="row"><?php _e('Newsletters.lk API Key',WebsmsConstants::TEXT_DOMAIN); ?>
				<span class="tooltip" data-title="Enter Websms API Key"><span class="dashicons dashicons-info"></span></span>
			</th>
			<td style="vertical-align: top;">
				<?php if($islogged){echo $websmslk_API_key;}?>
				<input type="text" name="Websms_gateway[websmslk_API_key]" id="Websms_gateway[websmslk_API_key]" value="<?php echo $websmslk_API_key; ?>" data-id="websmslk_API_key" class="<?php echo $hidden?>">
				<input type="hidden" name="action" value="save_websms_lk_settings" />
				<br />
				<span class="<?php echo $hidden?>"><?php _e( 'Your <b>Newsletters.lk</b> API Key', 'Websms' ); ?></span>
			</td>
		</tr>

		<tr valign="top">
			<th scrope="row"><?php _e( 'Newsletters.lk API Token', WebsmsConstants::TEXT_DOMAIN ) ?>
				<span class="tooltip" data-title="Newsletters.lk API Token"><span class="dashicons dashicons-info"></span></span>
			</th>
			<td >
				<?php if($islogged){echo $Websms_API_Token;}?>
				<input type="text" name="Websms_gateway[Websms_API_Token]" id="Websms_gateway[Websms_API_Token]" value="<?php echo $Websms_API_Token; ?>" data-id="Websms_API_Token" class="<?php echo $hidden?>">
				<br />
				<span class="<?php echo $hidden?>"><?php _e( 'Your <b>Newsletters.lk</b> API Token', WebsmsConstants::TEXT_DOMAIN ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scrope="row">
			<?php _e( 'Newsletters.lk Sender Id', WebsmsConstants::TEXT_DOMAIN ) ?>
			<span class="tooltip" data-title="Only available for transactional route"><span class="dashicons dashicons-info"></span></span>
			</th>
			<td >
				<?php if($islogged){echo $Websms_Sender_ID;}?>
				<input type="text" name="Websms_gateway[Websms_Sender_ID]" id="Websms_gateway[Websms_Sender_ID]" value="<?php echo $Websms_Sender_ID; ?>" data-id="Websms_Sender_ID" class="<?php echo $hidden?>">
				<br />
				<span class="<?php echo $hidden?>"><?php _e( 'Your <b>Newsletters.lk</b> API Token', WebsmsConstants::TEXT_DOMAIN ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scrope="row">
			</th>
			<td >
				<?php if($islogged){?>
				<a href="#" class="button-primary" onclick="logout(); return false;"><?php echo _e( 'Reset Account', WebsmsConstants::TEXT_DOMAIN );?></a>
				<?php }?>
			</td>
		</tr>
	</table>
</div>
				