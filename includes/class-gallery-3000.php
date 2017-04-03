<?php
/**
 * Image gallery meta box class.
 *
 * @package   Gallery_3000
 * @author    Barry Ceelen
 * @license   GPL-3.0+
 * @link      https://github.com/barryceelen/wp-gallery-3000
 * @copyright Barry Ceelen
 */

/**
 * Class containing the functionality for adding and saving the meta box.
 */
class Gallery_3000 {

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {

		/**
		 * Filters The post types for which the gallery meta box is added.
		 *
		 * @since 3.9.0
		 *
		 * @param array $post_types The post types for which the gallery meta box is added to
		 *                          the edit screen. Defaults are 'post' and 'page'.
		 */
		$this->post_types = apply_filters( 'gallery_3000_post_types', array( 'post', 'page' ) );

		foreach ( $this->post_types as $post_type ) {
			add_post_type_support( $post_type, 'gallery-3000' );
		}

		$this->add_actions_and_filters();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Add actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function add_actions_and_filters() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'admin_print_footer_scripts-post.php', array( $this, 'enqueue_media' ) );
		add_action( 'admin_print_footer_scripts-post-new.php', array( $this, 'enqueue_media' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {

		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		global $post;

		if ( ! post_type_supports( $post->post_type, 'gallery-3000' ) ) {
			return;
		}

		wp_enqueue_script(
			'gallery-3000',
			GALLERY_3000_PLUGIN_URL . '/js/gallery-3000.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			null,
			true
		);

		$attachment_ids = get_post_meta( $post->ID, '_gallery_3000', true );
		$items          = array();

		if ( is_array( $attachment_ids ) ) {

			foreach ( $attachment_ids as $attachment_id ) {

				$src = wp_get_attachment_image_src( absint( $attachment_id ), 'thumbnail' );

				if ( $src ) {
					$items[] = array(
						'id'     => absint( $attachment_id ),
						'url'    => esc_url( $src[0] ),
						'width'  => absint( $src[1] ),
						'height' => absint( $src[2] ),
						'ratio'  => absint( ( $src[2] / $src[1] ) * 100 ),
					);
				}
			}
		}

		wp_localize_script(
			'gallery-3000',
			'gallery3000Vars',
			array(
 				'classNameEmpty'   => 'gallery-3000-empty',
				'classNameLoading' => 'gallery-3000-loading',
				'titleAdd'         => esc_html__( 'Add Images', 'gallery-3000' ),
				'titleUpdate'      => esc_html__( 'Update Image', 'gallery-3000' ),
				'buttonTextAdd'    => esc_html__( 'Add Item', 'gallery-3000' ),
				'buttonTextUpdate' => esc_html__( 'Update Item', 'gallery-3000' ),
				'galleryWrapEl'    => '[data-gallery-3000-wrap]',
				'galleryEl'        => '[data-gallery-3000]',
				'items'            => stripslashes( wp_json_encode( $items ) ),
				'buttonAdd'        => '[data-gallery-3000-add]',
				'buttonDelete'     => '[data-gallery-3000-item-delete]',
				'buttonEdit'       => '[data-gallery-3000-item-edit]',
				'template'         => '#gallery-3000-item-template',
			)
		);
	}

	/**
	 * Enqueue admin styles.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_styles( $hook_suffix ) {

		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		global $post;

		if ( ! post_type_supports( $post->post_type, 'gallery-3000' ) ) {
			return;
		}

		wp_enqueue_style(
			'gallery-3000',
			GALLERY_3000_PLUGIN_URL . 'css/gallery-3000.css'
		);
	}

	/**
	 * Add meta box to the post edit screen.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_box() {

		add_meta_box(
			'gallery-3000',
			esc_html__( 'Gallery', 'gallery-3000' ),
			array( $this, 'meta_box' ),
			get_post_types_by_support( 'gallery-3000' ),
			'normal',
			'high'
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post WP_Post object for the post we're editing.
	 */
	public function meta_box( $post ) {

		wp_nonce_field( basename( __FILE__ ), 'gallery_3000_nonce' );

		require( GALLERY_3000_PLUGIN_DIR . 'templates/tmpl-meta-box.php' );
	}

	/**
	 * Save gallery items when saving a post.
	 *
	 * Note: Gallery items are saved as a list of post IDs.
	 *       Downside is that it is not possible to query the database for a specific image to post gallery relation.
	 *       This would be beneficial for instance to be able to remove an image from the gallery when an attachment is deleted.
	 *
	 * @since 1.0.0
	 * @param int     $post_id ID of the post.
	 * @param WP_Post $post    WP_Post object of the post.
	 */
	public function save_post( $post_id, $post ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! post_type_supports( $post->post_type, 'gallery-3000' ) ) {
			return;
		}

		if (
			! isset( $_POST['gallery_3000_nonce'] ) // Input var okay.
			||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gallery_3000_nonce'] ) ), basename( __FILE__ ) ) ) { // Input var okay.
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( empty( $_POST['gallery_3000_items'] ) ) { // Input var okay.
			delete_post_meta( $post_id, '_gallery_3000' );
		} else {
			$items = array_map( 'intval', wp_unslash( (array) $_POST['gallery_3000_items'] ) ); // Input var okay.
			update_post_meta( $post_id, '_gallery_3000', $items );
		}
	}

	/**
	 * Enqueue media scripts.
	 *
	 * Enqueueing them in the meta box itself seems to muck with the default editor media view.
	 * Let's just go ahead and enqueue them really super late.
	 *
	 * @since 1.0.0
	 */
	function enqueue_media() {

		global $post;

		if ( ! post_type_supports( $post->post_type, 'gallery-3000' ) ) {
			return;
		}

		wp_enqueue_media();
	}
}
