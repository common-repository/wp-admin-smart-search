<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPAS_Recent_Access extends WP_Widget {

	function __construct() {
		$widget_details = array(
			'classname'   => 'widget_wpas_recent_access',
			'description' => esc_html__( 'Recent access pages for WPAS.', 'wpas' )
		);

		parent::__construct( 'wpas_recent_access', esc_html__( '[WPAS] Recent Access', 'wpas' ), $widget_details );
	}

	function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$num   = intval( $instance['num'] );
		echo wp_kses( $args['before_widget'], array(
			'div' => array( 'id' => array(), 'class' => array() )
		) );
		echo wp_kses( $args['before_title'] . $title . $args['after_title'], array(
			'div' => array( 'class' => array() )
		) );
		?>
		<?php
		$wpas_recent_access = get_option( 'wpas_recent_access' );
		if ( is_array( $wpas_recent_access ) && ( count( $wpas_recent_access ) > 0 ) ) {
			echo '<ul>';
			$i = 1;
			foreach ( $wpas_recent_access as $item ) {
				if ( $i <= $num ) {
					echo '<li><a href="' . get_bloginfo( 'wpurl' ) . esc_url( trim( $item['link'] ) ) . '"><span class="title">' . esc_html( trim( $item['title'] ) ) . '</span><span class="link">' . date( 'M d, Y H:i:s', $item['time'] ) . ' - ' . esc_url( trim( $item['link'] ) ) . '</span></a></li>';
				}
				$i ++;
			}
			echo '</ul>';
		}
		?>
		<?php
		echo wp_kses( $args['after_widget'], array(
			'div' => array()
		) );
	}

	function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['num']   = ( ! empty( $new_instance['num'] ) ) ? strip_tags( $new_instance['num'] ) : 10;

		return $instance;
	}

	function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = esc_html__( 'Recent Access', 'wpas' );
		}
		if ( isset( $instance['num'] ) ) {
			$num = intval( $instance['num'] );
		} else {
			$num = 10;
		}
		?>
		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Widget title:', 'wpas' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			       name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
			       value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p>
			<label
				for="<?php echo esc_attr( $this->get_field_id( 'num' ) ); ?>"><?php esc_html_e( 'Number items:', 'wpas' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'num' ) ); ?>"
			        name="<?php echo esc_attr( $this->get_field_name( 'num' ) ); ?>">
				<?php
				for ( $i = 1; $i < 21; $i ++ ) {
					echo '<option value="' . $i . '" ' . ( $i == $num ? 'selected' : '' ) . '>' . $i . '</option>';
				}
				?>
			</select>
		</p>
		<?php
	}
}
