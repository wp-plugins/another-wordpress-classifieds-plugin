<div id="classiwrapper">
	<?php if (!is_admin()): ?>
	<?php echo awpcp_menu_items() ?>
	<?php endif ?>

	<h2><?php _e('Upload Images') ?></h2>

	<?php foreach ((array) $header as $part): ?>
		<?php echo $part ?>
	<?php endforeach ?>

	<p>
		<?php _e('Image slots available', 'AWPCP') ?>: 
		<strong><?php echo $images_left ?></strong></br>
		<?php _e('Max image size', 'AWPCP') ?>: 
		<strong><?php echo $max_image_size/1000 ?> KB</strong>
	</p>

	<?php if (get_awpcp_option('imagesapprove') == 1): ?>
	<p>
		<?php _e('Image approval is in effect so any new images you upload will not be visible to viewers until an admin has approved it', 'AWPCP') ?>
	</p>
	<?php endif ?>

	<?php if ($images_left > 0): ?>
	<h3><?php _e('If adding images to your ad, select your image from your local computer', 'AWPCP') ?></h3>
	<?php endif ?>

	<?php if (!empty($form_errors)): ?>
	<ul>
		<?php foreach($form_errors as $error): ?>
		<li class="awpcp-error"><?php echo $error ?></li>
		<?php endforeach ?>
	</ul>
	<?php endif ?>

	<form id="awpcp-place-ad-upload-images-form" method="post" enctype="multipart/form-data">

		<div class="clearfix">
	<?php for ($i = 0; $i < $images_left; $i++): ?>
		<div class="uploadform">
			<input type="file" name="AWPCPfileToUpload<?php echo $i ?>" id="AWPCPfileToUpload<?php echo $i ?>" size="18" />
		</div>
	<?php endfor ?>
		</div>
		<br/>

		<p class="form-submit">
			<?php if ($images_uploaded <= 0): ?>
			<input class="button" type="submit" value="<?php _e('Place Ad without Images', 'AWPCP') ?>" id="submit" name="submit-no-images">
			<?php else: ?>
			<input class="button" type="submit" value="<?php _e('Finish', 'AWPCP') ?>" id="submit" name="submit-no-images">
			<?php endif ?>
			<?php if ($images_left > 0): ?>
			<input class="button" type="submit" value="<?php _e('Upload Images', 'AWPCP') ?>" id="submit" name="submit">
			<?php endif ?>
			<input type="hidden" name="a" value="store-images" />
			<input type="hidden" name="ad_id" value="<?php echo esc_attr($ad_id) ?>" />

			<input type="hidden" name="adid" value="<?php echo esc_attr($ad_id) ?>" />
			
			<?php /* ?>
			<input type="hidden" name="adtermid" value="$adterm_id" />
			<input type="hidden" name="nextstep" value="$nextstep" />
			<input type="hidden" name="adpaymethod" value="$adpaymethod" />
			<input type="hidden" name="adaction" value="$adaction" />
			<input type="hidden" name="adkey" value="$adkey" />
			<?php */ ?>
		</p>
	</form>
</div>