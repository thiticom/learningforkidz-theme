<?php
/**
 * Custom checkout form for the Learning For Kidz pure theme.
 *
 * Based on WooCommerce checkout/form-checkout.php @version 9.4.0.
 *
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

$payment_was_hooked = has_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment' );
if ( false !== $payment_was_hooked ) {
	remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout lfk-custom-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="col2-set" id="customer_details">
			<div class="col-1">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="col-2">
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>

	<div class="lfk-checkout-sidebar">
		<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

		<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>

		<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

		<div id="order_review" class="woocommerce-checkout-review-order">
			<?php do_action( 'woocommerce_checkout_order_review' ); ?>
		</div>

		<?php if ( wc_coupons_enabled() ) : ?>
			<div class="e-coupon-box lfk-checkout-coupon-box">
				<?php esc_html_e( 'Have a coupon?', 'woocommerce' ); ?>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Click here to enter your code', 'woocommerce' ); ?></a>
			</div>
		<?php endif; ?>

		<?php woocommerce_checkout_payment(); ?>

		<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
	</div>

</form>

<?php
if ( false !== $payment_was_hooked ) {
	add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
}

do_action( 'woocommerce_after_checkout_form', $checkout );
