# WordPress image gallery meta box plugin

[![Build Status](https://travis-ci.org/barryceelen/wp-gallery-3000.svg?branch=master)](https://travis-ci.org/barryceelen/wp-gallery-3000)

Adds an image gallery meta box to the post and page editor. The gallery image IDs are stored in the `_gallery_3000` custom field.

**Note:** This plugin does not provide any template tags or gallery functionality to show the images in your theme.

A simplified example of how this plugin can be used in your theme:

```
$ids  = get_post_meta( $post_id, '_gallery_3000', true );

foreach ( $ids as $id ) {
	echo wp_get_attachment_image( $id, 'medium' );
}
```

By default, the plugin adds its meta box to the 'post' and 'page' post types. This can be changed by filtering the post types where the gallery meta box should be added:

```
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
```
