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
	private $dir;
	private $assets_dir;
	private $assets_url;
	private $token;
	public $version;
	private $file;
    
	/**
	 * Constructor function.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function __construct( $file ) {
		$this->dir = dirname( $file );
		$this->file = $file;
		$this->assets_dir = trailingslashit( dirname( $this->dir ) ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', dirname( $file ) ) ) );
		$this->token = 'testimonial';
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_submission_form_styles' ) );
		
		add_action( 'woothemes_testimonials_submission_form', array( $this, 'submission_form_fields' ) );
		
		add_action( 'init', array( $this, 'process_submission_form' ) );

		
		add_shortcode( 'woothemes_testimonials_form', array( $this, 'submission_form' ) );
			
	} // End __construct()

	/**
	 * Enqueue testimonials submission form CSS.
	 *
	 * @access public
	 * @since   1.6.0
	 * @return   void
	 */
	public function enqueue_submission_form_styles () {
		wp_register_style( 'woothemes-testimonials-submission-form', $this->assets_url . '/css/form.css');
		wp_enqueue_style( 'woothemes-testimonials-submission-form' );
	} // End enqueue_submission_form_styles()
	
	/**
	 * Adds a testimonials submission form html
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function submission_form () {
		
		$html = '';
	
		$html .= '<div class="col2-set" id="testimonials_submission">';
		$html .= '<div class="col-1">';		
		$html .= '<h2>' . __( 'Add a testimonial', 'woothemes-testimonials' ) . '</h2>';
		$html .= '<form method="post" class="testimonials_submission">';
		
		$fields = $this->submission_form_fields();

		foreach ( $fields as $field_name => $field_params ) {
			
			if( $field_params['required'] == true ) {
				$required = '<span class="required">*</span>';
			} else {
				$required = '';
			}
					
			$html .= '<p class="form-row form-row-wide">';
			
				if( $field_params['type'] == 'text' ) {
				
					$html .= '<label for="' . $field_name . '">' . $field_params['label'] . $required . '</label>';
					$html .= '<input type="text" class="input-text" name="' . $field_name . '" id="' . $field_name . '" />';
					
				} elseif ( $field_params['type'] == 'textarea' ) {
				
					$html .= '<label for="' . $field_name . '">' . $field_params['label'] . $required . '</label>';
					$html .= '<textarea class="input-textarea" name="' . $field_name . '" rows="10" cols="40" id="' . $field_name . '"></textarea>';
					
			    } elseif ( $field_params['type'] == 'email' ) {
				    
				    $html .= '<label for="' . $field_name . '">' . $field_params['label'] . $required . '</label>';
					$html .= '<input type="email" class="input-email" name="' . $field_name . '" id="' . $field_name . '" />';
			    
			    } elseif ( $field_params['type'] == 'byline' ) {
			    
				    $html .= '<label for="' . $field_name . '">' . $field_params['label'] . $required . '</label>';
					$html .= '<input type="text" class="input-byline" name="' . $field_name . '" id="' . $field_name . '" />';
			    
			    } elseif ( $field_params['type'] == 'website_url' ) {
			    
				    $html .= '<label for="' . $field_name . '">' . $field_params['label'] . $required . '</label>';
				    $html .= '<input type="url" class="input-website-url" name="' . $field_name . '" id="' . $field_name . '" />';
				    
			    } elseif ( $field_params['type'] == 'checking' ) {
			    
				    $html .= '<input type="text" class="input-checking" name="' . $field_name . '" id="' . $field_name . '" />';
				    
			    } elseif ( $field_params['type'] == 'submit' ) {
			    
				    $html .= '<input type="submit" class="button" name="' . $field_name . '" id="' . $field_name . '" value="' . $field_params['label'] . '" />';

			    } elseif ( $field_params['type'] == 'hidden' ) {
			    
				    $html .= '<input type="hidden" name="' . $field_name . '" id="' . $field_name . '" value="' . $field_params['value'] . '" />';

			    }

			$html .= '</p>';
		}
		
		$html .= '</form>';
		$html .= '</div>';
		$html .= '</div>';
		
	    return $html;
	    
	} // End submission_form()

	
	private function submission_form_fields () {
		
		$fields = array();
		
		$fields['woothemes_testimonials_name'] = array(
							  'label' => __( 'Name', 'woothemes-testimonials' ),
							  'type' => 'text',
							  'required' => true
						  );
						  
		$fields['woothemes_testimonials_content'] = array(
							  'label' => __( 'Testimonial', 'woothemes-testimonials' ),
							  'type' => 'textarea',
							  'required' => true
						  );
						  
		$fields['woothemes_testimonials_email'] = array(
							  'label' => __( 'E-mail address ( Gravatar )', 'woothemes-testimonials' ),
							  'type' => 'email',
							  'required' => true
						  );
						  
		$fields['woothemes_testimonials_byline'] = array(
							  'label' => __( 'Byline', 'woothemes-testimonials' ),
							  'type' => 'text',
							  'required' => false
						  );
						  
		$fields['woothemes_testimonials_website_url'] = array(
							  'label' => __( 'Website', 'woothemes-testimonials' ),
							  'type' => 'website_url',
							  'required' => false
						  );
						  
		$fields['woothemes_testimonials_checking'] = array(
							  'label' => __( 'If you want to submit this form, do not enter anything in this field', 'woothemes-testimonials' ),
							  'type' => 'checking',
							  'required' => false
						  );
						  
		$fields['woothemes_testimonials_submit'] = array(
							  'label' => __( 'Submit for review', 'woothemes-testimonials' ),
							  'type' => 'submit',
							  'required' => false
						  );
						  
		$fields['woothemes_testimonials_submission'] = array(
							  'type' => 'hidden',
							  'required' => false,
							  'value' => '1'
							  
						  );
						  
		return apply_filters( 'woothemes_testimonials_submission_form_fields', $fields );		
	
	} // End submission_form_fields()
	
	
	function process_submission_form () {
	
		if ( isset( $_POST['woothemes_testimonials_submission'] ) && '1' == $_POST['woothemes_testimonials_submission'] ) {
				
			$name          = sanitize_user( $_POST['woothemes_testimonials_name'] );
			$content       = sanitize_text_field( $_POST['woothemes_testimonials_content'] );
			$email         = sanitize_email( $_POST['woothemes_testimonials_email'] );
			$byline        = sanitize_text_field( $_POST['woothemes_testimonials_byline'] );
			$website_url   = esc_url_raw( $_POST['woothemes_testimonials_website_url'] );
			$honeypot      = $_POST['woothemes_testimonials_checking'];
			
			// Only proceed with saving the Testimonial if the honeypot is empty
			if ( $honeypot == '' ) {
			}

		}
	
	} // End process_submission_form()

} // End Class