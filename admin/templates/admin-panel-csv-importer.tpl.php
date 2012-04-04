<?php $page_id = 'awpcp-admin-csv-importer' ?>
<?php $page_title = __('AWPCP Classifieds Management System: Import Ad', 'AWPCP') ?>

<?php include(AWPCP_DIR . 'admin/templates/admin-panel-header.tpl.php') ?>

<?php if ($import_count > 0 || $reject_count > 0): ?>
	<?php if ($test_import): ?>
			<h3><?php _e('Import Testing Results', 'AWPCP'); ?></h3>
	<?php else: ?>
			<h3><?php _e('Final Import Results', 'AWPCP'); ?></h3>
	<?php endif ?>

			<ul>
				<li><b><?php _e('Imported rows:', 'AWPCP') ?></b> <?php echo $import_count ?></li>
				<li><b><?php _e('Imported Picture count:', 'AWPCP') ?></b> <?php echo $pic_import_count ?></li>
				<li><b><?php _e('Rejected rows:', 'AWPCP') ?></b> <?php echo $reject_count ?></li>
			</ul>
<?php endif ?>

	<link rel="stylesheet" href="<?php echo AWPCP_URL ?>js/datepicker/cupertino/jquery-ui-1.8.16.custom.css"/>
	<script type='text/javascript' src='<?php echo site_url(); ?>/wp-includes/js/jquery/jquery.js?ver=1.6.1'></script>
    <script type='text/javascript' src='<?php echo AWPCP_URL ?>js/datepicker/jquery-ui-1.8.16.datepicker.min.js'></script>

			<?php if (!empty($messages)): ?>
				<h3>Messages</h3>
				<ul>
				<?php foreach ($messages as $message): ?>
					<li><?php echo "$message" ?></li>
				<?php endforeach ?>
				</ul>
			<?php endif ?>

			<?php if (!empty($import_errors)): ?>
				<h3>Errors</h3>
				<ul>
				<?php foreach ($import_errors as $error): ?>
					<li><?php echo "$error" ?></li>
				<?php endforeach ?>
				</ul>
			<?php endif ?>
		
			<script type="text/javascript">
				(function($){
					$(function() {
						$('#awpcp-importer-start-date, #awpcp-importer-end-date').datepicker({
							changeMonth: true,
							changeYear: true
						});
						$('#awpcp-importer-auto-assign-user').change(function(event) {
							if (!$(this).attr('checked') || !$(this).prop('checked')) {
								console.log('hmm');
								$('#awpcp-importer-user').attr('disabled', 'disabled');
							} else {
								$('#awpcp-importer-user').removeAttr('disabled');
							}
						}).change();
					});
				})(jQuery);
			</script>

			<form enctype="multipart/form-data" method="post">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="awpcp-importer-csv-file"><?php _e('CSV file', 'AWPCP') ?></label>
							</th>
							<td>
								<input id="awpcp-importer-csv-file" type="file" name="import" id="import" />
								<br/><span class="error"><?php echo awpcp_array_data('import', '', $form_errors) ?></span>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="awpcp-importer-zip-file"><?php _e('Zip file containing images', 'AWPCP') ?></label>
							</th>
							<td>
								<input id="awpcp-importer-zip-file" type="file" name="import_zip" id="import_zip" />
								<br/><span class="error"><?php echo awpcp_array_data('import_zip', '', $form_errors) ?></span>
							</td>
						</tr>

						<tr><th scope="row" colspan="2"><b><?php _e('You can configure default dates for imported Ads', 'AWPCP') ?></b></th></tr>
						<tr>
							<th scope="row">
								<label for="awpcp-importer-start-date"><?php _e('Start Date (mm/dd/yyyy)', 'AWPCP') ?></label>
							</th>
							<td>
								<input id="awpcp-importer-start-date" type="text" name="startDate" value="<?php echo esc_attr($start_date) ?>" />
								<br/><span class="error"><?php echo awpcp_array_data('startDate', '', $form_errors) ?></span>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="awpcp-importer-end-date"><?php _e('End Date (mm/dd/yyyy)', 'AWPCP') ?></label>
							</th>
							<td>
								<input id="awpcp-importer-end-date" type="text" name="endDate" value="<?php echo esc_attr($end_date) ?>" />
								<br/><span class="error"><?php echo awpcp_array_data('endDate', '', $form_errors) ?></span>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php _e('Date Format', 'AWPCP') ?>
							</th>
							<td>
								<br/><span class="error"><?php echo awpcp_array_data('date_fmt', '', $form_errors) ?></span>
								<input id="awpcp-importer-format-us-date" type="radio" name="date_fmt" 
									   value="us_date" <?php if ($import_date_format == "us_date") echo "checked"; ?> />
								<label for="awpcp-importer-format-us-date">
									<?php _e('US Date Only (mm/dd/year)', 'AWPCP') ?></label>
								<br/>

								<input id="awpcp-importer-format-uk-date" type="radio" name="date_fmt" 
									   value="uk_date" <?php if ($import_date_format == "uk_date") echo "checked"; ?> />
								<label for="awpcp-importer-format-uk-date">
									<?php _e('UK Date Only (dd/mm/year)', 'AWPCP') ?></label>
								<br/>
								
								<input id="awpcp-importer-format-us-date-time" type="radio" name="date_fmt" 
									   value="us_date_time" <?php if ($import_date_format == "us_date_time") echo "checked"; ?> />
								<label for="awpcp-importer-format-us-date-time">
									<?php _e('US Date and Time (mm/dd/year hh:mm:ss)', 'AWPCP') ?></label>
								<br/>
								
								<input id="awpcp-importer-format-uk-date-time" type="radio" name="date_fmt" 
									   value="uk_date_time" <?php if ($import_date_format == "uk_date_time") echo "checked"; ?> />
								<label for="awpcp-importer-format-uk-date-time">
									<?php _e('UK Date and Time (dd/mm/year hh:mm:ss)', 'AWPCP') ?></label>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php _e('Separators Used in CSV', 'AWPCP') ?>
							</th>
							<td>
								<label for="awpcp-importer-date-separator"><?php _e('Date Separator', 'AWPCP') ?></label>
								<input id="awpcp-importer-date-separator" type="text" maxlength="1" size="1" 
									   name="sep_date" value="<?php echo esc_attr($date_sep); ?>" />
								<br/><span class="error"><?php echo awpcp_array_data('sep_date', '', $form_errors) ?></span>

								<label for="awpcp-importer-time-separator"><?php _e('Time Separator', 'AWPCP') ?></label>
								<input id="awpcp-importer-time-separator" type="text" maxlength="1" size="1" 
									   name="sep_time" value="<?php echo esc_attr($time_sep); ?>" />
								<br/><span class="error"><?php echo awpcp_array_data('sep_time', '', $form_errors) ?></span>

								<label for="awpcp-importer-image-separator"><?php _e('Image Separator', 'AWPCP') ?></label>
								<input id="awpcp-importer-image-separator" type="text" maxlength="1" size="1" 
									   name="sep_image" value=";" disabled="disabled" /> <?php _e('(semi-colon)', 'AWPCP') ?>
								<br/><span class="error"><?php echo awpcp_array_data('sep_image', '', $form_errors) ?></span>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php _e('Category Handling on Import', 'AWPCP') ?>
							</th>
							<td>
								<input type="checkbox" name="auto_cat" id="auto_cat" value="1" <?php if ($auto_cat == "1") echo "checked"; ?> />
								<label for="awpcp-importer-auto-create-categories"><?php _e('Auto create categories', 'AWPCP') ?></label>
								<br/><span class="error"><?php echo awpcp_array_data('auto_cat', '', $form_errors) ?></span>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<?php _e('Assign Ads to an user?', 'AWPCP') ?>
							</th>
							<td>
								<input type="checkbox" name="assign_user" id="awpcp-importer-auto-assign-user" value="1" 
									   <?php echo $assign_user == 1 ? 'checked="checked"' : '' ?> />
								<label for="awpcp-importer-auto-assign-user"><?php _e('Assign Ads to an user?', 'AWPCP') ?></label><br/>
								<span class="description"><?php _e("If unchecked, Ads won't be associated to an user.", 'AWPCP') ?></span>
								<br/><span class="error"><?php echo awpcp_array_data('assign-user', '', $form_errors) ?></span>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="awpcp-importer-user"><?php _e('Default user', 'AWPCP') ?></label>
							</th>
							<td>
								<select id="awpcp-importer-user" name="user">
									<option value=""><?php _e('use spreadsheet information', 'AWPCP') ?></option>
								<?php foreach (awpcp_get_users() as $user): ?>
									<option value="<?php echo esc_attr($user->ID) ?>" 
										<?php echo $assigned_user == $user->ID ? 'selected="selected"' : '' ?>>
										<?php echo $user->user_login ?></option>
								<?php endforeach ?>
								</select><br/>
								<span class="description"><?php _e("Ads will be associated to this user if the username column is not present in the CSV file, there is no user with that username and we couldn't find an user with the contact_email address specified in the CSV file.", 'AWPCP') ?></span>
								<br/><span class="error"><?php echo awpcp_array_data('user', '', $form_errors) ?></span>
							</td>
						</tr>
					</tbody>
				</table>

				<p class="submit">
					<input type="submit" class="button" name="import_type" value="<?php _e('Test Import', 'AWPCP') ?>"></input>
					<input type="submit" class="button-primary button" name="import_type" value="<?php _e('Import', 'AWPCP') ?>"></input>
				</p>
			</form>
		</div><!-- end of .awpcp-main-content -->
	</div><!-- end of .page-content -->
</div><!-- end of #page_id -->