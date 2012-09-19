<div id="classiwrapper">
	<?php if (!is_admin()): ?>
	<?php echo awpcp_menu_items() ?>
	<?php endif ?>

	<h2><?php _e('Select Payment/Category') ?></h2>

	<?php foreach ($header as $part): ?>
	<p><?php echo $part ?></p>
	<?php endforeach ?>

	<form id="awpcp-place-ad-payment-step-form" method="post">
		<fieldset>
			<h3><?php _e('Please select a Category for your Ad') ?></h3>

			<label for="place-ad-category">Ad Category</label>
			<select id="place-ad-category" name="category">
				<option value="0"><?php _e('Select a Category') ?></option>
				<?php echo get_categorynameidall(awpcp_array_data('category', '', $form_values)); ?>
			</select>
			<?php $error = awpcp_array_data('category', '', $form_errors); ?>
			<?php if (!empty($error)): ?>
			<br/><span class="awpcp-error"><?php echo $error ?></span>
			<?php endif ?>
		</fieldset>

		<fieldset>
			<h3><?php _e('Please select a payment term for your Ad', 'AWPCP') ?></h3>
			<?php $error = awpcp_array_data('payment-term', '', $form_errors); ?>
			<?php if (!empty($error)): ?>
			<span class="awpcp-error"><?php echo $error ?></span>
			<?php endif ?>

			<table class="awpcp-table">
				<thead>
					<tr>
						<th>Payment Term</th>
						<th>Ads Allowed</th>
						<th>Images Allowed</th>
						<th>Characters Allowed</th>
						<th>Duration</th>
						<th>Price</th>
					</tr>
				</thead>
				<tbody>
					<?php $type = '' ?>
					<?php $selected = awpcp_array_data('payment-term', '', $form_values) ?>
					<?php foreach ($payment_terms as $term): ?>

					<?php if ($term->type != $type): ?>
					<tr class="awpcp-payment-term-type-header">
						<th colspan="5" scope="row"><?php echo $term->type_name ?></th>
					</tr>
					<?php endif ?>

					<tr class="js-awpcp-payment-term" data-price="<?php echo esc_attr($term->price) ?>" data-categories="<?php echo esc_attr(json_encode($term->categories)) ?>">
						<td>
							<?php $id = "{$term->type}-{$term->id}" ?>
							<?php $element_id = "payment-term-$id" ?>
							<input id="<?php echo $element_id ?>" type="radio" name="payment-term" value="<?php echo esc_attr($id) ?>" <?php echo $id == $selected ? 'checked="checked"' : '' ?> />
							<label for="<?php echo $id ?>"><strong><?php echo $term->name ?></strong></label><br/>
							<?php echo $term->description ?>
						</td>
						<td><?php echo $term->ads_allowed ?></td>
						<td><?php echo $term->images_allowed ?></td>
						<td><?php echo empty($term->characters_allowed) ? __('No Limit', 'AWPCP') : $term->characters_allowed ?></td>
						<td><?php echo $term->duration ?></td>
						<td><?php echo number_format($term->price, 2) ?></td>
					</tr>
					<?php $type = $term->type ?>

					<?php endforeach ?>
				</tbody>
			</table>
		</fieldset>

		<fieldset>
			<h3><?php _e('Please select a payment method', 'AWPCP') ?></h3>
			<?php $error = awpcp_array_data('payment-method', '', $form_errors); ?>
			<?php if (!empty($error)): ?>
			<span class="awpcp-error"><?php echo $error ?></span>
			<?php endif ?>

			<?php $selected = awpcp_array_data('payment-method', '', $form_values) ?>
			<?php $selected = empty($selected) ? array_shift(awpcp_get_properties($payment_methods, 'slug')) : $selected ?>
			<?php echo awpcp_payments_methods_form($selected) ?>
		</fieldset>

		<p class="form-submit">
			<input class="button" type="submit" value="<?php _e('Continue', 'AWPCP') ?>" id="submit" name="submit">
			<input type="hidden" value="<?php echo esc_attr($transaction->id) ?>" name="awpcp-txn">
			<input type="hidden" value="checkout" name="a">
		</p>
	</form>
</div>