<div id="classiwrapper">
	<?php if (!is_admin()): ?>
	<?php echo awpcp_menu_items() ?>
	<?php endif ?>

	<h2><?php _e('Step 2 of 4 - Checkout', 'AWPCP') ?></h2>

	<?php foreach ($header as $part): ?>
		<?php echo $part ?>
	<?php endforeach ?>

	<?php $text = __("Please click the button below to submit payment. You'll be asked to pay <strong>%0.2f</strong>", 'AWPCP'); ?>
	<p><?php echo sprintf($text, $transaction->get('amount')) ?></p>

	<?php echo $checkout_form ?>
</div>