<?php
/**
 * Template tags for displaying Database contents
 */


if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! function_exists( 'rdb_list_dir' ) ) {
	/**
	 * List directory contents
	 *
	 * @param $dir_id
	 */
	function rdb_list_dir( $dir_id ) {
		return \notne\rdb\DB::list_dir( $dir_id );
	}

	/**
	 * Returns the directory
	 *
	 * @return string   The directory listing markup
	 */
	function rdb_get_dir( $dir_id ) {
		$name     = rdb_get_name( $dir_id );
		$children = rdb_list_dir( $dir_id );

		$output = rdb_tab_lvl( "<h2 class='rdb-item-title'>$name</h2>", 0 );
		$output .= rdb_tab_lvl( '<ul class="rdb-item-ul">', 0 );

		foreach ( $children as $child ) {
			$output .= rdb_tab_lvl(
				sprintf( '<li class="rdb-item-li"><a href="%1$s">%2$s</a></li>',
					$child['item_download_url'],
					$child['item_title'] ),
				1 );
		}

		$output .= rdb_tab_lvl( '</ul>', 0 );

		return wp_kses( $output, array(
			'h2' => array(),
			'ul' => array(
				'class' => array()
			),
			'li' => array(
				'class' => array()
			)
		) );
	}

	/**
	 * Wrapper function for rdb_get_dir
	 *
	 * @param $dir_id
	 */
	function rdb_the_dir( $dir_id ) {
		echo rdb_get_dir( $dir_id );
	}
}

if ( ! function_exists( 'rdb_list_item' ) ) {
	/**
	 * Get an item
	 *
	 * @param $item_id
	 */
	function rdb_list_item( $item_id ) {
		return \notne\rdb\DB::get_item( $item_id );
	}

	/**
	 * Echoes the item
	 *
	 * @param string $item_id The ID
	 * @param mixed $options (Optional)  An array of strings, each of which maps to a field in the database.
	 *                      Adding a field here appends it to the output. The key item_groups is not
	 *                      included for security reasons.
	 */
	function rdb_get_item( $item_id, $options = array() ) {
		$item   = rdb_list_item( $item_id );
		$output = '';

		if ( ! empty( $options ) ) {
			array_map( 'sanitize_text_field', $options );

			foreach ( $options as $option ) {
				if ( isset( $item[ $option ] ) ) {
					$output .= rdb_tab_lvl( "<span class='rdb-" . urlencode( $option ) . "'>" . $item[ $option ] . "</span>", 0 );
				}
			}

			return wp_kses( $output, array(
				'span' => array(
					'class' => array()
				)
			) );
		}

		$output = sprintf( '<span class="od-item_title"><a href="%1$s" class="rdb-item_title">%2$s</a></span>',
			$item['item_download_url'],
			$item['item_title'] );
		$output .= "<span class='rdb-item_modified'>" . $item['item_modified'] . "</span>";

		return wp_kses( $output, array(
			'span' => array(
				'class' => array()
			),
			'a'    => array(
				'href'  => array(),
				'class' => array()
			)
		) );
	}

	/**
	 * Wrapper function for rdb_get_item
	 *
	 * @param string $item_id The ID
	 * @param mixed $options (Optional)  An array of strings, each of which maps to a field in the database.
	 *                      Adding a field here appends it to the output. The key item_groups is not
	 *                      included for security reasons.
	 */
	function rdb_the_item( $item_id, $options = array() ) {
		echo rdb_get_item( $item_id, $options );
	}
}

if ( ! function_exists( 'rdb_get_parent_id' ) ) {
	/**
	 * Get an item's parent ID
	 *
	 * @param $item_id
	 */
	function rdb_get_parent_id( $item_id ) {
		return \notne\rdb\DB::get_parent( $item_id );
	}
}

if ( ! function_exists( 'rdb_list_children' ) ) {
	/**
	 * Get an item's children
	 *
	 * @param $item_id
	 * @param boolean $recurse Whether or not to recurse through the object's children
	 */
	function rdb_list_children( $item_id, $recurse = false ) {
		$children = \notne\rdb\DB::get_children( $item_id );

		if ( $recurse && ! empty( $children ) ) {
			return rdb_list_each_child( $children );
		}

		return $children;
	}

	/**
	 * Build an array of items and their children
	 *
	 * @param $children One-dimensional array of children
	 */
	function rdb_list_each_child( $children ) {
		$return = array();

		foreach ( $children as $child ) {
			unset( $child['children'] );
			if ( $child['item_type'] === 'dir' ) {
				if ( ! empty( $next_children = rdb_list_children( $child['item_id'] ) ) ) {
					$child['children'] = rdb_list_each_child( $next_children );
				}
			}

			$return[$child['item_title']] = $child;
		}

		return $return;
	}

	/**
	 * Gets the children
	 *
	 * Best-named function in the package.
	 *
	 * @param string $item_id The item ID
	 * @param boolean $expand_all Whether to expand all children in nested lists
	 */
	function rdb_get_children( $item_id, $expand_all ) {
		$children = rdb_list_children( $item_id );
		$output   = '';

		if ( is_a( $children, 'WP_Error' ) ) {
			return;
		}

		$output = rdb_tab_lvl( '<ul class="rdb-children">', 0 );

		$output .= rdb_each_child( $children, $expand_all, 1 );

		$output .= rdb_tab_lvl( '</ul>', 0 );

		return wp_kses( $output, array(
			'ul' => array(
				'class' => array()
			),
			'li' => array(),
			'a'  => array(
				'href'      => array(),
				'class'     => array(),
				'data-odid' => array()
			)
		) );
	}

	/**
	 * Self-recursing function to echo all the children and grandchildren and so on
	 *
	 * @param array $children The children, or top-level directory for each stage of recursion
	 * @param boolean $expand_all Whether or not to expand all. This is just true past the first level
	 * @param $lvl                  The level of recursion, used for anal-retentive tabbing
	 *
	 * @return string       The output
	 */
	function rdb_each_child( $children, $expand_all, $lvl ) {
		$output = '';

		foreach ( $children as $child ) {
			$next_children = rdb_list_children( $child['item_id'] );
			if ( $expand_all && $child['item_type'] === 'dir' && ! empty( $next_children ) ) {
				$output .= rdb_tab_lvl( "<li>" . esc_html( $child["item_title"] ), $lvl + 1 );
				$output .= rdb_tab_lvl( "<ul class='rdb-children nested'>", $lvl + 2 );
				$output .= rdb_each_child( $next_children, true, $lvl + 2 );
				$output .= rdb_tab_lvl( "</ul>", $lvl + 2 );
				$output .= rdb_tab_lvl( "</li>", $lvl + 1 );
			} else if ( $child['item_type'] === 'dir' ) {
				$output .= rdb_tab_lvl( "<li>" . esc_html( $child["item_title"] ) . "</li>", $lvl + 1 );
			} else {
				$output .= rdb_tab_lvl( sprintf( '<li><a href="%2$s" class="file-link" data-odid="%2$s">%3$s</a></li>',
					esc_url( $child['item_download_url'] ),
					esc_html( $child['item_id'] ),
					esc_html( $child['item_title'] )
				),
					$lvl + 1 );
			}
		}

		return $output;
	}

	/**
	 * Wrapper function to echo the children
	 *
	 * @param string $item_id
	 */
	function rdb_the_children( $item_id, $expand_all = false ) {
		echo rdb_get_children( $item_id, $expand_all );
	}
}

if ( ! function_exists( 'rdb_get_root_id' ) ) {
	/**
	 * Returns the root ID
	 */
	function rdb_get_root_id() {
		return \notne\rdb\DB::quicky_root_finder();
	}

	/**
	 * Echoes the root ID
	 */
	function rdb_the_root_id() {
		echo esc_attr( rdb_get_root_id() );
	}
}

if ( ! function_exists( 'rdb_get_name' ) ) {
	/**
	 * Get the name of an item
	 *
	 * @param string $item_id The ID
	 */
	function rdb_get_name( $item_id ) {
		$item = rdb_list_item( $item_id );

		return $item['item_title'];
	}

	/**
	 * Echo the item name
	 *
	 * @param string $item_id The ID
	 */
	function rdb_the_name( $item_id ) {
		echo esc_html( rdb_get_name( $item_id ) );
	}
}

if ( ! function_exists( 'rdb_get_dirs' ) ) {
	/**
	 * Get an array of directories in the database.
	 *
	 * Though not strictly a template tag, this belongs here for simplicity
	 */
	function rdb_get_dirs() {
		return \notne\rdb\DB::get_dirs();
	}

	/**
	 * Prints a list of directories as a multiselect box
	 *
	 * @param string $id The HTML name and id attribute
	 */
	function rdb_the_dirs_selector( $id, $term_meta = array() ) {
		$dirs     = rdb_get_dirs();
		$output   = rdb_tab_lvl( '<select name="' . $id . '[]" id="' . $id . '[]" size="10" multiple>', 0 );
		$selected = '';

		foreach ( $dirs as $dir ) {
			if ( ! empty( $term_meta ) && in_array( $dir, $term_meta ) ) {
				$selected = ' selected';
			}

			$output .= rdb_tab_lvl( '<option value="' . $dir . '"' . $selected . '>' . rdb_get_name( $dir ) . '</option>', 1 );

			$selected = '';
		}

		$output .= rdb_tab_lvl( '</select>', 0 );

		echo wp_kses( $output, array(
			'select' => array(
				'name'     => array(),
				'id'       => array(),
				'size'     => array(),
				'multiple' => array()
			),
			'option' => array(
				'value'    => array(),
				'selected' => array()
			)
		) );
	}
}

if ( ! function_exists( 'rdb_get_current_group_dirs' ) ) {
	/**
	 * Get the directories a user has access to vis-a-vis its group
	 *
	 * @return mixed|void   An array of directory IDs
	 */
	function rdb_get_current_group_dirs() {
		$terms = wp_get_object_terms( get_current_user_id(), 'rdb_od_user_group' );

		$term_id = $terms[0]->term_id;

		// retrieve the existing value(s) for this meta field. This returns an array
		$term_meta = get_option( "taxonomy_$term_id" );

		return $term_meta;
	}

	/**
	 * The golden apple-- where you print all the directories a user can see.
	 */
	function rdb_the_current_group_dirs() {
		$dir_ids = rdb_get_current_group_dirs();
		$output  = rdb_tab_lvl( "<div class='rdb_files'>", 0 );

		foreach ( $dir_ids as $dir_id ) {
			$output .= rdb_tab_lvl( rdb_get_dir( $dir_id ), 1 );
		}

		$output .= rdb_tab_lvl( "</div>", 0 );

		echo $output;
	}
}

if ( ! function_exists( 'rdb_tab_lvl' ) ) {
	/**
	 * So I can be retentive about tabbing output
	 */
	function rdb_tab_lvl( $string, $lvl ) {
		return str_repeat( "\t", $lvl ) . $string . "\n";
	}
}