<?php

get_header();

$promotion_cards = array(
	array(
		'id'    => 32960,
		'title' => 'CANDY RETAIL & Wobble Tower เล่นสนุกทั้งครอบครัว✨',
	),
	array(
		'id'    => 32962,
		'title' => 'Feed the Woodpecker & Sensory Bottles✨',
	),
	array(
		'id'    => 32988,
		'title' => 'Numberblocks 1-10 & Playing Cards✨',
	),
	array(
		'id'    => 32978,
		'title' => 'Numberblocks Puzzle1 & Numberblocks Counters✨',
	),
	array(
		'id'    => 32995,
		'title' => 'Numberblocks® One to Five Sensory Bottles & SPIKE THE FINE MOTOR HEDGEHOG✨',
	),
	array(
		'id'    => 1333,
		'title' => '[อายุ 3+] ลูกโลกถอดประกอบสำหรับเด็ก (Puzzle Globe) [Learning Resources]',
	),
);
?>
<main id="primary" class="lfk-promotion-page">
	<section class="lfk-promotion-stage">
		<ul class="lfk-promotion-grid">
			<?php foreach ( $promotion_cards as $card ) : ?>
				<?php
				$product = wc_get_product( $card['id'] );
				if ( ! $product instanceof WC_Product ) {
					continue;
				}
				$product_id = $product->get_id();
				?>
				<li class="lfk-promotion-card product">
					<a class="lfk-promotion-image" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
						<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ); ?>
					</a>
					<h2 class="lfk-promotion-title"><a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"><?php echo esc_html( $card['title'] ); ?></a></h2>
					<div class="lfk-promotion-meta">
						<div class="lfk-promotion-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
						<a class="lfk-promotion-wishlist" href="<?php echo esc_url( home_url( '/wishlists/' ) ); ?>">Add to Wishlist</a>
					</div>
					<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
						<a
							href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
							data-quantity="1"
							data-product_id="<?php echo esc_attr( $product_id ); ?>"
							data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
							class="lfk-promotion-add-to-cart button <?php echo esc_attr( $product->is_type( 'simple' ) ? 'product_type_simple add_to_cart_button ajax_add_to_cart' : 'product_type_' . $product->get_type() ); ?>"
							aria-label="<?php echo esc_attr( $product->add_to_cart_description() ); ?>"
							rel="nofollow"
						><?php echo esc_html( $product->add_to_cart_text() ); ?></a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
</main>
<?php
get_footer();
