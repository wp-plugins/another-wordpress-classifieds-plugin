<?php

class AWPCP_Payment_Transaction {

	public static $PAYMENT_STATUS_UNKNOWN = 'Unknown';
	public static $PAYMENT_STATUS_INVALID = 'Invalid';
	public static $PAYMENT_STATUS_FAILED = 'Failed';
	public static $PAYMENT_STATUS_PENDING = 'Pending';
	public static $PAYMENT_STATUS_COMPLETED = 'Completed';
	public static $PAYMENT_STATUS_FREE = 'Free';

	private $attributes = array('__items__' => array());
	
	public $id;
	public $errors = array();

	public function AWPCP_Payment_Transaction($id, $attributes=array()) {
		$this->id = $id;
		$this->attributes = $attributes;
	}

	public static function find_by_id($id) {		
		$attributes = get_option("awpcp-payment-transaction-$id", null);
		if (is_null($attributes)) {
			return null;
		}
		return new AWPCP_Payment_Transaction($id, $attributes);
	}

	public static function find_or_create($id) {
		$transaction = AWPCP_Payment_Transaction::find_by_id($id);
		if (is_null($transaction)) {
			$parts = split(' ', microtime());
			$id = md5(($parts[1]+$parts[0]) . wp_salt());
			$transaction = new AWPCP_Payment_Transaction($id);
		}
		return $transaction;
	}

	public static function find() {

	}

	public function get($name, $default=null) {
		if (isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}
		return $default;
	}

	public function set($name, $value) {
		$this->attributes[$name] = $value;
	}

	public function add_item($id, $name) {
		$item = new stdClass();
		$item->id = $id;
		$item->name = $name;
		$this->attributes['__items__'][] = $item;
	}

	public function get_item($index) {
		if (isset($this->attributes['__items__'][$index])) {
			return $this->attributes['__items__'][$index];
		}
		return null;
	}

	public function save() {
		if (!isset($this->attributes['__created__'])) {
			$this->attributes['__created__'] = current_time('mysql');
			$this->attributes['__updated__'] = current_time('mysql');
			add_option("awpcp-payment-transaction-{$this->id}", $this->attributes, '', 'no');
		} else {
			$this->attributes['__updated__'] = current_time('mysql');
			update_option("awpcp-payment-transaction-{$this->id}", $this->attributes);
		}
	}
}