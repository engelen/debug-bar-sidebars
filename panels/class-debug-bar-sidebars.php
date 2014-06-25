<?php
class DBSW_Debug_Bar_Sidebars extends Debug_Bar_Panel {

	public $sidebars = array();

	public function init() {
		$this->title( __( 'Sidebars', 'dbsw' ) );

		add_action( 'dynamic_sidebar_before', array( $this, 'add_sidebar' ), 10, 2 );
		add_action( 'dynamic_sidebar', array( $this, 'add_widget' ) );
	}

	public function add_sidebar( $index, $has_widgets ) {
		global $wp_registered_sidebars;

		$this->sidebars[] = (object) array(
			'index' => $index,
			'has_widgets' => $has_widgets,
			'widgets' => array()
		);
	}

	public function add_widget( $widget ) {
		$current_sidebar = count( $this->sidebars ) - 1 ;

		$this->sidebars[ $current_sidebar ]->widgets[] = $widget;
	}

	public function prerender() {
		$this->set_visible( ! is_admin() );
	}

	public function render() {
		global $wp_registered_sidebars, $wp_registered_widgets;

		$cas_enabled = false;

		if ( class_exists( 'ContentAwareSidebars' ) ) {
			$cas_enabled = true;
			$cas_sidebars = array();
			$cas_sidebars_raw = ContentAwareSidebars::instance()->get_sidebars();

			if ( $cas_sidebars_raw ) {
				foreach ( $cas_sidebars_raw as $cas_sidebar ) {
					$id = ContentAwareSidebars::SIDEBAR_PREFIX . $cas_sidebar->ID;
					$host = get_post_meta( $cas_sidebar->ID, ContentAwareSidebars::PREFIX . 'host', true );

					if ( ! isset( $cas_sidebars[ $host ] ) ) {
						$cas_sidebars[ $host ] = array();
					}

					$cas_sidebars[ $host ][ $id ] = $cas_sidebar;
				}
			}

			$handles = array(
				0 => __( 'Replace', ContentAwareSidebars::DOMAIN ),
				1 => __( 'Merge', ContentAwareSidebars::DOMAIN ),
				2 => __( 'Manual', ContentAwareSidebars::DOMAIN ),
				3 => __( 'Forced replace', ContentAwareSidebars::DOMAIN )
			);
		}

		echo '<div class="dbsw-debug-bar-panel">';

		foreach ( $this->sidebars as $sidebar ) {
			echo '<h3>' . $wp_registered_sidebars[ $sidebar->index ]['name'] . ' <span>(' . $sidebar->index . ')</span></h3>';

			if ( $cas_enabled ) {
				echo '<strong>' . __( 'Content Aware Sidebars enabled.', 'dbsw' ) . '</strong> ';

				if ( empty( $cas_sidebars[ $sidebar->index ] ) ) {
					_e( 'No replacement sidebars found.', 'dbsw' );
				}
				else {
					printf( __( '%d replacement sidebars found:', 'dbsw' ), count( $cas_sidebars[ $sidebar->index ] ) );

					echo "<br/>\n";

					foreach ( $cas_sidebars[ $sidebar->index ] as $cas_sidebar ) {
						$handle = isset( $handles[ $cas_sidebar->handle ] ) ? $handles[ $cas_sidebar->handle ] : $cas_sidebar->handle;
						$mergepos = get_post_meta( $post->ID, ContentAwareSidebars::PREFIX . 'merge-pos', true ) ? __( 'Top', ContentAwareSidebars::DOMAIN ) : __( 'Bottom', ContentAwareSidebars::DOMAIN );
						
						echo '<em>' . get_the_title( $cas_sidebar->ID ) . '</em>: ' . $handle . ' (' . $mergepos . ')' . "<br/>\n";
					}
				}

				echo "<br/>\n";
			}

			if ( $sidebar->has_widgets ) {
				$widgets = array();
				$num_widgets_displayed = 0;

				foreach ( $sidebar->widgets as $widget ) {
					$displayed = false;

					if ( is_callable( $widget['callback'] ) ) {
						$displayed = true;
						$num_widgets_displayed++;
					}

					$widgets[] = (object) array(
						'widget' => $widget,
						'displayed' => $displayed
					);
				}

				echo '<p>' . sprintf( __( '%d widgets available; %d widgets displayed.', 'dbsw' ), count( $sidebar->widgets ), $num_widgets_displayed ) . '</p>';
				
				echo '<ol>';

				foreach ( $widgets as $widget ) {
					$registered_widget = $wp_registered_widgets[ $widget->widget['id'] ];
					$title = '';

					if ( is_array( $registered_widget['callback'] ) && is_callable( array( $registered_widget['callback'][0], 'get_settings' ) ) ) {
						$instance = $registered_widget['callback'][0]->get_settings();

						if ( isset( $instance[ $widget->widget['params'][0]['number'] ]['title'] ) ) {
							$title = $instance[ $widget->widget['params'][0]['number'] ]['title'];
						}
					}

					echo '<li>';
					echo '<h4><strong>' . $widget->widget['name'] . '</strong>' . ( $title ? '<em>: ' . $title . '</em>' : '' ) . ' <span>(' . $widget->widget['id'] . ')</span></h4>';

					if ( ! $widget->displayed ) {
						$callback = $widget->widget['callback'];

						if ( ! is_array( $callback ) ) {
							$callback_formatted = $callback;
						}
						else {
							if ( is_object( $callback[0] ) ) {
								$callback_formatted = '<code>' . get_class( $callback[0] ) . '-&gt;' . $callback[1] . '()</code>';
							}
							else {
								$callback_formatted = $callback[0] . '::' . $callback[1] . '()';
							}
						}

						echo '<p>(!) ' . sprintf( __( 'Widget not displayed; %s not callable.', 'dbsw' ), $callback_formatted ) . '</p>';
					}

					echo '</li>';
				}

				echo '</ol>';
			}
			else {
				echo '<p>' . __( 'No widgets available.', 'dbsw' ) . '</p>';
			}
		}

		echo '</div>';
	}

}
