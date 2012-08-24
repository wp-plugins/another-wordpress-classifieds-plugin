	<?php _e("You're about to renew your Ad. Please select a payment method below and click Continue.", 'AWPCP'); ?>

	<form method="post">
		<?php $selected = awpcp_post_param('payment-method', false) ?>
		<?php $selected = empty($selected) ? array_shift(awpcp_get_properties($payment_methods, 'slug')) : $selected ?>
		
		<?php foreach ($payment_methods as $method): ?>
		<?php $id = "payment-method-{$method->slug}" ?>
		<input id="<?php echo $id ?>" type="radio" name="payment-method" value="<?php echo esc_attr($method->slug) ?>" <?php echo $method->slug == $selected ? 'checked="checked"' : '' ?> />
		<label for="<?php echo $id ?>"><strong><?php echo $method->name ?></strong></label><br/>
		<?php echo $method->description ?>
		<?php endforeach ?>		

		<p class="form-submit">
			<input class="button" type="submit" value="<?php _e('Continue', 'AWPCP') ?>" id="submit" name="submit">
			<input type="hidden" value="<?php echo esc_attr($transaction->id) ?>" name="awpcp-txn">
			<input type="hidden" value="checkout" name="step">
		</p>
	</from>