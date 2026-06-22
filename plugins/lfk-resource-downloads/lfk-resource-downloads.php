<?php
/**
 * Plugin Name: LFK Resource Downloads
 * Description: Gates product guides and lesson plans behind a name/email form and records leads for CRM export.
 * Version: 0.2.0
 * Author: Learning for Kidz
 * Text Domain: lfk-resource-downloads
 */

defined( 'ABSPATH' ) || exit;

final class LFK_Resource_Downloads {
	const VERSION           = '0.2.0';
	const POST_TYPE         = 'lfk_resource';
	const DB_VERSION        = '1';
	const DB_VERSION_OPTION = 'lfk_resource_downloads_db_version';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_create_table' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_resource_meta' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_leads_page' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_assets' ) );
		add_action( 'admin_post_lfk_resource_download', array( __CLASS__, 'handle_download' ) );
		add_action( 'admin_post_nopriv_lfk_resource_download', array( __CLASS__, 'handle_download' ) );
		add_action( 'admin_post_lfk_resource_leads_export', array( __CLASS__, 'handle_leads_export' ) );
		add_action( 'lfk_single_product_downloads', array( __CLASS__, 'render_single_product_downloads' ) );

		add_shortcode( 'lfk_resource_library', array( __CLASS__, 'render_library_shortcode' ) );
		add_shortcode( 'lfk_product_resources', array( __CLASS__, 'render_product_shortcode' ) );
		add_filter( 'the_content', array( __CLASS__, 'append_product_resources' ), 20 );
	}

	public static function activate() {
		self::register_post_type();
		self::create_table();
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	public static function maybe_create_table() {
		if ( self::DB_VERSION !== get_option( self::DB_VERSION_OPTION ) ) {
			self::create_table();
		}
	}

	public static function register_post_type() {
		$labels = array(
			'name'               => __( 'Download Resources', 'lfk-resource-downloads' ),
			'singular_name'      => __( 'Download Resource', 'lfk-resource-downloads' ),
			'add_new_item'       => __( 'Add Download Resource', 'lfk-resource-downloads' ),
			'edit_item'          => __( 'Edit Download Resource', 'lfk-resource-downloads' ),
			'new_item'           => __( 'New Download Resource', 'lfk-resource-downloads' ),
			'view_item'          => __( 'View Download Resource', 'lfk-resource-downloads' ),
			'search_items'       => __( 'Search Download Resources', 'lfk-resource-downloads' ),
			'not_found'          => __( 'No download resources found.', 'lfk-resource-downloads' ),
			'menu_name'          => __( 'Download Resources', 'lfk-resource-downloads' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => $labels,
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'menu_icon'    => 'dashicons-download',
				'supports'     => array( 'title', 'editor' ),
				'show_in_rest' => true,
			)
		);
	}

	public static function create_table() {
		global $wpdb;

		$table_name      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta(
			"CREATE TABLE {$table_name} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				resource_id bigint(20) unsigned NOT NULL,
				product_id bigint(20) unsigned NOT NULL DEFAULT 0,
				resource_type varchar(40) NOT NULL DEFAULT '',
				name varchar(190) NOT NULL DEFAULT '',
				email varchar(190) NOT NULL DEFAULT '',
				source_url varchar(255) NOT NULL DEFAULT '',
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY email (email),
				KEY resource_id (resource_id),
				KEY product_id (product_id),
				KEY created_at (created_at)
			) {$charset_collate};"
		);

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	private static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'lfk_resource_leads';
	}

	private static function resource_types() {
		return array(
			'product_guide' => __( 'Product guide', 'lfk-resource-downloads' ),
			'lesson_plan'   => __( 'Lesson plan', 'lfk-resource-downloads' ),
		);
	}

	public static function add_meta_boxes() {
		add_meta_box(
			'lfk-resource-details',
			__( 'Resource Details', 'lfk-resource-downloads' ),
			array( __CLASS__, 'render_resource_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	public static function render_resource_meta_box( $post ) {
		$type        = get_post_meta( $post->ID, '_lfk_resource_type', true ) ?: 'product_guide';
		$file_id     = (int) get_post_meta( $post->ID, '_lfk_resource_file_id', true );
		$file_url    = $file_id ? wp_get_attachment_url( $file_id ) : '';
		$product_ids = self::format_product_ids_for_input( get_post_meta( $post->ID, '_lfk_resource_product_ids', true ) );

		wp_nonce_field( 'lfk_resource_meta_' . $post->ID, 'lfk_resource_meta_nonce' );
		?>
		<p>
			<label for="lfk_resource_type"><strong><?php esc_html_e( 'Resource type', 'lfk-resource-downloads' ); ?></strong></label><br>
			<select id="lfk_resource_type" name="lfk_resource_type">
				<?php foreach ( self::resource_types() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $type, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="lfk_resource_file_url"><strong><?php esc_html_e( 'Download file', 'lfk-resource-downloads' ); ?></strong></label><br>
			<input type="hidden" id="lfk_resource_file_id" name="lfk_resource_file_id" value="<?php echo esc_attr( $file_id ); ?>">
			<input type="text" id="lfk_resource_file_url" class="regular-text" value="<?php echo esc_url( $file_url ); ?>" readonly>
			<button type="button" class="button" data-lfk-resource-file-picker><?php esc_html_e( 'Choose file', 'lfk-resource-downloads' ); ?></button>
			<button type="button" class="button" data-lfk-resource-file-clear><?php esc_html_e( 'Clear', 'lfk-resource-downloads' ); ?></button>
		</p>
		<p>
			<label for="lfk_resource_product_ids"><strong><?php esc_html_e( 'Related products', 'lfk-resource-downloads' ); ?></strong></label><br>
			<input type="text" id="lfk_resource_product_ids" name="lfk_resource_product_ids" class="regular-text" value="<?php echo esc_attr( $product_ids ); ?>" placeholder="AA227, 456">
			<span class="description"><?php esc_html_e( 'Comma-separated WooCommerce product SKUs or IDs. Leave blank to show only in the resource library.', 'lfk-resource-downloads' ); ?></span>
		</p>
		<?php
	}

	public static function save_resource_meta( $post_id ) {
		if ( ! isset( $_POST['lfk_resource_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfk_resource_meta_nonce'] ) ), 'lfk_resource_meta_' . $post_id ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$type = isset( $_POST['lfk_resource_type'] ) ? sanitize_key( wp_unslash( $_POST['lfk_resource_type'] ) ) : 'product_guide';
		if ( ! array_key_exists( $type, self::resource_types() ) ) {
			$type = 'product_guide';
		}

		$file_id         = isset( $_POST['lfk_resource_file_id'] ) ? absint( wp_unslash( $_POST['lfk_resource_file_id'] ) ) : 0;
		$product_ids_raw = isset( $_POST['lfk_resource_product_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['lfk_resource_product_ids'] ) ) : '';
		$product_ids     = self::parse_related_products( $product_ids_raw );

		update_post_meta( $post_id, '_lfk_resource_type', $type );
		update_post_meta( $post_id, '_lfk_resource_file_id', $file_id );

		if ( $product_ids ) {
			update_post_meta( $post_id, '_lfk_resource_product_ids', self::format_product_ids_for_storage( $product_ids ) );
		} else {
			delete_post_meta( $post_id, '_lfk_resource_product_ids' );
		}
	}

	public static function enqueue_admin_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || self::POST_TYPE !== $screen->post_type || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'lfk-resource-downloads-admin',
			plugins_url( 'assets/js/admin.js', __FILE__ ),
			array( 'jquery' ),
			self::VERSION,
			true
		);
	}

	public static function add_leads_page() {
		add_submenu_page(
			'edit.php?post_type=' . self::POST_TYPE,
			__( 'Resource Leads', 'lfk-resource-downloads' ),
			__( 'Leads', 'lfk-resource-downloads' ),
			'manage_options',
			'lfk-resource-leads',
			array( __CLASS__, 'render_leads_page' )
		);
	}

	public static function enqueue_frontend_assets() {
		self::register_frontend_style();

		$post = get_post();
		if (
			is_singular( 'product' ) ||
			( $post && ( has_shortcode( $post->post_content, 'lfk_resource_library' ) || has_shortcode( $post->post_content, 'lfk_product_resources' ) ) )
		) {
			self::enqueue_frontend_bundle();
		}
	}

	private static function register_frontend_style() {
		if ( wp_style_is( 'lfk-resource-downloads', 'registered' ) ) {
			return;
		}

		wp_register_style(
			'lfk-resource-downloads',
			plugins_url( 'assets/css/frontend.css', __FILE__ ),
			array(),
			self::VERSION
		);
	}

	private static function register_frontend_script() {
		if ( wp_script_is( 'lfk-resource-downloads', 'registered' ) ) {
			return;
		}

		wp_register_script(
			'lfk-resource-downloads',
			plugins_url( 'assets/js/frontend.js', __FILE__ ),
			array(),
			self::VERSION,
			true
		);
	}

	private static function enqueue_frontend_bundle() {
		self::register_frontend_style();
		self::register_frontend_script();

		wp_enqueue_style( 'lfk-resource-downloads' );
		wp_enqueue_script( 'lfk-resource-downloads' );
	}

	public static function render_leads_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view resource leads.', 'lfk-resource-downloads' ) );
		}

		global $wpdb;

		$table_name = self::table_name();
		$leads      = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 200" );
		$export_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=lfk_resource_leads_export' ),
			'lfk_resource_leads_export'
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Resource Leads', 'lfk-resource-downloads' ); ?></h1>
			<p><?php esc_html_e( 'Recent people who submitted the download form. Export CSV when you are ready to import into a CRM.', 'lfk-resource-downloads' ); ?></p>
			<p><a class="button button-primary" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Export CSV', 'lfk-resource-downloads' ); ?></a></p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'lfk-resource-downloads' ); ?></th>
						<th><?php esc_html_e( 'Name', 'lfk-resource-downloads' ); ?></th>
						<th><?php esc_html_e( 'Email', 'lfk-resource-downloads' ); ?></th>
						<th><?php esc_html_e( 'Resource', 'lfk-resource-downloads' ); ?></th>
						<th><?php esc_html_e( 'Product', 'lfk-resource-downloads' ); ?></th>
						<th><?php esc_html_e( 'Source URL', 'lfk-resource-downloads' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $leads ) : ?>
						<?php foreach ( $leads as $lead ) : ?>
							<tr>
								<td><?php echo esc_html( $lead->created_at ); ?></td>
								<td><?php echo esc_html( $lead->name ); ?></td>
								<td><a href="mailto:<?php echo esc_attr( $lead->email ); ?>"><?php echo esc_html( $lead->email ); ?></a></td>
								<td><?php echo esc_html( get_the_title( (int) $lead->resource_id ) ); ?></td>
								<td><?php echo $lead->product_id ? esc_html( get_the_title( (int) $lead->product_id ) ) : '&mdash;'; ?></td>
								<td><?php echo $lead->source_url ? '<a href="' . esc_url( $lead->source_url ) . '">' . esc_html( $lead->source_url ) . '</a>' : '&mdash;'; ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="6"><?php esc_html_e( 'No leads yet.', 'lfk-resource-downloads' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public static function render_library_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'product_id' => 0,
				'type'       => '',
				'heading'    => __( 'Product guides and lesson plans', 'lfk-resource-downloads' ),
			),
			$atts,
			'lfk_resource_library'
		);

		return self::render_resources(
			array(
				'product_id' => absint( $atts['product_id'] ),
				'type'       => sanitize_key( $atts['type'] ),
				'heading'    => sanitize_text_field( $atts['heading'] ),
				'empty_text' => __( 'No download resources are available yet.', 'lfk-resource-downloads' ),
			)
		);
	}

	public static function render_product_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'product_id' => 0,
				'heading'    => __( 'Downloads for this product', 'lfk-resource-downloads' ),
			),
			$atts,
			'lfk_product_resources'
		);

		$product_id = absint( $atts['product_id'] );
		if ( ! $product_id && function_exists( 'is_product' ) && is_product() ) {
			$product_id = get_the_ID();
		}

		return self::render_resources(
			array(
				'product_id' => $product_id,
				'heading'    => sanitize_text_field( $atts['heading'] ),
				'empty_text' => '',
			)
		);
	}

	public static function render_single_product_downloads( $product ) {
		$product_id = $product instanceof WC_Product ? $product->get_id() : absint( $product );
		if ( ! $product_id ) {
			return;
		}

		echo self::render_resources(
			array(
				'product_id' => $product_id,
				'heading'    => __( 'ไฟล์ดาวน์โหลด', 'lfk-resource-downloads' ),
				'empty_text' => '',
				'context'    => 'summary',
			)
		);
	}

	public static function append_product_resources( $content ) {
		if ( is_admin() || ! is_singular( 'product' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( did_action( 'lfk_single_product_downloads' ) ) {
			return $content;
		}

		if ( has_shortcode( $content, 'lfk_product_resources' ) || has_shortcode( $content, 'lfk_resource_library' ) ) {
			return $content;
		}

		$resources = self::render_resources(
			array(
				'product_id' => get_the_ID(),
				'heading'    => __( 'Downloads for this product', 'lfk-resource-downloads' ),
				'empty_text' => '',
			)
		);

		return $resources ? $content . $resources : $content;
	}

	private static function render_resources( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'product_id' => 0,
				'type'       => '',
				'heading'    => '',
				'empty_text' => '',
				'context'    => '',
			)
		);

		$resources = self::get_resources( (int) $args['product_id'], $args['type'] );

		if ( ! $resources && empty( $args['empty_text'] ) ) {
			return '';
		}

		self::enqueue_frontend_bundle();

		ob_start();
		?>
		<section class="lfk-resource-downloads<?php echo 'summary' === $args['context'] ? ' lfk-resource-downloads--summary' : ''; ?>">
			<?php if ( ! empty( $args['heading'] ) ) : ?>
				<h2><?php echo esc_html( $args['heading'] ); ?></h2>
			<?php endif; ?>

			<?php if ( $resources ) : ?>
				<div class="lfk-resource-downloads__grid">
					<?php foreach ( $resources as $resource ) : ?>
						<?php self::render_resource_card( $resource, (int) $args['product_id'] ); ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="lfk-resource-downloads__empty"><?php echo esc_html( $args['empty_text'] ); ?></p>
			<?php endif; ?>
		</section>
		<?php
		return ob_get_clean();
	}

	private static function render_resource_card( $resource, $product_id ) {
		$type       = get_post_meta( $resource->ID, '_lfk_resource_type', true );
		$type_label = self::resource_types()[ $type ] ?? __( 'Resource', 'lfk-resource-downloads' );
		$file_id    = (int) get_post_meta( $resource->ID, '_lfk_resource_file_id', true );
		$form_id    = 'lfk-resource-' . $resource->ID;
		$modal_id   = $form_id . '-modal';
		$file_ext   = self::get_file_extension( $file_id );

		if ( ! $file_id ) {
			return;
		}
		?>
		<article class="lfk-resource-downloads__card">
			<button
				type="button"
				class="lfk-resource-downloads__file"
				data-lfk-resource-modal-open
				aria-controls="<?php echo esc_attr( $modal_id ); ?>"
			>
				<span class="lfk-resource-downloads__file-icon" data-file-ext="<?php echo esc_attr( $file_ext ); ?>" aria-hidden="true"></span>
				<span class="lfk-resource-downloads__file-body">
					<span class="lfk-resource-downloads__type"><?php echo esc_html( $type_label ); ?></span>
					<span class="lfk-resource-downloads__title"><?php echo esc_html( get_the_title( $resource ) ); ?></span>
				</span>
			</button>
			<div class="lfk-resource-downloads__modal" id="<?php echo esc_attr( $modal_id ); ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $form_id ); ?>-title" hidden>
				<div class="lfk-resource-downloads__modal-backdrop" data-lfk-resource-modal-close></div>
				<div class="lfk-resource-downloads__dialog">
					<button type="button" class="lfk-resource-downloads__close" data-lfk-resource-modal-close aria-label="<?php esc_attr_e( 'Close', 'lfk-resource-downloads' ); ?>">×</button>
					<div class="lfk-resource-downloads__dialog-icon" aria-hidden="true"></div>
					<h3 id="<?php echo esc_attr( $form_id ); ?>-title"><?php echo esc_html( get_the_title( $resource ) ); ?></h3>
					<?php if ( trim( $resource->post_content ) ) : ?>
						<div class="lfk-resource-downloads__description">
							<?php echo wp_kses_post( wpautop( $resource->post_content ) ); ?>
						</div>
					<?php endif; ?>
					<form class="lfk-resource-downloads__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<input type="hidden" name="action" value="lfk_resource_download">
						<input type="hidden" name="lfk_resource_id" value="<?php echo esc_attr( $resource->ID ); ?>">
						<input type="hidden" name="lfk_product_id" value="<?php echo esc_attr( $product_id ); ?>">
						<?php wp_nonce_field( 'lfk_resource_download_' . $resource->ID, 'lfk_resource_nonce' ); ?>
						<label for="<?php echo esc_attr( $form_id ); ?>-name"><?php esc_html_e( 'Name', 'lfk-resource-downloads' ); ?></label>
						<input id="<?php echo esc_attr( $form_id ); ?>-name" type="text" name="lfk_name" autocomplete="name" required>
						<label for="<?php echo esc_attr( $form_id ); ?>-email"><?php esc_html_e( 'Email', 'lfk-resource-downloads' ); ?></label>
						<input id="<?php echo esc_attr( $form_id ); ?>-email" type="email" name="lfk_email" autocomplete="email" required>
						<button type="submit"><?php esc_html_e( 'Download file', 'lfk-resource-downloads' ); ?></button>
					</form>
				</div>
			</div>
		</article>
		<?php
	}

	private static function get_file_extension( $file_id ) {
		$file_path = get_attached_file( $file_id );
		$file_url  = wp_get_attachment_url( $file_id );
		$path      = $file_path ?: wp_parse_url( $file_url, PHP_URL_PATH );
		$extension = $path ? pathinfo( $path, PATHINFO_EXTENSION ) : '';

		return $extension ? strtoupper( substr( sanitize_key( $extension ), 0, 4 ) ) : 'FILE';
	}

	private static function get_resources( $product_id = 0, $type = '' ) {
		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => '_lfk_resource_file_id',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			),
		);

		if ( $product_id ) {
			$meta_query[] = array(
				'key'     => '_lfk_resource_product_ids',
				'value'   => ',' . absint( $product_id ) . ',',
				'compare' => 'LIKE',
			);
		}

		if ( $type && array_key_exists( $type, self::resource_types() ) ) {
			$meta_query[] = array(
				'key'   => '_lfk_resource_type',
				'value' => $type,
			);
		}

		$query = new WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'meta_query'     => $meta_query,
			)
		);

		return $query->posts;
	}

	public static function handle_download() {
		$resource_id = isset( $_POST['lfk_resource_id'] ) ? absint( wp_unslash( $_POST['lfk_resource_id'] ) ) : 0;
		$product_id  = isset( $_POST['lfk_product_id'] ) ? absint( wp_unslash( $_POST['lfk_product_id'] ) ) : 0;
		$name        = isset( $_POST['lfk_name'] ) ? sanitize_text_field( wp_unslash( $_POST['lfk_name'] ) ) : '';
		$email       = isset( $_POST['lfk_email'] ) ? sanitize_email( wp_unslash( $_POST['lfk_email'] ) ) : '';

		if ( ! $resource_id || ! isset( $_POST['lfk_resource_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lfk_resource_nonce'] ) ), 'lfk_resource_download_' . $resource_id ) ) {
			wp_die( esc_html__( 'The download request is invalid. Please go back and try again.', 'lfk-resource-downloads' ) );
		}

		if ( '' === $name || ! is_email( $email ) ) {
			wp_die( esc_html__( 'Please enter your name and a valid email address before downloading.', 'lfk-resource-downloads' ) );
		}

		$resource = get_post( $resource_id );
		if ( ! $resource || self::POST_TYPE !== $resource->post_type || 'publish' !== $resource->post_status ) {
			wp_die( esc_html__( 'This download is not available.', 'lfk-resource-downloads' ) );
		}

		$file_id = (int) get_post_meta( $resource_id, '_lfk_resource_file_id', true );
		if ( ! $file_id ) {
			wp_die( esc_html__( 'This download file is not configured yet.', 'lfk-resource-downloads' ) );
		}

		if ( $product_id && ! self::resource_matches_product( $resource_id, $product_id ) ) {
			$product_id = 0;
		}

		self::record_lead( $resource_id, $product_id, $name, $email );
		self::send_file( $file_id );
	}

	private static function resource_matches_product( $resource_id, $product_id ) {
		$stored_product_ids = get_post_meta( $resource_id, '_lfk_resource_product_ids', true );

		return false !== strpos( (string) $stored_product_ids, ',' . absint( $product_id ) . ',' );
	}

	private static function record_lead( $resource_id, $product_id, $name, $email ) {
		global $wpdb;

		$resource_type = get_post_meta( $resource_id, '_lfk_resource_type', true );
		$source_url    = wp_get_referer();
		$source_url    = $source_url ? substr( esc_url_raw( $source_url ), 0, 255 ) : '';

		$wpdb->insert(
			self::table_name(),
			array(
				'resource_id'   => $resource_id,
				'product_id'    => $product_id,
				'resource_type' => $resource_type,
				'name'          => $name,
				'email'         => $email,
				'source_url'    => $source_url,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	private static function send_file( $file_id ) {
		$file_path = get_attached_file( $file_id );
		if ( ! $file_path || ! is_readable( $file_path ) ) {
			wp_die( esc_html__( 'The download file could not be found.', 'lfk-resource-downloads' ) );
		}

		$file_name = sanitize_file_name( basename( $file_path ) );
		$mime_type = get_post_mime_type( $file_id ) ?: 'application/octet-stream';

		while ( ob_get_level() ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Type: ' . $mime_type );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		readfile( $file_path );
		exit;
	}

	public static function handle_leads_export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to export resource leads.', 'lfk-resource-downloads' ) );
		}

		check_admin_referer( 'lfk_resource_leads_export' );

		global $wpdb;

		$table_name = self::table_name();
		$leads      = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY created_at DESC" );

		while ( ob_get_level() ) {
			ob_end_clean();
		}

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=lfk-resource-leads.csv' );

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'created_at', 'name', 'email', 'resource_title', 'resource_type', 'product_id', 'product_title', 'source_url' ) );

		foreach ( $leads as $lead ) {
			fputcsv(
				$output,
				array(
					$lead->created_at,
					$lead->name,
					$lead->email,
					get_the_title( (int) $lead->resource_id ),
					$lead->resource_type,
					$lead->product_id,
					$lead->product_id ? get_the_title( (int) $lead->product_id ) : '',
					$lead->source_url,
				)
			);
		}

		fclose( $output );
		exit;
	}

	private static function parse_related_products( $raw ) {
		$ids    = array();
		$tokens = array_filter( preg_split( '/[\s,]+/', (string) $raw ) );

		foreach ( $tokens as $token ) {
			if ( is_numeric( $token ) ) {
				$ids[] = absint( $token );
				continue;
			}

			if ( function_exists( 'wc_get_product_id_by_sku' ) ) {
				$ids[] = (int) wc_get_product_id_by_sku( sanitize_text_field( $token ) );
			}
		}

		$ids = array_filter( $ids );
		$ids = array_values( array_unique( $ids ) );

		return $ids;
	}

	private static function format_product_ids_for_storage( $ids ) {
		return $ids ? ',' . implode( ',', array_map( 'absint', $ids ) ) . ',' : '';
	}

	private static function format_product_ids_for_input( $stored ) {
		return trim( (string) $stored, ',' );
	}
}

LFK_Resource_Downloads::init();

register_activation_hook( __FILE__, array( 'LFK_Resource_Downloads', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'LFK_Resource_Downloads', 'deactivate' ) );
