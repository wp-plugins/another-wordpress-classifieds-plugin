<form action="<?php echo $paypal_url ?>" method="post">
	<?php if ($is_recurring): ?>
	<input type="hidden" name="cmd" value="_xclick-subscriptions" />
	<input type="hidden" name="a3" value="<?php echo $amount ?>" />
	<input type="hidden" name="p3" value="<?php echo $payment_period ?>" />
	<input type="hidden" name="t3" value="D" />
	<?php else: ?>
	<input type="hidden" name="cmd" value="_xclick" />
	<?php endif ?>

	<?php if ($is_test_mode_enabled): ?>
	<input type="hidden" name="test_ipn" value="1" />
	<?php endif ?>

	<input type="hidden" name="business" value="<?php echo get_awpcp_option('paypalemail') ?>" />
	<input type="hidden" name="no_shipping" value="1" />

	<input type="hidden" name="return" value="<?php echo $return_url ?>" />
	<input type="hidden" name="notify_url" value="<?php echo $notify_url ?>" />
	<input type="hidden" name="cancel_return" value="<?php echo $cancel_url ?>" />

	<input type="hidden" name="no_note" value="1" />
	<input type="hidden" name="quantity" value="1" />
	<input type="hidden" name="no_shipping" value="1" />
	<input type="hidden" name="rm" value="2" />
	<input type="hidden" name="item_name" value="<?php echo $item->name ?>" />
	<input type="hidden" name="item_number" value="<?php echo $item->id ?>" />
	<input type="hidden" name="amount" value="<?php echo $amount ?>" />
	<input type="hidden" name="currency_code" value="<?php echo $currency ?>" />
	<input type="hidden" name="custom" value="<?php echo $custom ?>" />
	<input type="hidden" name="src" value="1" />
	<input type="hidden" name="sra" value="1" />

	<?php $alt = __("Make payments with PayPal - it's fast, free and secure!","AWPCP") ?>
	<input type="image" src="<?php echo $awpcp_imagesurl ?>/paypalbuynow.gif" 
		   border="0" name="submit" alt="<?php echo $alt ?>" />
</form>