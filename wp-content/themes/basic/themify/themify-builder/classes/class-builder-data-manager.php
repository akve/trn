<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ThemifyBuilder_Data_Manager {

	var $meta_key;

	public function __construct() {
		$this->meta_key = '_themify_builder_settings_json';
		add_filter( 'themify_builder_data', array( $this, 'themify_builder_data' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'builder_154_update' ) );
		add_action( 'themify_after_demo_import', array( $this, 'redo_builder_154_update' ) );
		add_action( 'import_post_meta', array( $this, 'import_post_meta' ), 10, 3 );
	}

	public function themify_builder_data( $builder_data, $post_id ) {
		$new_data = $this->get_data( $post_id );

		/* if the new post meta for builder does not exists, create it */
		if( ! empty( $builder_data ) && empty( $new_data ) ) {
			 /* save the data in json format */
			$this->save_data( $builder_data, $post_id );

			/* re-try retrieving it back */
			$new_data = $this->get_data( $post_id );
		}

		if( ! is_array( $new_data ) ) {
			$new_data = array();
		}

		return $new_data;
	}

	/* helpers */
	public function get_data( $post_id ) {
		$data = get_post_meta( $post_id, $this->meta_key, true );
		$data = stripslashes_deep( json_decode( $data, true ) );

		return $data;
	}

	public function save_data( $builder_data, $post_id ) {
		global $ThemifyBuilder;

		 /* if it's serialized, convert to array */
		if( is_serialized( $builder_data ) ) {
			$builder_data = stripslashes_deep( unserialize( $builder_data ) );
		}
		$builder_data = self::array_map_deep( $builder_data, 'wp_slash' );
		$builder_data = json_encode( $builder_data );

		/* slashes are removed by update_post_meta, apply twice to protect slashes */
		$builder_data = wp_slash( $builder_data );

		/* save the data in json format */
		update_post_meta( $post_id, $this->meta_key, $builder_data );

		/* remove the old data format */
		delete_post_meta( $post_id, $ThemifyBuilder->meta_key );
		Themify_Builder::remove_cache($post_id);
	}

	/**
	 * Utility function to apply callback on all items of array, recursively
	 *
	 * return array
	 */
	public static function array_map_deep( array $array, $callback, $on_nonscalar = false ) {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$args = array($value, $callback, $on_nonscalar);
				$array[$key] = call_user_func_array(array(__CLASS__, __FUNCTION__), $args);
			} elseif (is_scalar($value) || $on_nonscalar) {
				$array[$key] = call_user_func($callback, $value);
			}
		}
		return $array;
	}

	/**
	 * fix importing Builder contents using WP_Import
	 */
	function import_post_meta( $post_id, $key, $value ) {
		if( $key == $this->meta_key ) {
			/* slashes are removed by update_post_meta, add it to protect the data */
			$builder_data = wp_slash( $value );

			/* save the data in json format */
			update_post_meta( $post_id, $this->meta_key, $builder_data );
		}
	}

	/**
	 * Runs once after the 1.5.4 Builder upgrade to update all posts
	 */
	public function builder_154_update() {
		global $ThemifyBuilder;
		if( get_option( 'builder_154_update_done' ) == 'yes' )
			return;
		$posts = get_posts(
			array(
				'post_type' => 'any',
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => $ThemifyBuilder->meta_key,
						'meta_compare' => 'EXISTS'
					)
				)
			)
		);
		if( $posts ) {
			foreach( $posts as $post ) {
				/* get the data, it will automatically update the database */
				$ThemifyBuilder->get_builder_data( $post->ID );
			}
		}
		update_option( 'builder_154_update_done', 'yes' );
	}

	public function redo_builder_154_update() {
		delete_option( 'builder_154_update_done' );
	}
}

$GLOBALS['ThemifyBuilder_Data_Manager'] = new ThemifyBuilder_Data_Manager();