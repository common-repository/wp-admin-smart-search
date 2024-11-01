<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPAS_At_A_Glance extends WP_Widget {

	function __construct() {
		$widget_details = array(
			'classname'   => 'widget_wpas_at_a_glance',
			'description' => esc_html__( '"At A Glance" section for WPAS.', 'wpas' )
		);

		parent::__construct( 'wpas_at_a_glance', esc_html__( '[WPAS] At A Glance', 'wpas' ), $widget_details );
	}

	function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo wp_kses( $args['before_widget'], array(
			'div' => array( 'id' => array(), 'class' => array() )
		) );
		echo wp_kses( $args['before_title'] . $title . $args['after_title'], array(
			'div' => array( 'class' => array() )
		) );

		$count_posts    = wp_count_posts();
		$count_pages    = wp_count_posts( 'page' );
		$count_comments = wp_count_comments();
		$current_theme  = wp_get_theme();
		global $wp_version;
		?>
		<ul>
			<li><i class="icon-paper"></i> <a
					href="<?php echo admin_url( 'edit.php?post_type=post' ); ?>"><?php echo $count_posts->publish; ?>
					<?php esc_html_e( 'Posts', 'wpas' ); ?></a></li>
			<li><i class="icon-paper-stack"></i> <a
					href="<?php echo admin_url( 'edit.php?post_type=page' ); ?>"><?php echo $count_pages->publish; ?>
					<?php esc_html_e( 'Pages', 'wpas' ); ?></a></li>
			<li><i class="icon-speech-bubble"></i> <a
					href="<?php echo admin_url( 'edit-comments.php' ); ?>"><?php echo $count_comments->total_comments; ?>
					<?php esc_html_e( 'Comments', 'wpas' ); ?><?php echo ( $count_comments->spam > 0 ) ? '(' . $count_comments->spam . ' ' . esc_html__( 'spam', 'wpas' ) . ')' : ''; ?></a>
			</li>
			<li><i class="icon-flag"></i> <a
					href="<?php echo admin_url( 'themes.php' ); ?>">WordPress <?php echo $wp_version; ?>
					running <?php echo esc_html( $current_theme->get( 'Name' ) ); ?> theme</a>
			</li>
		</ul>
		<?php
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
			$title = esc_html__( 'At A Glance', 'wpas' );
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
