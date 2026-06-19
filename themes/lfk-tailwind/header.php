<?php
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="lfk-site-header">
	<div class="lfk-topbar">
		<div class="lfk-shell flex min-h-[38px] items-center justify-between gap-4">
			<div>จัดส่งฟรีทุกคำสั่งซื้อ</div>
			<div class="hidden items-center gap-6 md:flex">
				<a class="lfk-topbar-link" href="<?php echo esc_url( home_url( '/become-a-partner/' ) ); ?>"><?php echo lfk_svg_icon( 'globe' ); ?>ร่วมเป็นตัวแทน</a>
				<a class="lfk-topbar-link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php echo lfk_svg_icon( 'user' ); ?>ลงชื่อเข้าใช้ / บัญชี</a>
				<?php echo do_shortcode( '[gtranslate]' ); ?>
			</div>
		</div>
	</div>
	<div class="lfk-header">
		<div class="lfk-shell lfk-header-inner">
			<button class="lfk-mobile-menu-button" type="button" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'lfk-tailwind' ); ?>" data-lfk-menu-toggle>
				<?php echo lfk_svg_icon( 'menu' ); ?>
			</button>
			<a class="lfk-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Learning for Kidz home', 'lfk-tailwind' ); ?>">
				<img class="skip-lazy" src="<?php echo esc_url( lfk_logo_url() ); ?>" alt="<?php esc_attr_e( 'Learning for Kidz', 'lfk-tailwind' ); ?>" width="114" height="80" loading="eager" fetchpriority="high" data-no-lazy="1">
			</a>
			<nav class="lfk-primary-nav" aria-label="<?php esc_attr_e( 'Primary menu', 'lfk-tailwind' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'menu'           => 'top eng',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'fallback_cb'    => false,
					'depth'          => 3,
				) );
				?>
			</nav>
			<div class="lfk-icon-row">
				<a class="lfk-icon-link" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Search', 'lfk-tailwind' ); ?>"><?php echo lfk_svg_icon( 'search' ); ?></a>
				<a class="lfk-icon-link" href="<?php echo esc_url( home_url( '/wishlists/' ) ); ?>" aria-label="<?php esc_attr_e( 'Wishlist', 'lfk-tailwind' ); ?>"><?php echo lfk_svg_icon( 'heart' ); ?></a>
				<a class="lfk-icon-link hidden md:inline-flex" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'Account', 'lfk-tailwind' ); ?>"><?php echo lfk_svg_icon( 'user' ); ?></a>
				<a class="lfk-cart-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'lfk-tailwind' ); ?>">
					<span class="lfk-cart-total"><?php echo wp_kses_post( lfk_cart_total() ); ?></span>
					<?php echo lfk_svg_icon( 'cart' ); ?>
					<span class="lfk-cart-count"><?php echo esc_html( lfk_cart_count() ); ?></span>
				</a>
			</div>
		</div>
		<nav class="lfk-mobile-nav" aria-label="<?php esc_attr_e( 'Mobile menu', 'lfk-tailwind' ); ?>" data-lfk-mobile-nav>
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'menu'           => 'top eng',
				'container'      => false,
				'items_wrap'     => '%3$s',
				'fallback_cb'    => false,
				'depth'          => 3,
			) );
			?>
		</nav>
	</div>
</header>
