<?php // @codingStandardsIgnoreLine
/**
 * Plugin Name:     Gutena Tabs
 * Description:     Gutena Tabs is a simple and easy-to-use WordPress plugin which allows you to create beautiful tabs in your posts and pages. The plugin is simple to use but provides many customization options so you can create tabs that look great and fit into your design. Additionally, You can add beautiful icons to the tabs.
 * Version:         1.0.1
 * Author:          ExpressTech
 * Author URI:      https://expresstech.io
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     gutena-tabs
 *
 * @package         gutena-tabs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Abort if the class is already exists.
 */
if ( ! class_exists( 'Gutena_Tabs' ) ) {

	/**
	 * Gutena Advanced Tabs class.
	 *
	 * @class Main class of the plugin.
	 */
	class Gutena_Tabs {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		public $version = '1.0.1';

		/**
		 * Child Block styles.
		 *
		 * @since 1.0.1
		 * @var array
		 */
		public $styles = [];

		/**
		 * Instance of this class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		protected static $instance;

		/**
		 * Get the singleton instance of this class.
		 *
		 * @since 1.0.0
		 * @return Gutena_Tabs
		 */
		public static function get() {
			if ( ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'register' ] );
			add_filter( 'block_categories_all', [ $this, 'register_category' ], 10, 2 );
		}

		/**
		 * Register required functionalities.
		 */
		public function register() {
			// Register blocks.
			register_block_type( __DIR__ . '/build', [
				'render_callback' => [ $this, 'render_block' ],
			] );

			// Register blocks.
			register_block_type( __DIR__ . '/build/tab', [
				'render_callback' => [ $this, 'render_tab_block' ],
			] );
		}

		/**
		 * Render Gutena play button block.
		 */
		public function render_tab_block( $attributes, $content, $block ) {
			if ( ! empty( $attributes['blockStyles'] ) && is_array( $attributes['blockStyles'] ) && ! empty( $attributes['tabBorder']['enable'] ) ) {
				$this->styles[ $attributes['parentUniqueId'] ][ $attributes['tabId'] ] = $attributes['blockStyles'];
			}
			
			return $content;
		}

		/**
		 * Render Gutena play button block.
		 */
		public function render_block( $attributes, $content, $block ) {
			add_action(
				'wp_head',
				function() use ( $attributes ) {
					$styles = '';
					if ( ! empty( $attributes['blockStyles'] ) && is_array( $attributes['blockStyles'] ) ) {
						$styles .= sprintf( 
							'.gutena-tabs-block-%1$s { %2$s }',
							esc_attr( $attributes['uniqueId'] ),
							$this->render_css( $attributes['blockStyles'] ),
						);

						if ( ! empty( $this->styles[ $attributes['uniqueId'] ] ) ) {
							foreach ( $this->styles[ $attributes['uniqueId'] ] as $tab_id => $style ) {
								$styles .= sprintf( 
									'.gutena-tabs-block-%1$s .gutena-tabs-tab .gutena-tab-title[data-tab="%2$s"] { %3$s }',
									esc_attr( $attributes['uniqueId'] ),
									esc_attr( $tab_id ),
									$this->render_css( $style ),
								);
							}
						}
					}

					// print css
					if ( ! empty( $styles ) ) {
						printf(
							'<style id="gutena-tabs-css-%1$s">%2$s</style>',
							esc_attr( $attributes['uniqueId'] ),
							$styles,
						);
					}
				}
			);

			return $content;
		}

		/**
		 * Generate dynamic styles
		 *
		 * @param array $styles
		 * @return string
		 */
		private function render_css( $styles ) {
			$style = [];
			foreach ( (array) $styles as $key => $value ) {
				$style[] = $key . ': ' . $value;
			}

			return join( ';', $style );
		}

		/**
		 * Register block category.
		 */
		public function register_category( $block_categories, $editor_context ) {
			$fields = wp_list_pluck( $block_categories, 'slug' );
			
			if ( ! empty( $editor_context->post ) && ! in_array( 'gutena', $fields, true ) ) {
				array_push(
					$block_categories,
					[
						'slug'  => 'gutena',
						'title' => __( 'Gutena', 'gutena-tabs' ),
					]
				);
			}

			return $block_categories;
		}
	}
}

/**
 * Check the existance of the function.
 */
if ( ! function_exists( 'gutena_tabs_init' ) ) {
	/**
	 * Returns the main instance of Gutena_Tabs to prevent the need to use globals.
	 *
	 * @return Gutena_Tabs
	 */
	function gutena_tabs_init() {
		return Gutena_Tabs::get();
	}

	// Start it.
	gutena_tabs_init();
}