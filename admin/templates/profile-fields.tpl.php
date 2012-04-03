<h3><?php _e('AWPCP Profile Info', 'AWPCP') ?></h3>
<table class="form-table"><table class="form-table">
	<tbody>
		<?php /*<tr valign="top">
			<th scope="row">
				<label for="awpcp-profile-username"><?php _e('Username', 'AWPCP') ?></label>
			</th>
			<td>
				<input id="awpcp-profile-username" class="regular-text" type="text" name="awpcp-profile[username]" value="<?php echo $profile['username'] ?>" />
				<span class="description"><?php _e('If not empty will override your WordPress username.', 'AWPCP') ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="awpcp-profile-email"><?php _e('Email Adress', 'AWPCP') ?></label>
			</th>
			<td>
				<input id="awpcp-profile-email" class="regular-text" type="text" name="awpcp-profile[email]" value="<?php echo $profile['email'] ?>" />
				<span class="description"><?php _e('If not empty will override your WordPress email address.', 'AWPCP') ?></span>
			</td>
		</tr> */ ?>
		<tr valign="top">
			<th scope="row">
				<label for="awpcp-profile-address"><?php _e('Address', 'AWPCP') ?></label>
			</th>
			<td>
				<input id="awpcp-profile-address" class="regular-text" type="text" name="awpcp-profile[address]" value="<?php echo esc_attr($profile['address']) ?>" />
				<span class="description"></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="awpcp-profile-state"><?php _e('State', 'AWPCP') ?></label>
			</th>
			<td>
				<input id="awpcp-profile-state" class="regular-text" type="text" name="awpcp-profile[state]" value="<?php echo esc_attr($profile['state']) ?>" />
				<span class="description"></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="awpcp-profile-city"><?php _e('City', 'AWPCP') ?></label>
			</th>
			<td>
				<input id="awpcp-profile-city" class="regular-text" type="text" name="awpcp-profile[city]" value="<?php echo esc_attr($profile['city']) ?>" />
				<span class="description"></span>
			</td>
		</tr>
	</tbody>
</table>