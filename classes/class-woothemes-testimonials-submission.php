<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * WooThemes Testimonials Submission Class
 *
 * All functionality pertaining to the Testimonials submission form.
 *
 * @package WordPress
 * @subpackage Woothemes_Testimonials
 * @category Plugin
 * @author Danny
 * @since 1.6.0
 */
class Woothemes_Testimonials_Submission {
	private $assets_url;
	private $file;
	public $fields;
	public $errors;
	public $response;
	public $response_items;
	public $moderator_emails;
	public $testimonial_id;

	/**
	 * Constructor function.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function __construct( $file ) {

		$this->file = $file;
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', dirname( $file ) ) ) );

		// Load form fields
		$this->submission_form_fields();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_submission_form_styles' ) );

		add_action( 'init', array( $this, 'process_submission_form' ) );
		add_action( 'woothemes_testimonials_before_form', array( $this, 'print_response' ) );
		add_action( 'woothemes_testimonials_after_form_fields', array( $this, 'generate_nonce_field' ) );

		add_shortcode( 'woothemes_testimonials_form', array( $this, 'submission_form' ) );

	} // End __construct()

	/**
	 * Enqueue testimonials submission form CSS.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function enqueue_submission_form_styles () {
		wp_register_style( 'woothemes-testimonials-submission-form', $this->assets_url . '/css/form.css');
		wp_enqueue_style( 'woothemes-testimonials-submission-form' );
	} // End enqueue_submission_form_styles()

	/**
	 * Generate the testimonials submission form html.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return string submission form html
	 */
	public function submission_form ( $atts ) {

		// If the 'notify' parameter is passed, the moderator(s) will receive email notifications.
		if( $atts['notify'] != '' ) {
			$this->moderator_emails = explode( ',', $atts['notify'] );
		}

		$html = '';

		ob_start();
		do_action( 'woothemes_testimonials_before_form' );
        $html .= ob_get_contents();
        ob_end_clean();

		$html .= '<div id="testimonials-submission">';
		$html .= '<h2>' . __( 'Add a testimonial', 'woothemes-testimonials' ) . '</h2>';
		$html .= '<form method="post" class="testimonials-submission">';

		ob_start();
		do_action( 'woothemes_testimonials_before_form_fields' );
        $html .= ob_get_contents();
        ob_end_clean();

		foreach ( $this->fields as $field_name => $field_params ) {

			if ( $field_params['required'] == true ) {
				$required = __( '<span class="required">*</span>', 'woothemes-testimonials' );
			} else {
				$required = '';
			}

			$html .= '<p class="form-row form-row-wide ' . $field_name . '">';

				if ( $field_params['type'] == 'text' ) {

					$html .= '<label for="' . $field_name . '">' . $field_params['label'] . $required . '</label>';
					$html .= '<input type="text" class="input-text" name="' . $field_name . '" id="' . $field_name . '" />';

				} elseif ( $field_params['type'] == 'textarea' ) {

					$html .= '<label for="' . $field_name . '">' . $field_params['label'] . $required . '</label>';
					$html .= '<textarea class="input-textarea" name="' . $field_name . '" rows="10" cols="40" id="' . $field_name . '"></textarea>';

			    } elseif ( $field_params['type'] == 'email' ) {

				    $html .= '<label for="' . $field_name . '">' . $field_params['label'] . $required . '</label>';
					$html .= '<input type="text" class="input-email" name="' . $field_name . '" id="' . $field_name . '" />';

			    } elseif ( $field_params['type'] == 'byline' ) {

				    $html .= '<label for="' . $field_name . '">' . $field_params['label'] . $required . '</label>';
					$html .= '<input type="text" class="input-byline" name="' . $field_name . '" id="' . $field_name . '" />';

			    } elseif ( $field_params['type'] == 'website_url' ) {

				    $html .= '<label for="' . $field_name . '">' . $field_params['label'] . $required . '</label>';
				    $html .= '<input type="text" class="input-website-url" name="' . $field_name . '" id="' . $field_name . '" />';

			    } elseif ( $field_params['type'] == 'checking' ) {

				    $html .= '<label class="input-checking" for="' . $field_name . '">' . $field_params['label'] . $required . '</label>';
				    $html .= '<input type="text" class="input-checking" name="' . $field_name . '" id="' . $field_name . '" />';

			    } elseif ( $field_params['type'] == 'submit' ) {

					ob_start();
					do_action( 'woothemes_testimonials_before_submit_field' );
					$html .= ob_get_contents();
					ob_end_clean();

				    $html .= '<input type="submit" class="button" name="' . $field_name . '" id="' . $field_name . '" value="' . $field_params['label'] . '" />';

			    } elseif ( $field_params['type'] == 'hidden' ) {

				    $html .= '<input type="hidden" name="' . $field_name . '" id="' . $field_name . '" value="' . $field_params['value'] . '" />';

			    }

			$html .= '</p>';
		}

		ob_start();
		do_action( 'woothemes_testimonials_after_form_fields' );
        $html .= ob_get_contents();
        ob_end_clean();

		$html .= '</form>';
		$html .= '</div>';

		ob_start();
		do_action( 'woothemes_testimonials_after_form' );
        $html .= ob_get_contents();
        ob_end_clean();

	    return $html;

	} // End submission_form()

	/**
	 * Register form fields.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function submission_form_fields () {

		$this->fields = array();

		$this->fields['woothemes_testimonials_name'] = array(
							  'label' => __( 'Name', 'woothemes-testimonials' ),
							  'type' => 'text',
							  'required' => true
						  );

		$this->fields['woothemes_testimonials_content'] = array(
							  'label' => __( 'Testimonial', 'woothemes-testimonials' ),
							  'type' => 'textarea',
							  'required' => true
						  );

		$this->fields['woothemes_testimonials_email'] = array(
							  'label' => __( 'E-mail address ( Gravatar )', 'woothemes-testimonials' ),
							  'type' => 'email',
							  'required' => true
						  );

		$this->fields['woothemes_testimonials_byline'] = array(
							  'label' => __( 'Byline', 'woothemes-testimonials' ),
							  'type' => 'text',
							  'required' => false
						  );

		$this->fields['woothemes_testimonials_website_url'] = array(
							  'label' => __( 'Website', 'woothemes-testimonials' ),
							  'type' => 'website_url',
							  'required' => false
						  );

		$this->fields['woothemes_testimonials_checking'] = array(
							  'label' => __( 'Checking <small>( If you want to submit this form, do not enter anything in this field. )</small>', 'woothemes-testimonials' ),
							  'type' => 'checking',
							  'required' => false
						  );

		$this->fields['woothemes_testimonials_submit'] = array(
							  'label' => __( 'Submit for review', 'woothemes-testimonials' ),
							  'type' => 'submit',
							  'required' => false
						  );

		$this->fields['woothemes_testimonials_submission'] = array(
							  'type' => 'hidden',
							  'required' => false,
							  'value' => '1'

						  );

		apply_filters( 'woothemes_testimonials_submission_form_fields', $this->fields );

	} // End submission_form_fields()

	/**
	 * Generate a nonce for the submission form.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function generate_nonce_field () {
		wp_nonce_field( 'woothemes_testimonials_item_nonce', 'woothemes_testimonials_item_submit' );
	} // End generate_nonce_field()

	/**
	 * Process the submitted testimonial data.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function process_submission_form () {

		if ( isset( $_POST['woothemes_testimonials_submission'] ) && '1' == $_POST['woothemes_testimonials_submission'] ) {

			$testimonial_data_raw['woothemes_testimonials_name'] = trim( $_POST['woothemes_testimonials_name'] );
			$testimonial_data_raw['woothemes_testimonials_content'] = trim( $_POST['woothemes_testimonials_content'] );
			$testimonial_data_raw['woothemes_testimonials_email'] = trim( $_POST['woothemes_testimonials_email'] );
			$testimonial_data_raw['woothemes_testimonials_byline'] = trim( $_POST['woothemes_testimonials_byline'] );
			$testimonial_data_raw['woothemes_testimonials_website_url'] = trim( $_POST['woothemes_testimonials_website_url'] );
			$testimonial_data_raw['woothemes_testimonials_checking'] = trim( $_POST['woothemes_testimonials_checking'] );

			$testimonial_data['woothemes_testimonials_name'] = sanitize_user( $_POST['woothemes_testimonials_name'] );
			$testimonial_data['woothemes_testimonials_content'] = sanitize_text_field( $_POST['woothemes_testimonials_content'] );
			$testimonial_data['woothemes_testimonials_email'] = sanitize_email( $_POST['woothemes_testimonials_email'] );
			$testimonial_data['woothemes_testimonials_byline'] = sanitize_text_field( $_POST['woothemes_testimonials_byline'] );
			$testimonial_data['woothemes_testimonials_website_url'] = esc_url_raw( $_POST['woothemes_testimonials_website_url'] );

			// Only proceed with saving the Testimonial if the honeypot is empty.
			if ( $testimonial_data_raw['woothemes_testimonials_checking'] != '' ) {
				// Spam
				$this->generate_response( __( 'In order to submit a new testimonial, please leave the <strong>Checking</strong> field empty.', 'woothemes-testimonials' ), 'error' );

			} else {

				// Make sure the form came from the location on the current site and not somewhere else.
				if ( isset( $_POST['woothemes_testimonials_item_submit'] ) && wp_verify_nonce( $_POST['woothemes_testimonials_item_submit'], 'woothemes_testimonials_item_nonce' ) ) {

					// If the submitted data will pass the validation, let's go and add the testimonial.
					if ( $this->validate_testimonial_data( $testimonial_data, $testimonial_data_raw ) == true ) {
						if ( $this->add_testimonial( $testimonial_data ) ) {
							// Success
							$this->generate_response( __( 'Your testimonial has been submitted and is now awaiting moderation.', 'woothemes-testimonials' ), 'success' );
							if( $this->moderator_emails != '' ) {
								// Send an email notification to the moderator(s).
								$this->notify_moderators( $testimonial_data );
							}
						} else {
							// Failure
							$this->generate_response( __( 'Your testimonial data seems to be correct, but something went wrong. Unfortunately your testimonial could not be submitted.', 'woothemes-testimonials' ), 'error' );
						}
					} else {
						// Validation failed.
						$this->generate_error_notices();
						$this->generate_response( __( 'Unfortunately your testimonial could not be submitted.', 'woothemes-testimonials' ), 'error');
					}

				} else {
					// Someone is playing dirty.
					$this->generate_response( __( 'Cheatin&#8217; huh?', 'woothemes-testimonials' ), 'error');
				}

			}

		}
	} // End process_submission_form()

	/**
	 * Validate the submitted testimonial data.
	 *
	 * @param array $testimonial_data Sanitized $_POST testimonial data.
	 * @param array $testimonial_data_raw Unsanitized $_POST testimonial data.
	 * @access public
	 * @since 1.6.0
	 * @return bool
	 */
	public function validate_testimonial_data ( $testimonial_data, $testimonial_data_raw ) {

		// Loops though all form fields.
		foreach ( $this->fields as $field_name => $field_params ) {

				if ( isset ( $testimonial_data_raw[ $field_name ] ) && $testimonial_data_raw[ $field_name ] == '' && $field_name != 'woothemes_testimonials_checking' ) {
					$this->errors['missing_content'][ $field_name ] = true;
				}
		}

		$this->validate_required( $testimonial_data_raw);
		$this->validate_email( $testimonial_data_raw );
		$this->validate_url( $testimonial_data );

		// All clear! No errors.
		if ( !isset( $this->errors ) && $this->errors == '' ) {
			return true;
		} else {
			return false;
		}

	} // End validate_testimonial_data()

	/**
	 * Validate the required fields.
	 *
	 * @param array $testimonial_data_raw Unsanitized $_POST testimonial data.
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function validate_required ( $testimonial_data_raw ) {

		// Loops though all form fields.
		foreach ( $this->fields as $field_name => $field_params ) {

			if ( $field_params['required'] == true ) {

				// Check if the required fields are not empty.
				if ( $testimonial_data_raw[$field_name] == '' ) {
					$this->errors['missing_required'][ $field_name ] = true;
				}
			}

		}

	} // End validate_required()

	/**
	 * Validate the email field.
	 *
	 * @param array $testimonial_data_raw Unsanitized $_POST testimonial data.
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function validate_email ( $testimonial_data_raw ) {

			// Makes sure the email has been submitted.
			if ( !isset ( $this->errors['missing_content'][ 'woothemes_testimonials_email' ] ) ) {

				if ( is_email( $testimonial_data_raw['woothemes_testimonials_email'] ) ) {
					return true;
				} else {
					$this->errors['invalid_content']['woothemes_testimonials_email'] = true;
				}

			}

	} // End validate_email()

	/**
	 * Validate the url field.
	 *
	 * @param array $testimonial_data Sanitized $_POST testimonial data.
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function validate_url ( $testimonial_data ) {

		// Makes sure the URL has been submitted.
		if ( !isset ( $this->errors['missing_content'][ 'woothemes_testimonials_website_url' ] ) ) {

			if( filter_var( $testimonial_data['woothemes_testimonials_website_url'], FILTER_VALIDATE_URL) ){
				return true;
			} else {
				$this->errors['invalid_content']['woothemes_testimonials_website_url'] = true;
			}

		}

	} // End validate_url()

	/**
	 * Generate a response after a testimonial submission attempt.
	 *
	 * @param string $message
	 * @param string $type
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function generate_response ( $message = '', $type = '' ) {

		$type_class = '';

		if ( isset( $type ) ) {
			$type_class = $type;
		}

		$before = '<div class="woothemes-testimonials-notice ' . $type_class . '">';
		$after = '</div>';

		$items_html = '';

		if ( isset ( $this->response_items ) ) {
			$items_html .= '<ul>';
			foreach ( $this->response_items as $response_message ) {
				$items_html .= '<li>' . $response_message . '</li>';
			}
			$items_html .= '</ul>';
		}

		$this->response = $before . $message . $items_html . $after;

	} // End generate_response()

	/**
	 * Generate error notices.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function generate_error_notices () {

		// Missing required fields
		if( isset ( $this->errors['missing_required'] ) ) {

			$field_labels = array();
			$message_html_before = __( 'The following fields are required: ', 'woothemes-testimonials' );
			$message_html_after = '';

			foreach ( $this->errors['missing_required'] as $k => $v ) {
				$field_labels[] = $this->fields[ $k ]['label'];
			}

			$message_html = implode( ", ", $field_labels );

			$message = $message_html_before . $message_html . $message_html_after;

			$this->add_response_item( $message );
		}

		// Invalid fields
		if( isset ( $this->errors['invalid_content'] ) ) {

			$field_labels = array();
			$message_html_before = __( 'Please make sure, you submit correct data in the following fields: ', 'woothemes-testimonials' );
			$message_html_after = '';

			foreach ( $this->errors['invalid_content'] as $k => $v ) {
				$field_labels[] = $this->fields[ $k ]['label'];
			}

			$field_labels = implode( ", ", $field_labels );

			$message = $message_html_before . $field_labels . $message_html_after;

			$this->add_response_item( $message );

		}

	} // End generate_error_notices()

	/**
	 * Add a response item.
	 *
	 * @param string $message
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function add_response_item ( $message ) {

		$this->response_items[] = $message;

	} // End add_response_item()

	/**
	 * Print the response.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function print_response ( ) {
		echo apply_filters( 'woothemes_testimonials_form_response', $this->response );
	} // End print_response()

	/**
	 * Add the testimonial item.
	 *
	 * @param array $testimonial_data Sanitized $_POST testimonial data.
	 * @access public
	 * @since 1.6.0
	 * @return mixed The ID of the post if the testimonial is successfully added to the database. On failure, it returns 0 if $wp_error is set to false, or a WP_Error object if $wp_error is set to true.
	 */
	public function add_testimonial ( $testimonial_data ) {

		$testimonial_information = array(
						    'post_title' => $testimonial_data['woothemes_testimonials_name'],
						    'post_content' => $testimonial_data['woothemes_testimonials_content'],
						    'post_type' => 'testimonial',
						    'post_status' => 'draft'
						);

		$post_id = wp_insert_post( $testimonial_information );

		$this->testimonial_id = $post_id;

		update_post_meta( $post_id, '_gravatar_email', $testimonial_data['woothemes_testimonials_email'] );
		update_post_meta( $post_id, '_byline', $testimonial_data['woothemes_testimonials_byline'] );
		update_post_meta( $post_id, '_url', $testimonial_data['woothemes_testimonials_website_url'] );

		return $post_id;

	} // End add_testimonial()

	/**
	 * Send an email notification to the moderator(s).
	 *
	 * @param array $testimonial_data Sanitized $_POST testimonial data.
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function notify_moderators ( $testimonial_data ) {

		$author_name =  	$testimonial_data['woothemes_testimonials_name'];
		$content = 			$testimonial_data['woothemes_testimonials_content'];
		$author_email = 	$testimonial_data['woothemes_testimonials_email'];
		$author_url = 		$testimonial_data['woothemes_testimonials_website_url'];
		$author_byline = 	$testimonial_data['woothemes_testimonials_byline'];
		$blogname = 		wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );

		$message  = __( 'A new testimonial is waiting for your approval.', 'woothemes-testimonials' ) . "\r\n";
		$message .= sprintf( __( 'Author: %1$s <%2$s>', 'woothemes-testimonials' ), $author_name, $author_email ) . "\r\n";
		$message .= sprintf( __( 'E-mail: %s', 'woothemes-testimonials' ), $author_email ) . "\r\n";
		$message .= sprintf( __( 'URL: %s', 'woothemes-testimonials' ), $author_url ) . "\r\n";
		$message .= __( 'Testimonial: ', 'woothemes-testimonials' ) . "\r\n" . $content . "\r\n\r\n";

		$message .= sprintf( __( 'Moderate it: %s', 'woothemes-testimonials' ),  admin_url( 'post.php?action=edit&post=' . $this->testimonial_id ) ) . "\r\n";

		$subject = sprintf( __( '[%1$s] Please moderate a new testimonial from "%2$s"', 'woothemes-testimonials' ), $blogname, $author_name );

		$headers = 'Content-Type: text/html\r\n';

		// Filter the testimonial notification email content.
		$message = apply_filters( 'woothemes_testimonials_notification_content', $message );

		// Filter the testimonial notification email subject.
		$subject = apply_filters( 'woothemes_testimonials_notification_subject', $subject );

		// Filter the testimonial notification email headers.
		$headers = apply_filters( 'woothemes_testimonials_notification_headers', $headers );

		foreach ( $this->moderator_emails as $email ) {
			wp_mail( $email, wp_specialchars_decode( $subject ), $message, $headers );
		}

	} // End notify_moderators()

} // End Class