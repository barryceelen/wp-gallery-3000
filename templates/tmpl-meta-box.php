<?php
/**
 * Gallery meta box content
 *
 * @package   Gallery_3000
 * @author    Barry Ceelen
 * @license   GPL-3.0+
 * @link      https://github.com/barryceelen/wp-gallery-3000
 * @copyright Barry Ceelen
 */

// Don't load directly.
defined( 'ABSPATH' ) || die();
?>

<div class="gallery-3000 gallery-3000-empty" data-gallery-3000-wrap="true">
	<ul class="gallery-3000-items" data-gallery-3000="true"></ul>
	<div class="gallery-3000-footer">
		<?php
		printf(
			'<button type="button" class="button gallery-3000-add-images-button" data-gallery-3000-add="true">%s</button>',
			esc_html__( 'Add Images', 'gallery-3000' )
		);
		?>
	</div>
</div>

<script type="text/html" id="gallery-3000-item-template">
	<li class="gallery-3000-item" data-gallery-3000-item="{{id}}">
		<input type="hidden" name="gallery_3000_items[]" value="{{id}}">
		<figure class="gallery-3000-item-thumbnail" style="width:{{width}}px;height:{{height}}px;">
			<div class="gallery-3000-item-thumbnail-wrap" style="padding-bottom: {{ratio}}%;">
				<img class="gallery-3000-item-thumbnail-image" src="{{url}}" />
			</div>
		</figure>
		<button class="gallery-3000-item-delete" data-gallery-3000-item-delete="true">
			<span class="gallery-3000-item-delete-icon">
				<span class="screen-reader-text"><?php esc_html_e( 'Remove gallery item', 'gallery-3000' ); ?></span>
			</span>
		</button>
		<button class="gallery-3000-item-edit" data-gallery-3000-item-edit="true">
			<span class="gallery-3000-item-edit-icon">
				<span class="screen-reader-text"><?php esc_html_e( 'Edit gallery item', 'gallery-3000' ); ?></span>
			</span>
		</button>
	</li>
</script>
