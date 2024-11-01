<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPAS_WooCommerce_Status extends WP_Widget {

	function __construct() {
		$widget_details = array(
			'classname'   => 'widget_wpas_woocommerce_status',
			'description' => esc_html__( '"WooCommerce Status" section for WPAS.', 'wpas' )
		);

		parent::__construct( 'wpas_woocommerce_status', esc_html__( '[WPAS] WooCommerce Status', 'wpas' ), $widget_details );
	}

	function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo wp_kses( $args['before_widget'], array(
			'div' => array( 'id' => array(), 'class' => array() )
		) );
		echo wp_kses( $args['before_title'] . $title . $args['after_title'], array(
			'div' => array( 'class' => array() )
		) );
		if ( class_exists( 'WooCommerce' ) ) {
			$count_orders = wp_count_posts( 'shop_order' );
			?>
			<ul>
				<li><i class="icon-ribbon"></i> <a
						href="<?php echo admin_url( 'edit.php?post_type=shop_order' ); ?>">Orders: <?php echo $count_orders->{'wc-pending'}; ?>
						pending, <?php echo $count_orders->{'wc-processing'}; ?>
						processing, <?php echo $count_orders->{'wc-on-hold'}; ?> on-hold</a>
				</li>
				<li><i class="icon-heart"></i> <a
						href="<?php echo admin_url( 'admin.php?page=wc-reports' ); ?>"><?php echo get_woocommerce_currency_symbol() . wpas_woo_get_total_sales(), ' gross sales'; ?></a>
				</li>
				<?php wpas_woo_stock_rows(); ?>
			</ul>
			<?php
		} else {
			echo esc_html__( 'Your website do not support WooCommerce, please remove this widget.', 'wpas' );
		}
		echo wp_kses( $args['after_widget'], array(
			'div' => array()
		) );
	}

	function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

	function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = esc_html__( 'WooCommerce Status', 'wpas' );
		}
		?>
		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Widget title:', 'wpas' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
			       value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<?php
	}
}

function wpas_woo_get_total_sales() {
	global $wpdb;
	$order_totals = apply_filters( 'woocommerce_reports_sales_overview_order_totals', $wpdb->get_row( "
	 SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts
	 LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
	 WHERE meta.meta_key = '_order_total'
	 AND posts.post_type = 'shop_order'
	 AND posts.post_status IN ( '" . implode( "','", array( 'wc-completed', 'wc-processing', 'wc-on-hold' ) ) . "' )
	" ) );

	return round( $order_totals->total_sales, 2 );
}

function wpas_woo_stock_rows() {
	global $wpdb;

	// Get products using a query - this is too advanced for get_posts :(
	$stock          = absint( max( get_option( 'woocommerce_notify_low_stock_amount' ), 1 ) );
	$nostock        = absint( max( get_option( 'woocommerce_notify_no_stock_amount' ), 0 ) );
	$transient_name = 'wc_low_stock_count';

	if ( false === ( $lowinstock_count = get_transient( $transient_name ) ) ) {
		$query_from       = apply_filters( 'woocommerce_report_low_in_stock_query_from', "FROM {$wpdb->posts} as posts
				INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
				INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
				WHERE 1=1
				AND posts.post_type IN ( 'product', 'product_variation' )
				AND posts.post_status = 'publish'
				AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
				AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$stock}'
				AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) > '{$nostock}'
			" );
		$lowinstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );
		set_transient( $transient_name, $lowinstock_count, DAY_IN_SECONDS * 30 );
	}

	$transient_name = 'wc_outofstock_count';

	if ( false === ( $outofstock_count = get_transient( $transient_name ) ) ) {
		$query_from       = apply_filters( 'woocommerce_report_out_of_stock_query_from', "FROM {$wpdb->posts} as posts
				INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
				INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
				WHERE 1=1
				AND posts.post_type IN ( 'product', 'product_variation' )
				AND posts.post_status = 'publish'
				AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
				AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$nostock}'
			" );
		$outofstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );
		set_transient( $transient_name, $outofstock_count, DAY_IN_SECONDS * 30 );
	}
	?>
	<li><i class="icon-stack-2"></i> <a
			href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=stock&report=low_in_stock' ); ?>"><?php printf( _n( '%s product low in stock', '%s products low in stock', $lowinstock_count, 'wpas' ), $lowinstock_count ); ?>
		</a>
	</li>
	<li><i class="icon-stack-2"></i> <a
			href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=stock&report=out_of_stock' ); ?>"><?php printf( _n( '%s product out of stock', '%s products out of stock', $outofstock_count, 'wpas' ), $outofstock_count ); ?>
		</a>
	</li>
	<?php
}