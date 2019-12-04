<?php
/**
 * Plugin Name: Manual Reviews for WooCommerce
 * Description: A simple and easy way to manually add verified product reviews from your WordPress dashboard.
 * Version: 1.0.1
 * Author: mesbahamine
 * Author URI: http://royalz.store
 * Requires at least: 4.7
 * Tested up to: 4.9.5
 * Text Domain: mrfw
*/

if ( ! class_exists( 'MRFW' ) ) :
	class MRFW {


		/**
		 * Construct
		 *
		 * @since 1.0
		*/
		function __construct() {

			add_action( 'init', 											array( $this, 'textdomain' ) 		);
			add_action( 'admin_enqueue_scripts', 							array( $this, 'admin_scripts' ) 	);
			add_action( 'add_meta_boxes', 									array( $this, 'add_metabox' ) 		);
			add_action( 'wp_ajax_woocommerce_add_manual_review', 			array( $this, 'insert_comment' ) 	);
			add_action( 'wp_ajax_nopriv_woocommerce_add_manual_review', 	array( $this, 'insert_comment' ) 	);

		}



		/**
		 * Load the plugin's text domain
		 */
		function textdomain() {

			load_plugin_textdomain( 'mrfw', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		}



		/**
		 * Enqueue scripts.
		 */
		public function admin_scripts() {

			global $post;

			wp_register_script( 'mrfw-product-metabox', plugins_url( '/assets/js/product-metabox.js', __FILE__ ), array( 'jquery' ), '1.0' );

			wp_enqueue_script( 'mrfw-product-metabox' );

			wp_localize_script( 
				'mrfw-product-metabox', 
				'mrfw_admin_meta_box', 
				array( 
					'ajax_url' => admin_url( 'admin-ajax.php' ), 
					'add_manual_review_nonce' => wp_create_nonce( 'add-manual-review' ), 
					'post_id' => isset( $post->ID ) ? $post->ID : '' ) );

		}


		/**
		 * Add metabox
		 */
		function add_metabox() {
			
			add_meta_box( 'mrfw', __( 'Add a review', 'mrfw' ), array( $this, 'render_metabox' ), 'product' );

		}


		/**
		 * Render metabox content
		 */
		function render_metabox( $post ) {

	        ?>

	        <div class="woocommerce_options_panel" id="mrfw_form">

				<p class="form-field">
					<label for="mrfw_form_name"><?php esc_html_e('Name', 'mrfw'); ?></label>
					<input type="text" class="short" name="mrfw_form_name" id="mrfw_form_name" placeholder="<?php esc_html_e('Enter reviewer name', 'mrfw'); ?>">
				</p>

				<p class="form-field">
					<label for="mrfw_form_content"><?php esc_html_e('Content', 'mrfw'); ?></label>
					<textarea class="short" name="mrfw_form_content" id="mrfw_form_content" placeholder="<?php esc_html_e('Enter review content.', 'mrfw'); ?>" rows="2" cols="20"></textarea>
				</p>

				<p class="form-field">
					<label for="mrfw_form_rating"><?php esc_html_e('Rating', 'mrfw'); ?></label>
					<select class="select short" id="mrfw_form_rating" name="mrfw_form_rating">
						<option value="1"><?php esc_html_e('1 Star', 'mrfw'); ?></option>
						<option value="2"><?php esc_html_e('2 Stars', 'mrfw'); ?></option>
						<option value="3"><?php esc_html_e('3 Stars', 'mrfw'); ?></option>
						<option value="4"><?php esc_html_e('4 Stars', 'mrfw'); ?></option>
						<option value="5" selected><?php esc_html_e('5 Stars', 'mrfw'); ?></option>
					</select>
				</p>

				<p class="form-field">
					<label for="mrfw_form_approved"><?php esc_html_e('Approve', 'mrfw'); ?></label>
					<input type="checkbox" class="checkbox" name="mrfw_form_approved" id="mrfw_form_approved" checked>
					<span class="description"><?php esc_html_e('Uncheck this if you want to approve and publish this review later.', 'mrfw'); ?></span>
				</p>

				<p class="form-field">
					<label for="mrfw_form_verified"><?php esc_html_e('Verified?', 'mrfw'); ?></label>
					<input type="checkbox" class="checkbox" name="mrfw_form_verified" id="mrfw_form_verified" checked>
					<span class="description"><?php esc_html_e('This will mark this review as verfied (aka left by a verified owner).', 'mrfw'); ?></span>
				</p>

				<p class="form-field">
					<a href="#" class="button insert" id="mrfw_form_add"><?php esc_html_e('Send review', 'mrfw'); ?></a>
				</p>

			</div>

	        <?php

		}


		/**
		 * Insert comment
		 */
		function insert_comment() {

			check_ajax_referer( 'add-manual-review', 'security' );

			$post_id 		= ( isset( $_POST['post_id'] ) ) 		? sanitize_text_field( $_POST['post_id'] ) 		: '';
			$name 			= ( isset( $_POST['name'] ) ) 			? sanitize_text_field( $_POST['name'] ) 		: '';
			$content 		= ( isset( $_POST['content'] ) ) 		? sanitize_text_field( $_POST['content'] )		: '';
			$approved 		= ( isset( $_POST['approved'] ) ) 		? absint( $_POST['approved'] )					: 1;
			$verified 		= ( isset( $_POST['verified'] ) ) 		? absint( $_POST['verified'] )					: 1;
			$rating 		= ( isset( $_POST['rating'] ) ) 		? absint( $_POST['rating'] ) 					: 5;

			if ( empty($post_id) || empty($name) || empty($content) ) {
				wp_die( -1 );
			} 

			$commentdata = array(
				'comment_post_ID' 	=> $post_id,
				'comment_author' 	=> $name,
				'comment_content' 	=> $content,
				'comment_parent' 	=> 0,
			);

			$comment_id = wp_new_comment( $commentdata );

			if ( $comment_id == '-1' ) {
				wp_die( -1 );
			}

			if ( $approved ) {
				wp_set_comment_status( $comment_id, 'approve' );
			}

			add_comment_meta( $comment_id, 'rating', (int) esc_attr( $rating ), true );
			delete_comment_meta( $comment_id, 'verified' );
			add_comment_meta( $comment_id, 'verified', (int) esc_attr( $verified ), true );

			wp_die();

		}


	}
endif;
$GLOBALS['mrfw'] = new MRFW(); ?>
