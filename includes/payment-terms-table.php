<?php

class AWPCP_PaymentTermsTable {

    protected $items = array();
    protected $selected = null;

    protected $term = null;

    public function __construct($items, $default=null) {
        foreach ($items as $type => $terms) {
            $this->items = array_merge($this->items, $terms);
        }

        $selected = awpcp_post_param('payment_term', $default);

        if ($selected) {
            $this->selected = $selected;
        } else if (count($this->items) > 0) {
            $item = reset($this->items);

            $columns = $this->get_columns();
            if (isset($columns['price'])) {
                $this->selected = $this->item_id( $item, AWPCP_Payment_Transaction::PAYMENT_TYPE_MONEY );
            } else if (isset($columns['credits'])) {
                $this->selected = $this->item_id( $item, AWPCP_Payment_Transaction::PAYMENT_TYPE_CREDITS );
            }
        }
    }

    public function get_columns() {
        $columns = array(
            'name' => _x('Payment Term', 'place ad payment terms column headers', 'AWPCP'),
            'ads' => _x('Ads Allowed', 'place ad payment terms column headers', 'AWPCP'),
            'images' => _x('Images Allowed', 'place ad payment terms column headers', 'AWPCP'),
            'title_characters' => _x( 'Characters in Title', 'place ad payment terms column headers', 'AWPCP'),
            'characters' => _x('Chars in Description', 'place ad payment terms column headers', 'AWPCP'),
            'duration' => _x('Duration', 'place ad payment terms column headers', 'AWPCP'),
        );

        $accepted_payment_types = awpcp_payments_api()->get_accepted_payment_types();

        if ( in_array( AWPCP_Payment_Transaction::PAYMENT_TYPE_MONEY, $accepted_payment_types ) ) {
            $columns['price'] = _x('Price (currency)', 'place ad payment terms column headers', 'AWPCP');
        }

        if ( in_array( AWPCP_Payment_Transaction::PAYMENT_TYPE_CREDITS, $accepted_payment_types ) ) {
            if ( awpcp_payments_api()->credit_system_enabled() ) {
                $columns['credits'] = _x('Price (credits)', 'place ad payment terms column headers', 'AWPCP');
            }
        }

        return $columns;
    }

    public function get_items() {
        return $this->items;
    }

    public function item_id($item, $suffix=false) {
        $parts = array_filter(array($item->type, $item->id, $suffix), 'strlen');
        return join('-', $parts);
    }

    public function item_group($item) {
        return $item->type;
    }

    public function item_group_name($item) {
        $payments = awpcp_payments_api();
        $type = $payments->get_payment_term_type($item->type);
        $name = is_null($type) ? '' : sprintf('%s - %s', $type->name, $type->description);
        return trim($name, '- ');
    }

    public function item_attributes($item) {
        $attrs = array(
            'class' => 'awpcp-payment-term',
            'id' => $this->item_id($item),
            'data-price' => $item->price,
            'data-credits' => $item->credits,
            'data-categories' => json_encode($item->categories)
        );

        return awpcp_render_attributes($attrs);
    }

    public function item_column($item, $column) {
        switch ($column) {
            case 'name':
                if ( $item->description ) {
                    $description = sprintf( '%s<p>%s</p>', esc_html( $item->get_name() ), esc_html( $item->description ) );
                } else {
                    $description = esc_html( $item->get_name() );
                }
                return $description;

            case 'ads':
                return esc_html( $item->get_allowed_ads_count() );

            case 'images':
                return esc_html( $item->images );

            case 'title_characters':
                $characters = $item->get_characters_allowed_in_title();
                $characters = empty( $characters ) ? _x( 'No Limit', 'payment term duration', 'AWPCP' ) : $characters;
                return esc_html( $characters );

            case 'characters':
                $characters = $item->get_characters_allowed();
                $characters = empty( $characters ) ? _x( 'No Limit', 'payment term duration', 'AWPCP' ) : $characters;
                return esc_html( $characters );

            case 'duration':
                return esc_html( $item->get_duration() );

            case 'price':
                return $this->render_payment_option(
                    $this->item_id($item, AWPCP_Payment_Transaction::PAYMENT_TYPE_MONEY),
                                          awpcp_format_money($item->price), $this->selected);

            case 'credits':
                return $this->render_payment_option(
                    $this->item_id($item, AWPCP_Payment_Transaction::PAYMENT_TYPE_CREDITS),
                                          number_format($item->credits, 0), $this->selected);
        }
    }

    public function get_payment_term(&$payment_type='', &$selected=null) {
        $selected = $this->selected;

        if (!preg_match('/(.+)-(.+)-(money|credits)/', $selected, $matches))
            return null;
        list($selected, $type, $id, $payment_type) = $matches;

        if (is_null($this->term))
            $this->term = awpcp_payments_api()->get_payment_term($id, $type);
        return $this->term;
    }

    public function set_transaction_item($transaction, $term=null, $payment_type=null) {
        if (is_null($term)) {
            $term = $this->get_payment_term($payment_type, $selected);
        } else {
            if ( is_null( $payment_type ) ) {
                $payment_type = AWPCP_Payment_Transaction::PAYMENT_TYPE_MONEY;
            }
            $selected = $this->item_id($term, $payment_type);
        }

        if ( !in_array( $payment_type, awpcp_payments_api()->get_accepted_payment_types() ) ) {
            awpcp_flash( __( "The selected payment type can't be used in this kind of transaction.", 'AWPCP' ), 'error' );
            return;
        }

        if (!$term->is_suitable_for_transaction($transaction)) {
            awpcp_flash( __( "The selected payment term can't be used in this kind of transaction.", 'AWPCP' ), 'error' );
            return;
        }

        $amount = $payment_type == 'credits' ? $term->credits : $term->price;
        $transaction->add_item($selected, $term->get_name(), $term->description, $payment_type, $amount);
    }

    public function render_payment_option($value, $amount, $selected) {
        $attrs = array(
            'class' => 'required',
            'id' => $value,
            'type' => 'radio',
            'name' => 'payment_term',
            'value' => $value
        );

        if ($value == $selected)
            $attrs['checked'] = 'checked';

        $html = sprintf('<input %s>', awpcp_render_attributes($attrs));
        $html.= sprintf( '&nbsp;<label for="%s">%s</label>', esc_attr( $value ), esc_html( $amount ) );

        return $html;
    }

    public function render() {
        ob_start();
            include(AWPCP_DIR . '/frontend/templates/payments-payment-terms-table.tpl.php');
            $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }
}
