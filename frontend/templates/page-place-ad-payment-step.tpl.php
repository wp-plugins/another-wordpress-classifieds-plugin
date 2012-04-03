<div id="classiwrapper">
	<?php if (!is_admin()): ?>
	<?php echo awpcp_menu_items() ?>
	<?php endif ?>

	<h2><?php _e('Step 1 of 4 - Payment Information') ?></h2>

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
			<br/><span class="error erroralert"><?php echo awpcp_array_data('category', '', $form_errors) ?></span>
		</fieldset>

		<fieldset>
			<h3><?php _e('Please select a payment term for your Ad', 'AWPCP') ?></h3>
			<span class="error erroralert"><?php echo awpcp_array_data('payment-term', '', $form_errors) ?></span>

			<table>
				<thead>
					<tr>
						<th>Payment Term</th>
						<th>Ads Allowed</th>
						<th>Images Allowed</th>
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
						<th colspan="5" scope="row"><?php echo $term->type_name ?></td>
					</tr>
					<?php endif ?>

					<tr class="js-awpcp-payment-term" 
						data-price="<?php echo esc_attr($term->price) ?>" 
						data-categories="<?php echo esc_attr(json_encode($term->categories)) ?>">
						<td>
							<?php $id = "{$term->type}-{$term->id}" ?>
							<?php $element_id = "payment-term-$id" ?>
							<input id="<?php echo $element_id ?>" type="radio" name="payment-term" 
								   value="<?php echo esc_attr("$id") ?>" 
								   <?php echo $id == $selected ? 'checked="checked"' : '' ?> 
								   <?php echo $selected ?>/>
							<label for="<?php echo $id ?>"><strong><?php echo $term->name ?></strong></label><br/>
							<?php echo $term->description ?>
						</td>
						<td><?php echo $term->ads_allowed ?></td>
						<td><?php echo $term->images_allowed ?></td>
						<td><?php echo $term->duration ?></td>
						<td><?php echo $term->price ?></td>
					</tr>
					<?php $type = $term->type ?>

					<?php endforeach ?>
				</tbody>
			</table>
		</fieldset>

		<fieldset>
			<h3><?php _e('Please select a payment method', 'AWPCP') ?></h3>
			<span class="error erroralert"><?php echo awpcp_array_data('payment-method', '', $form_errors) ?></span>

			<table>
				<thead>
					<tr>
						<th>Payment Method</th>
					</tr>
				</thead>
				<tbody>
					<?php $selected = awpcp_array_data('payment-method', '', $form_values) ?>
					<?php $selected = empty($selected) ? array_shift(awpcp_get_properties($payment_methods, 'slug')) : $selected ?>
					<?php foreach ($payment_methods as $method): ?>
					<tr class="js-awpcp-payment-method">
						<td>
							<?php $id = "payment-method-{$method->slug}" ?>
							<input id="<?php echo $id ?>" type="radio" name="payment-method" 
								   value="<?php echo esc_attr($method->slug) ?>" 
								   <?php echo $method->slug == $selected ? 'checked="checked"' : '' ?> />
							<label for="<?php echo $id ?>"><strong><?php echo $method->name ?></strong></label><br/>
							<?php echo $method->description ?>
						</td>
					</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</fieldset>

		<p class="form-submit">
			<input class="button" type="submit" value="<?php _e('Continue', 'AWPCP') ?>" id="submit" name="submit">
			<input type="hidden" value="<?php echo esc_attr($transaction->id) ?>" name="awpcp-txn">
			<input type="hidden" value="checkout" name="a">
		</p>
	</form>
</div>