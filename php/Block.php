<?php
/**
 * Block class.
 *
 * @package SiteCounts
 */

namespace XWP\SiteCounts;

use WP_Block;

/**
 * The Site Counts dynamic block.
 *
 * Registers and renders the dynamic block.
 */
class Block {

	/**
	 * The Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Instantiates the class.
	 *
	 * @param Plugin $plugin The plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Adds the action to register the block.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Registers the block.
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin->dir(),
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array    $attributes The attributes for the block.
	 * @param string   $content    The block content, if any.
	 * @param WP_Block $block      The instance of this block.
	 * @return string The markup of the block.
	 */
	public function render_callback( $attributes, $content, $block )
	{
		$post_types = get_post_types( [ 'public' => true ] );
		$class_name = array_key_exists( 'className', $attributes ) ? esc_attr( strval( $attributes['className'] ) ) : '';

		$block_markup = sprintf('<div class="%1$s"><h2>%2$s</h2>', $class_name, __('Post Counts', 'sitecounts') );

		array_walk( $post_types, function($v, $k) use (&$block_markup) {
			$post_type_object = get_post_type_object( $v );
			$post_type_labels = get_post_type_labels( $post_type_object );
			$post_type_counts = wp_count_posts( $post_type_labels->name );

			$block_markup .= sprintf('<p>%1$s %2$d %3$s.</p>', _n('There is', 'There are', $post_type_counts, 'sitecounts'), $post_type_counts, _n( $post_type_labels->singular_name, $post_type_labels->name, $post_type_counts, 'sitecounts' ) );
		});

		$block_markup .=  sprintf('<p>The current post ID is %s.</p>', sanitize_text_field( strval($_GET['post_id']) ) );

		return apply_filters( 'sitecounts_block_markup', $block_markup, $post_type_counts, $post_type_labels );
	}
}
