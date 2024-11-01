<?php
/*
Plugin Name: WPC Smart Search
Plugin URI: https://wpclever.net/
Description: Powerful AJAX search and useful tools for WordPress administrators.
Version: 1.4.5
Author: WPClever.net
Author URI: https://wpclever.net
Text Domain: wpas
Domain Path: /languages/
Requires at least: 4.0
Tested up to: 5.4
*/

defined( 'ABSPATH' ) || exit;

! defined( 'WPAS_VERSION' ) && define( 'WPAS_VERSION', '1.4.5' );
! defined( 'WPAS_REVIEWS' ) && define( 'WPAS_REVIEWS', 'https://wordpress.org/support/plugin/wp-admin-smart-search/reviews/?filter=5' );
! defined( 'WPAS_CHANGELOG' ) && define( 'WPAS_CHANGELOG', 'https://wordpress.org/plugins/wp-admin-smart-search/#developers' );
! defined( 'WPAS_DISCUSSION' ) && define( 'WPAS_DISCUSSION', 'https://wordpress.org/support/plugin/wp-admin-smart-search' );
! defined( 'WPAS_URI' ) && define( 'WPAS_URI', plugin_dir_url( __FILE__ ) );
! defined( 'WPC_URI' ) && define( 'WPC_URI', WPAS_URI );

include( 'includes/wpc-menu.php' );
include( 'includes/wpc-dashboard.php' );

if ( ! class_exists( 'WPCleverWpas' ) ) {
	include( 'includes/functions.php' );
	include( 'includes/widgets/at-a-glance.php' );
	include( 'includes/widgets/recent-access.php' );
	include( 'includes/widgets/woocommerce-status.php' );

	class WPCleverWpas {
		function __construct() {
			add_action( 'plugins_loaded', array( $this, 'wpas_load_textdomain' ) );
			add_action( 'admin_init', array( $this, 'wpas_admin_init' ) );
			add_action( 'admin_menu', array( $this, 'wpas_admin_menu' ) );
			add_action( 'widgets_init', array( $this, 'wpas_widgets_init' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'wpas_enqueue_scripts' ) );
			add_action( 'wp_ajax_wpas_ajax', array( $this, 'wpas_ajax' ) );
			add_action( 'wp_ajax_nopriv_wpas_ajax', array( $this, 'wpas_ajax' ) );
			add_action( 'wp_ajax_wpas_quick_setting', array( $this, 'wpas_quick_setting' ) );
			add_action( 'wp_ajax_nopriv_wpas_quick_setting', array( $this, 'wpas_quick_setting' ) );
			add_filter( 'plugin_action_links', array( $this, 'wpas_action_links' ), 99, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'wpas_row_meta' ), 99, 2 );
			add_action( 'admin_footer', array( $this, 'wpas_footer' ) );

			if ( get_option( 'wpas_frontend', 'no' ) == 'yes' ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'wpas_enqueue_scripts' ) );
				add_action( 'wp_footer', array( $this, 'wpas_footer' ) );
			}

			if ( ( get_option( 'wpas_frontend', 'no' ) == 'yes' ) || is_admin() ) {
				add_action( 'admin_bar_menu', array( $this, 'wpas_admin_bar_menu' ), 99 );
			}

			if ( get_option( 'wpas_search_exact', 'no' ) == 'yes' ) {
				add_action( 'pre_get_posts', array( $this, 'wpas_search_exact' ), 99 );
			}

			if ( get_option( 'wpas_search_sentence', 'no' ) == 'yes' ) {
				add_action( 'pre_get_posts', array( $this, 'wpas_search_sentence' ), 99 );
			}
		}

		function wpas_load_textdomain() {
			load_plugin_textdomain( 'wpas', false, basename( dirname( __FILE__ ) ) . '/languages/' );
		}

		function wpas_admin_init() {
			if ( ( get_option( '_wpas_menus_update', 0 ) == 0 ) || ( ( time() - get_option( '_wpas_menus_update', 0 ) ) > 600 ) ) {
				global $menu, $submenu;
				$wpas_menus = array();

				if ( is_array( $menu ) && ( count( $menu ) > 0 ) ) {
					foreach ( $menu as $mn ) {
						if ( isset( $mn[0] ) && ( $mn[0] != '' ) ) {
							$wpas_menus[] = array(
								'pre'  => '',
								'name' => strip_tags( $mn[0] ),
								'url'  => esc_url( $mn[2] )
							);

							if ( isset( $submenu[ $mn[2] ] ) && ( count( $submenu[ $mn[2] ] ) > 0 ) ) {
								foreach ( $submenu[ $mn[2] ] as $sm ) {
									$wpas_menus[] = array(
										'pre'  => strip_tags( $mn[0] ),
										'name' => strip_tags( $sm[0] ),
										'url'  => esc_url( $sm[2] )
									);
								}
							}
						}
					}
				}

				if ( is_array( $wpas_menus ) && ( count( $wpas_menus ) > 0 ) ) {
					update_option( '_wpas_menus_update', time() );
					update_option( '_wpas_menus', $wpas_menus );
				}
			}
		}

		function wpas_admin_menu() {
			add_submenu_page( 'wpclever', esc_html__( 'WPC Smart Search', 'wpas' ), esc_html__( 'Smart Search', 'wpas' ), 'manage_options', 'wpclever-wpas', array(
				&$this,
				'wpas_admin_menu_pages'
			) );
		}

		function wpas_admin_menu_pages() {
			$page_slug  = 'wpclever-wpas';
			$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'how';
			?>
            <div class="wpclever_settings_page wrap">
                <h1 class="wpclever_settings_page_title">
					<?php echo esc_html__( 'WPC Smart Search', 'wpas' ) . ' ' . WPAS_VERSION; ?>
                </h1>
                <div class="wpclever_settings_page_desc about-text">
                    <p>
						<?php printf( esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'wpas' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
                        <br/>
                        <a href="<?php echo esc_url( WPAS_REVIEWS ); ?>"
                           target="_blank"><?php esc_html_e( 'Reviews', 'wpas' ); ?></a> | <a
                                href="<?php echo esc_url( WPAS_CHANGELOG ); ?>"
                                target="_blank"><?php esc_html_e( 'Changelog', 'wpas' ); ?></a>
                        | <a href="<?php echo esc_url( WPAS_DISCUSSION ); ?>"
                             target="_blank"><?php esc_html_e( 'Discussion', 'wpas' ); ?></a>
                    </p>
                </div>
                <div class="wpclever_settings_page_nav">
                    <h2 class="nav-tab-wrapper">
                        <a href="<?php echo admin_url( 'admin.php?page=' . $page_slug . '&tab=how' ); ?>"
                           class="nav-tab <?php echo $active_tab == 'how' ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e( 'How to use?', 'wpas' ); ?>
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=' . $page_slug . '&tab=settings' ); ?>"
                           class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e( 'Settings', 'wpas' ); ?>
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=' . $page_slug . '&tab=premium' ); ?>"
                           class="nav-tab <?php echo $active_tab == 'premium' ? 'nav-tab-active' : ''; ?>">
							<?php esc_html_e( 'Premium Version', 'wpas' ); ?>
                        </a>
                    </h2>
                </div>
                <div class="wpclever_settings_page_content">
					<?php if ( $active_tab == 'how' ) { ?>
                        <div class="wpclever_settings_page_content_text">
                            <p><?php esc_html_e( '1. Click to the menu at top-right screen or press Ctrl+M to open the Smart Search', 'wpas' ); ?></p>
                            <p><?php esc_html_e( '2. You can use bellow syntax', 'wpas' ); ?></p>
                            <ul style="list-style-type: circle; list-style-position: outside; margin-left: 17px; margin-bottom: 0">
                                <li>
									<?php printf( esc_html__( '%s - search the post, page, product and other post types', 'wpas' ), '<code>{keyword}</code>' ); ?>
                                </li>
                                <li>
									<?php printf( esc_html__( '%s - search the category, tag and other terms', 'wpas' ), '<code>/t {keyword}</code>' ); ?>
                                </li>
                                <li>
									<?php printf( esc_html__( '%s - search the user', 'wpas' ), '<code>/u {keyword}</code>' ); ?>
                                </li>
                                <li>
									<?php printf( esc_html__( '%s - search the admin menu', 'wpas' ), '<code>/m {keyword}</code>' ); ?>
                                </li>
                                <li>
									<?php printf( esc_html__( '%s - search the product', 'wpas' ), '<code>/p {keyword or sku}</code>' ); ?>
                                    (<a href="<?php echo admin_url( 'admin.php?page=' . $page_slug . '&tab=premium' ); ?>">Premium
                                        Version</a>)
                                </li>
                                <li>
									<?php printf( esc_html__( '%s - search the order', 'wpas' ), '<code>/o {order number}</code>' ); ?>
                                    (<a href="<?php echo admin_url( 'admin.php?page=' . $page_slug . '&tab=premium' ); ?>">Premium
                                        Version</a>)
                                </li>
                            </ul>
                        </div>
					<?php } elseif ( $active_tab == 'settings' ) { ?>
                        <form method="post" action="options.php">
							<?php wp_nonce_field( 'update-options' ) ?>
                            <table class="form-table">
                                <tr class="heading">
                                    <th>
										<?php esc_html_e( 'General', 'wpas' ); ?>
                                    </th>
                                    <td>
										<?php esc_html_e( 'General settings.', 'wpas' ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Front-end', 'wpas' ); ?></th>
                                    <td>
                                        <select name="wpas_frontend">
                                            <option
                                                    value="yes" <?php echo( get_option( 'wpas_frontend', 'no' ) == 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'wpas' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( 'wpas_frontend', 'no' ) == 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'wpas' ); ?>
                                            </option>
                                        </select> <span
                                                class="description"><?php esc_html_e( 'Enable for the front-end?', 'wpas' ); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Search limit', 'wpas' ); ?></th>
                                    <td>
                                        <input id="wpas_search_limit" name="wpas_search_limit" type="number" min="1"
                                               max="500"
                                               value="<?php echo get_option( 'wpas_search_limit', '30' ); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Search exact', 'wpas' ); ?></th>
                                    <td>
                                        <select name="wpas_search_exact">
                                            <option
                                                    value="yes" <?php echo( get_option( 'wpas_search_exact', 'no' ) == 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'wpas' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( 'wpas_search_exact', 'no' ) == 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'wpas' ); ?>
                                            </option>
                                        </select> <span
                                                class="description"><?php esc_html_e( 'Match whole post title or post content?', 'wpas' ); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Search sentence', 'wpas' ); ?></th>
                                    <td>
                                        <select name="wpas_search_sentence">
                                            <option
                                                    value="yes" <?php echo( get_option( 'wpas_search_sentence', 'no' ) == 'yes' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'Yes', 'wpas' ); ?>
                                            </option>
                                            <option
                                                    value="no" <?php echo( get_option( 'wpas_search_sentence', 'no' ) == 'no' ? 'selected' : '' ); ?>>
												<?php esc_html_e( 'No', 'wpas' ); ?>
                                            </option>
                                        </select> <span
                                                class="description"><?php esc_html_e( 'Do a phrase search?', 'wpas' ); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Post types', 'wpas' ); ?></th>
                                    <td>
                                        <ul>
											<?php
											$wpas_post_types = get_post_types( '', 'objects' );

											if ( is_array( get_option( 'wpas_post_types' ) ) ) {
												$wpas_saved_post_types = get_option( 'wpas_post_types' );
											} else {
												$wpas_saved_post_types = array( '__all' );
											}

											echo '<li><input type="checkbox" name="wpas_post_types[]" value="__all" ' . ( in_array( '__all', $wpas_saved_post_types ) ? 'checked' : '' ) . '/><label>All <i style="opacity: .5">search all post types</i></label></li>';

											foreach ( $wpas_post_types as $key => $wpas_post_type ) {
												echo '<li><input type="checkbox" name="wpas_post_types[]" value="' . $key . '" ' . ( in_array( $key, $wpas_saved_post_types ) ? 'checked' : '' ) . '/><label>' . $wpas_post_type->label . ' (' . $key . ') ' . ( $wpas_post_type->public ? '<i style="opacity: .5">public</i>' : '' ) . '</label></li>';
											}
											?>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Post statuses', 'wpas' ); ?></th>
                                    <td>
                                        <ul>
											<?php
											$wpas_post_statuses = get_post_stati( '', 'objects' );

											if ( is_array( get_option( 'wpas_post_statuses' ) ) ) {
												$wpas_saved_post_statuses = get_option( 'wpas_post_statuses' );
											} else {
												$wpas_saved_post_statuses = array( '__all' );
											}

											echo '<li><input type="checkbox" name="wpas_post_statuses[]" value="__all" ' . ( in_array( '__all', $wpas_saved_post_statuses ) ? 'checked' : '' ) . '/><label>All <i style="opacity: .5">search all post statuses</i></label></li>';

											foreach ( $wpas_post_statuses as $key => $wpas_post_status ) {
												echo '<li><input type="checkbox" name="wpas_post_statuses[]" value="' . $key . '" ' . ( in_array( $key, $wpas_saved_post_statuses ) ? 'checked' : '' ) . '/><label>' . $wpas_post_status->label . ' (' . $key . ')</label></li>';
											}
											?>
                                        </ul>
                                    </td>
                                </tr>
                                <tr class="submit">
                                    <th colspan="2">
                                        <input type="submit" name="submit" class="button button-primary"
                                               value="<?php esc_html_e( 'Update Options', 'wpas' ); ?>"/>
                                        <input type="hidden" name="action" value="update"/>
                                        <input type="hidden" name="page_options"
                                               value="wpas_frontend,wpas_search_limit,wpas_search_exact,wpas_search_sentence,wpas_post_types,wpas_post_statuses"/>
                                    </th>
                                </tr>
                            </table>
                        </form>
					<?php } elseif ( $active_tab == 'premium' ) { ?>
                        <div class="wpclever_settings_page_content_text">
                            <p>
                                Get the Premium Version just $19! <a
                                        href="https://wpclever.net?utm_source=pro&utm_medium=wpas&utm_campaign=wporg"
                                        target="_blank">https://wpclever.net</a>
                            </p>
                            <p><strong>Extra features for Premium Version:</strong></p>
                            <ul style="margin-bottom: 0">
                                <li>- Add more search commands (search product & order).</li>
                                <li>- Get the lifetime update & premium support.</li>
                            </ul>
                        </div>
					<?php } ?>
                </div>
            </div>
			<?php
		}

		function wpas_enqueue_scripts() {
			wp_enqueue_style( 'feather', WPAS_URI . 'assets/feather/feather.css' );
			wp_enqueue_style( 'perfect-scrollbar', WPAS_URI . 'assets/perfect-scrollbar/css/perfect-scrollbar.min.css' );
			wp_enqueue_style( 'perfect-scrollbar-wpc', WPAS_URI . 'assets/perfect-scrollbar/css/custom-theme.css' );
			wp_enqueue_script( 'perfect-scrollbar', WPAS_URI . 'assets/perfect-scrollbar/js/perfect-scrollbar.jquery.min.js', array( 'jquery' ), WPAS_VERSION, true );
			wp_enqueue_style( 'wpas', WPAS_URI . 'assets/css/backend.css' );
			wp_enqueue_script( 'wpas', WPAS_URI . 'assets/js/backend.js', array( 'jquery' ), WPAS_VERSION, true );
			wp_localize_script( 'wpas', 'wpas_vars', array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'wpas_nonce' => wp_create_nonce( 'wpas-nonce' )
			) );
		}

		function wpas_ajax() {
			if ( ! isset( $_POST['wpas_nonce'] ) || ! wp_verify_nonce( $_POST['wpas_nonce'], 'wpas-nonce' ) ) {
				die( esc_html__( 'Permissions check failed!', 'wpas' ) );
			}

			//settings
			$per_page = get_option( 'wpas_search_limit', '30' );
			$keyword  = trim( esc_html( $_POST['key'] ) );
			$command  = substr( $keyword, 0, 2 );

			switch ( $command ) {
				case '/p':
					//search product
					echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the product', 'wpas' ), '/p {keyword or sku}' ) . ' (<a href="' . admin_url( 'admin.php?page=wpclever-wpas&tab=premium' ) . '">Premium Version</a>)</span></div>';
					break;
				case '/o':
					//search order
					echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the order', 'wpas' ), '/o {order number}' ) . ' (<a href="' . admin_url( 'admin.php?page=wpclever-wpas&tab=premium' ) . '">Premium Version</a>)</span></div>';
					break;
				case '/m':
					$keyword = trim( substr( $keyword, 2, strlen( $keyword ) ) );
					//search menu
					echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the admin menu', 'wpas' ), '/m {keyword}' ) . '</span></div>';
					$wpas_menus = get_option( '_wpas_menus' );

					if ( is_array( $wpas_menus ) && count( $wpas_menus ) > 0 ) {
						foreach ( $wpas_menus as $wpas_menu ) {
							if ( ( strpos( strtolower( $wpas_menu['pre'] ), strtolower( $keyword ) ) !== false ) || ( strpos( strtolower( $wpas_menu['name'] ), strtolower( $keyword ) ) !== false ) ) {
								echo '<div class="line"><span class="main"><a href="' . admin_url( $wpas_menu['url'] ) . '">' . ( $wpas_menu['pre'] != '' ? $wpas_menu['pre'] . ' → ' : '' ) . strip_tags( $wpas_menu['name'] ) . '</a></span></div>';
							}
						}
					}

					break;
				case '/t':
					//search term
					echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the category, tag and other terms', 'wpas' ), '/t {keyword}' ) . '</span></div>';
					$keyword    = trim( substr( $keyword, 2 ) );
					$taxonomies = get_taxonomies( array(
						'public' => true
					) );
					$terms      = get_terms( $taxonomies, array(
						'orderby'    => 'name',
						'order'      => 'ASC',
						'hide_empty' => false,
						'search'     => $keyword,
						'number'     => $per_page
					) );

					if ( is_array( $terms ) && ( count( $terms ) > 0 ) ) {
						foreach ( $terms as $term ) {
							echo '<div class="line"><span class="main"><a href="' . get_edit_term_link( $term->term_id, $term->taxonomy ) . '">' . esc_html( $term->name ) . '</a></span><span class="data">' . $term->term_id . '</span><span class="data">' . $term->taxonomy . '</span><span class="act"><a href="' . get_term_link( $term->term_id ) . '"><i class="icon-link"></i></a></span></div>';
						}
					} else {
						echo '<div class="line"><span class="main">' . sprintf( esc_html__( 'No results found for "%s"', 'wpas' ), $keyword ) . '</span></div>';
					}

					break;
				case '/u':
					//search user
					echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the user', 'wpas' ), '/u {keyword}' ) . '</span></div>';
					$keyword    = trim( substr( $keyword, 2 ) );
					$user_query = new WP_User_Query( array(
						'search' => '*' . $keyword . '*',
						'number' => $per_page
					) );

					if ( is_array( $user_query->results ) && ( count( $user_query->results ) > 0 ) ) {
						foreach ( $user_query->results as $user ) {
							echo '<div class="line"><span class="main"><a href="' . get_edit_user_link( $user->ID ) . '">' . esc_html( $user->user_login ) . '</a></span><span class="data">' . $user->ID . '</span><span class="data">' . $user->user_email . '</span><span class="act"><a href="' . get_author_posts_url( $user->ID ) . '"><i class="icon-link"></i></a></span></div>';
						}
					} else {
						echo '<div class="line"><span class="main">' . sprintf( esc_html__( 'No results found for "%s"', 'wpas' ), $keyword ) . '</span></div>';
					}

					break;
				default:
					if ( strtolower( $keyword[0] ) == '/' ) {
						echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the post, page, product and other post types', 'wpas' ), '{keyword}' ) . '</span></div>';
						echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the category, tag and other terms', 'wpas' ), '/t {keyword}' ) . '</span></div>';
						echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the user', 'wpas' ), '/u {keyword}' ) . '</span></div>';
						echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the admin menu', 'wpas' ), '/m {keyword}' ) . '</span></div>';
						echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the product', 'wpas' ), '/p {keyword or sku}' ) . ' (<a href="' . admin_url( 'admin.php?page=wpclever-wpas&tab=premium' ) . '">Premium Version</a>)</span></div>';
						echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the order', 'wpas' ), '/o {order number}' ) . ' (<a href="' . admin_url( 'admin.php?page=wpclever-wpas&tab=premium' ) . '">Premium Version</a>)</span></div>';
					} else {
						//search post, page
						echo '<div class="line tip"><span class="main">' . sprintf( esc_html__( '%s - search the post, page, product and other post types', 'wpas' ), '{keyword}' ) . '</span></div>';
						$post_types = get_post_types( '', 'names' );

						if ( is_array( get_option( 'wpas_post_types' ) ) ) {
							$saved_post_types = get_option( 'wpas_post_types' );

							if ( ( count( $saved_post_types ) > 0 ) && ! in_array( '__all', $saved_post_types ) ) {
								$post_types = $saved_post_types;
							}
						}

						if ( is_array( get_option( 'wpas_post_statuses' ) ) ) {
							$saved_post_statuses = get_option( 'wpas_post_statuses' );

							if ( ( count( $saved_post_statuses ) > 0 ) && ! in_array( '__all', $saved_post_statuses ) ) {
								$post_statuses = $saved_post_statuses;
							} else {
								$post_statuses = 'any';
							}
						} else {
							$post_statuses = 'any';
						}

						$query_args = array(
							'is_wpas'        => true,
							'post_type'      => $post_types,
							'post_status'    => $post_statuses,
							's'              => $keyword,
							'posts_per_page' => $per_page
						);
						$query      = new WP_Query( $query_args );

						if ( $query->have_posts() ) {
							while ( $query->have_posts() ) {
								$query->the_post();
								$pid = get_the_ID();
								echo '<div class="line"><span class="main"><a href="' . get_edit_post_link( $pid ) . '">' . get_the_title() . '</a></span><span class="data data-50">' . $pid . '</span><span class="data data-80">' . get_post_type( $pid ) . '</span><span class="data data-50">' . get_post_status( $pid ) . '</span><span class="act"><a href="' . get_permalink( $pid ) . '"><i class="icon-link"></i></a></span></div>';
							}
						} else {
							echo '<div class="line"><span class="main">' . sprintf( esc_html__( 'No results found for "%s"', 'wpas' ), $keyword ) . '</span></div>';
						}
					}

					break;
			}

			die;
		}

		function wpas_quick_setting() {
			if ( ! isset( $_POST['wpas_nonce'] ) || ! wp_verify_nonce( $_POST['wpas_nonce'], 'wpas-nonce' ) ) {
				die( esc_html__( 'Permissions check failed!', 'wpas' ) );
			}

			$setting = esc_attr( $_POST['setting'] );
			$value   = esc_attr( $_POST['value'] );
			update_option( $setting, $value );
			die();
		}

		function wpas_admin_bar_menu( $wp_admin_bar ) {
			$wp_admin_bar->add_node( array(
				'id'    => 'wpas',
				'title' => '<i class="icon-menu"></i>',
				'href'  => '#',
				'meta'  => array( 'class' => 'wpas_menu' )
			) );
		}

		function wpas_action_links( $links, $file ) {
			static $plugin;

			if ( ! isset( $plugin ) ) {
				$plugin = plugin_basename( __FILE__ );
			}

			if ( $plugin == $file ) {
				$settings_link = '<a href="' . admin_url( 'admin.php?page=wpclever-wpas&tab=settings' ) . '">' . esc_html__( 'Settings', 'wpas' ) . '</a>';
				$links[]       = '<a href="' . admin_url( 'admin.php?page=wpclever-wpas&tab=premium' ) . '">' . esc_html__( 'Premium Version', 'wpas' ) . '</a>';
				array_unshift( $links, $settings_link );
			}

			return (array) $links;
		}

		function wpas_row_meta( $links, $file ) {
			static $plugin;

			if ( ! isset( $plugin ) ) {
				$plugin = plugin_basename( __FILE__ );
			}

			if ( $plugin == $file ) {
				$row_meta = array(
					'support' => '<a href="https://wpclever.net/support?utm_source=support&utm_medium=wpas&utm_campaign=wporg" target="_blank">' . esc_html__( 'Premium support', 'wpas' ) . '</a>',
				);

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		function wpas_widgets_init() {
			register_sidebar( array(
				'id'            => 'wpas-sidebar',
				'name'          => esc_html__( 'WPAS Sidebar', 'wpas' ),
				'description'   => esc_html__( 'Widgets in this area will be shown on WPC Smart Search (WPAS).', 'wpas' ),
				'before_widget' => '<div id="%1$s" class="wpas_widget widget %2$s">',
				'after_widget'  => '</div></div>',
				'before_title'  => '<div class="wpas_widget_header">',
				'after_title'   => '</div><div class="wpas_widget_content">',
			) );
			register_widget( 'WPAS_At_A_Glance' );
			register_widget( 'WPAS_Recent_Access' );
			register_widget( 'WPAS_WooCommerce_Status' );
		}

		function wpas_footer() {
			$wpas_recent_access = array();

			if ( is_admin() ) {
				$current_page_title = get_admin_page_title();
			} else {
				$current_page_title = wpas_wp_title();
			}

			$site_url          = get_bloginfo( 'wpurl' );
			$site_url          = str_replace( 'http://', '', $site_url );
			$site_url          = str_replace( 'https://', '', $site_url );
			$site_url          = str_replace( 'www.', '', $site_url );
			$current_page_link = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$current_page_link = str_replace( $site_url, '', $current_page_link );

			if ( ( $current_page_title != '' ) && ( $current_page_link != '' ) ) {
				if ( get_option( 'wpas_recent_access' ) != '' ) {
					$wpas_recent_access = get_option( 'wpas_recent_access' );

					if ( count( $wpas_recent_access ) >= 20 ) {
						$wpas_recent_access = array_slice( $wpas_recent_access, 0, 19 );
					}
				}
				//insert first item
				array_unshift( $wpas_recent_access, array(
					'title' => $current_page_title,
					'link'  => $current_page_link,
					'time'  => time()
				) );
				update_option( 'wpas_recent_access', $wpas_recent_access );
			}
			?>
            <div class="wpas_box">
                <div class="wpas_overlay"></div>
                <div class="wpas_sidebar">
                    <div class="wpas_header wpas_color_bg">
						<?php
						$current_user = wp_get_current_user();
						echo '<span class="avt">' . get_avatar( $current_user->ID, 16 ) . '</span>';
						echo '<span>' . sprintf( esc_html__( 'Howdy, %s', 'wpas' ), $current_user->display_name ) . '</span>';
						?>
                        <span class="actions">
							<span class="wpas_settings_btn">
								<i class="icon-cog"></i>
							</span>
							<span class="wpas_close_btn">
								<i class="icon-cross"></i>
							</span>
						</span>
                    </div>
                    <div class="wpas_content">
                        <div class="wpas_settings">
                            <div class="wpas_settings_line">
                                <div class="wpas_settings_item">
                                    <input class="wpas_quick_setting" data-for="wpas_search_exact"
                                           type="checkbox" <?php echo( get_option( 'wpas_search_exact', 'no' ) == 'yes' ? 'checked' : '' ); ?>/> <?php esc_html_e( 'Search exact', 'wpas' ); ?>
                                </div>
                                <div class="wpas_settings_item">
                                    <input class="wpas_quick_setting" data-for="wpas_search_sentence"
                                           type="checkbox" <?php echo( get_option( 'wpas_search_sentence', 'no' ) == 'yes' ? 'checked' : '' ); ?>/> <?php esc_html_e( 'Search sentence', 'wpas' ); ?>
                                </div>
                                <div class="wpas_settings_item">
                                    <a href="<?php echo admin_url( 'admin.php?page=wpclever-wpas&tab=settings' ); ?>"><?php esc_html_e( 'All settings', 'wpas' ); ?></a>
                                </div>
                            </div>
                        </div>
                        <div class="wpas_search">
                            <div class="wpas_search_input">
                                <input id="wpas_search_input" type="search"
                                       placeholder="<?php esc_html_e( 'Search...', 'wpas' ); ?>" name="s"
                                       autocomplete="off"/>
                            </div>
                            <div class="wpas_search_content">
                                <div class="wpas_search_result">
                                    <div class="line tip">
									<span class="main">
										<?php esc_html_e( 'Type keyword to search post, page, product and other post-types', 'wpas' ); ?>
									</span>
                                    </div>
                                    <div class="line tip">
									<span class="main">
										<?php esc_html_e( 'Or type / to get help', 'wpas' ); ?>
									</span>
                                    </div>
                                </div>
                            </div>
                        </div>
						<?php if ( is_active_sidebar( 'wpas-sidebar' ) ) {
							dynamic_sidebar( 'wpas-sidebar' );
						} else {
							echo '<div class="wpas_notice">' . esc_html__( 'Please go to Appearance >> Widgets to add more useful widgets into WPAS Sidebar.', 'wpas' ) . '</div>';
						} ?>
                    </div>
                </div>
            </div>
			<?php
		}

		function wpas_search_exact( $query ) {
			if ( $query->is_search && $query->query['is_wpas'] ) {
				$query->set( 'exact', true );
			}
		}

		function wpas_search_sentence( $query ) {
			if ( $query->is_search && $query->query['is_wpas'] ) {
				$query->set( 'sentence', true );
			}
		}
	}

	new WPCleverWpas();
}