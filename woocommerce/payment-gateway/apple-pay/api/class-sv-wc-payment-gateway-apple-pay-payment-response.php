<?php
/**
 * WooCommerce Payment Gateway Framework
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to http://www.skyverge.com
 *
 * @package   SkyVerge/WooCommerce/Payment-Gateway/Apple-Pay
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * The Apple Pay payment response object.
 *
 * @since 4.6.0-dev
 */
class SV_WC_Payment_Gateway_Apple_Pay_Payment_Response extends SV_WC_API_JSON_Response {


	/**
	 * Gets the authorized payment data.
	 *
	 * @since 4.6.0-dev
	 * @return object
	 */
	public function get_payment_data() {

		return ! empty( $this->token->paymentData ) ? (array) $this->token->paymentData : array();
	}


	/**
	 * Gets the authorization transaction ID.
	 *
	 * @since 4.6.0-dev
	 * @return string
	 */
	public function get_transaction_id() {

		return ! empty( $this->token->transactionIdentifier ) ? $this->token->transactionIdentifier : '';
	}


	/**
	 * Gets the authorized card type.
	 *
	 * @since 4.6.0-dev
	 * @return string
	 */
	public function get_card_type() {

		$card_type = ! empty( $this->token->paymentMethod->network ) ? $this->token->paymentMethod->network : 'card';

		return SV_WC_Payment_Gateway_Helper::normalize_card_type( $card_type );
	}


	/**
	 * Gets the last four digits of the authorized card.
	 *
	 * @since 4.6.0-dev
	 * @return string
	 */
	public function get_last_four() {

		$last_four = '';

		if ( ! empty( $this->token->paymentMethod->displayName ) ) {
			$last_four = substr( $this->token->paymentMethod->displayName, -4 );
		}

		return $last_four;
	}


	/**
	 * Gets the billing address.
	 *
	 * @since 4.6.0-dev
	 * @return array
	 */
	public function get_billing_address() {

		$address = ! empty( $this->response_data->billingContact ) ? $this->response_data->billingContact : null;

		$billing_address = $this->prepare_address( $address );

		// set the billing email
		if ( ! empty( $this->response_data->shippingContact->emailAddress ) ) {
			$billing_address['email'] = $this->shippingContact->emailAddress;
		}

		// set the billing phone number
		if ( ! empty( $this->response_data->shippingContact->phoneNumber ) ) {
			$billing_address['phone'] = $this->shippingContact->phoneNumber;
		}

		return $billing_address;
	}


	/**
	 * Gets the shipping address.
	 *
	 * @since 4.6.0-dev
	 * @return array
	 */
	public function get_shipping_address() {

		$address = ! empty( $this->response_data->shippingContact ) ? $this->response_data->shippingContact : null;

		$shipping_address = $this->prepare_address( $address );

		return $shipping_address;
	}


	/**
	 * Prepare an address to WC formatting.
	 *
	 * @since 4.6.0-dev
	 * @param object $contact the address to prepare
	 * @return array
	 */
	protected function prepare_address( $contact ) {

		$address = array(
			'first_name' => isset( $contact->givenName )       ? $contact->givenName :       '',
			'last_name'  => isset( $contact->familyName )      ? $contact->familyName :      '',
			'address_1'  => isset( $contact->addressLines[0] ) ? $contact->addressLines[0] : '',
			'address_2'  => '',
			'city'       => isset( $contact->locality )           ? $contact->locality :           '',
			'state'      => isset( $contact->administrativeArea ) ? $contact->administrativeArea : '',
			'postcode'   => isset( $contact->postalCode )         ? $contact->postalCode :         '',
			'country'    => isset( $contact->countryCode )        ? $contact->countryCode :        '',
		);

		if ( ! empty( $contact->addressLines[1] ) ) {
			$address['address_2'] = $contact->addressLines[1];
		}

		$address['country'] = strtoupper( $address['country'] );

		return $address;
	}


}