<?php
/**
 * This file is part of TheCartPress.
 * 
 * TheCartPress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TheCartPress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TheCartPress.  If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'NoCostPayment' ) ) :

class NoCostPayment extends TCP_Plugin {
	function getTitle() {
		return 'No Payment';
	}

	function getIcon() {
		return plugins_url( 'thecartpress/images/no-payment.png' );
	}

	function getDescription() {
		return 'No payment method.<br>Author: <a href="http://thecartpress.com" target="_blank">TheCartPress team</a>';
	}

	function getCheckoutMethodLabel( $instance, $shippingCountry = '', $shoppingCart = false ) {
		$data = tcp_get_payment_plugin_data( get_class( $this ), $instance );
		if ( isset( $data['title'] ) && function_exists( 'tcp_string' ) ) {
			$title = tcp_string( 'TheCartPress', apply_filters( 'tcp_plugin_data_get_option_translatable_key', 'pay_NoCostPayment-title-' . $instance ), $data['title'] );
		} else {
			$title = __( 'No payment.', 'tcp' );
		}
		return $title;
	}

	function showEditFields( $data, $instance = 0 ) {?>
		<tr valign="top">
			<th scope="row">
				<label for="notice"><?php _e( 'Notice', 'tcp' );?>:</label>
			</th><td>
				<textarea id="notice" name="notice" cols="40" rows="4" maxlength="500"><?php echo isset( $data['notice'] ) ? $data['notice'] : '';?></textarea>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="notice"><?php _e( 'Only visible to admin users', 'tcp' );?>:</label>
			</th><td>
				<input type="checkbox" id="only_admin_user" name="only_admin_user" value="Y" <?php checked( isset( $data['only_admin_user'] ) ? $data['only_admin_user'] : false );?> />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="redirect"><?php _e( 'Redirect automatically', 'tcp' );?>:</label>
			</th>
			<td>
				<input type="checkbox" id="redirect" name="redirect" value="yes" <?php checked( isset( $data['redirect'] ) ? $data['redirect'] : false ); ?> />
				<p class="description"><?php _e( 'If checked, Checkout page will completed the order. Customers will not need to click on "Finish" button.', 'tcp' ); ?></p>
			</td>
		</tr><?php
	}

	function saveEditFields( $data, $instance = 0 ) {
		$data['notice']			= isset( $_REQUEST['notice'] ) ? $_REQUEST['notice'] : '';
		$data['only_admin_user']	= isset( $_REQUEST['only_admin_user'] );
		$data['redirect']		= isset( $_REQUEST['redirect'] );
		return $data;
	}

	function isApplicable( $shippingCountry, $shoppingCart, $data ) {
		if ( isset( $data['only_admin_user'] ) ? $data['only_admin_user'] : false ) {
			return current_user_can( 'manage_options' );
		} else {
			return true;
		}
	}

	function sendPurchaseMail() {
		return true;
	}

	function showPayForm( $instance, $shippingCountry, $shoppingCart, $order_id = 0 ) {
		$buy_button_color	= tcp_get_buy_button_color();
		$data				= tcp_get_payment_plugin_data( get_class( $this ), $instance, $order_id );
		$url				= tcp_get_the_checkout_ok_url( $order_id );
		$title				= isset( $data['title'] ) ? $data['title'] : '';
		$redirect			= isset( $data['redirect'] ) ? $data['redirect'] : false; ?>
<p>
	<?php echo tcp_string( 'TheCartPress', 'pay_NoCostPayment-title', $title ); ?>
</p>

<p>
	<?php echo $data['notice'];?>
</p>

<p class="tcpf">
	<button type="submit" id="tcp_no_cost_payment_button" class="tcp_pay_button tcp-btn tcp-btn-lg <?php echo $buy_button_color; ?>" onclick="window.location.href = '<?php echo $url; ?>';"><?php _e( 'Finish', 'tcp' );?></button>
</p>

		<?php require_once( TCP_DAOS_FOLDER . '/Orders.class.php' );
		Orders::editStatus( $order_id, $data['new_status'] ); //Orders::$ORDER_PROCESSING );
		require_once( TCP_CHECKOUT_FOLDER . '/ActiveCheckout.class.php' );
		ActiveCheckout::sendMails( $order_id );
		if ( $redirect ) { ?>
<p class="tcp_redirect"><?php _e( 'Finishing automatically. Please, wait a moment', 'tcp' ); ?></p>
<script type="text/javascript">
	jQuery( '#tcp_no_cost_payment_button' ).click();
</script><?php }
	}
}
endif; // class_exists check