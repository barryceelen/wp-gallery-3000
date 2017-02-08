# WordPress image gallery meta box plugin

Adds an image gallery meta box to the post and page editor.
Filter the post types where the gallery meta box should be added:

	add_filter( 'gallery_3000_post_types', 'myprefix_filter_options' );

	public function myprefix_filter_options( $post_types ) {
    
    // Remove meta box from the 'page' post type edit screen.
    if ( ! empty( $post_types['page'] ) {
       unset( $post_types['page'] );
    }

    // Add meta box to the 'my_cool_post_type' custom post type edit screen.
    if ( empty( $post_types['my_cool_post_type'] ) {
       $post_types[] = 'my_cool_post_type';
    }
    
    return $post_types;
	}
