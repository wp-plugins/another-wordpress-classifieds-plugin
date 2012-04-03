<div id="classiwrapper">
	<?php echo awpcp_menu_items() ?>

	<h2><?php echo $texts['title'] ?></h2>

	<?php foreach ($header as $part): ?>
		<?php echo $part ?>
	<?php endforeach ?>

	<?php if ($status === $transaction->COMPLETED || $status === $transaction->PENDING): ?>

	<?php $url = $transaction->get('success-redirect') ?>
	<form id="awpcp-payment-thank-you-form" method="post" action="<?php echo esc_attr($url) ?>">
		<h3><?php echo $texts['subtitle'] ?></h3>
		<p><?php echo $texts['text'] ?></p>
		<p class="form-submit">
			<input class="button" type="submit" value="<?php _e('Continue', 'AWPCP') ?>" id="submit" name="submit" />
			<input type="hidden" value="<?php echo esc_attr($transaction->id) ?>" name="awpcp-txn" />
			<?php foreach ((array) $transaction->get('success-form') as $field => $value): ?>
			<input type="hidden" value="<?php echo esc_attr($value) ?>" name="<?php echo esc_attr($field) ?>" />
			<?php endforeach ?>
		</p>
	</form>

	<?php else: ?>

	<p><?php _e('Your payment was rejected. If you think this is an error please contact the website Administrator.', 'AWPCP') ?></p>

	<?php endif?>
</div>