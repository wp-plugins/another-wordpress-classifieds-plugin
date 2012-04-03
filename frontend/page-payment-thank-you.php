<?php

class AWPCP_Payment_ThankYou_Page {

	public function AWPCP_Payment_ThankYou_Page() {
		add_filter('query_vars', array($this, 'add_query_vars'));

		add_filter('awpcp-checkout-form', 'awpcp_paypal_checkout_form', 10, 2);
		add_filter('awpcp-checkout-form', 'awpcp_2checkout_checkout_form', 10, 2);

		add_filter('awpcp-payments-verify-transaction', 'awpcp_paypal_verify_transaction', 10, 2);
		add_filter('awpcp-payments-verify-transaction', 'awpcp_2checkout_verify_transaction', 10, 2);

		add_filter('awpcp-payments-validate-transaction', 'awpcp_paypal_validate_transaction', 10, 2);
		add_filter('awpcp-payments-validate-transaction', 'awpcp_2checkout_validate_transaction', 10, 2);

		// XXX: move to Place Ad page?
		add_filter('awpcp-payments-transaction-processed', 'awpcp_ad_term_fee_transaction_processed', 10, 2);
	}

	public function add_query_vars($vars) {
    	array_push($vars, 'awpcp-txn');
    	return $vars;
	}

	public function dispatch() {
		global $wp_query;

		$transaction_id = awpcp_array_data('awpcp-txn', '', $wp_query->query_vars);

		$transaction_id_msg = '<br/><br/>';
		$transaction_id_msg.= sprintf(__('Your Transaction ID is %s.'), "<strong>$transaction_id</strong>");

		$transaction = AWPCP_Payment_Transaction::find_by_id($transaction_id);
		if (is_null($transaction)) {
			$msg = __('An error ocurred while processing your Payment Transaction. Please contact the administrator about this error.', 'AWPCP');
			$msg.= $transaction_id_msg;
			// TODO: send email?
			return $msg;
		}

		$verified = apply_filters('awpcp-payments-verify-transaction', false, $transaction);
		if (!$verified) {
			if (empty($transaction->errors)) {
				$msg = __("There appears to be a problem. Please contact customer service if you are viewing this message after having made a payment. If you have not tried to make a payment and you are viewing this message, it means this message is being shown in error and can be disregarded.","AWPCP");
				$msg.= $transaction_id_msg;
			} else {
				$msg = join('<br/><br/>', $transaction->errors);
			}
			// TODO: send email
			// $output .= abort_payment_no_email($message,$ad_id,$txn_id,$gateway);
			return $msg;
		}

		$valid = apply_filters('awpcp-payments-validate-transaction', false, $transaction);
		if (!$valid) {
			return join('<br/><br/>', $transaction->errors);
		}

		$texts = array(
			'title' => __('Step 2 of 4 - Checkout'),
			'subtitle' => __('Congratulations', 'AWPCP'),
			'text' => __('Your Payment has been processed succesfully. Please press the button below to continue with the process.', 'AWPCP')
		);

		// TODO: update Ads related stuff? disabled Ads?
		// TODO: update Subscriptions related stuff? disable Subscriptions?
		// If you want to change the message shown in this page change this action to become a filter
		$texts = apply_filters('awpcp-payments-transaction-processed', $texts, $transaction);

		$status = $transaction->get('status');
		$transaction->save();

		ob_start();
			include(AWPCP_DIR . 'frontend/templates/page-payment-thank-you.tpl.php');
			$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}