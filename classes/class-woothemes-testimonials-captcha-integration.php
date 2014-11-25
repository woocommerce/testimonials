<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * WooThemes Testimonials Captcha Integration Class
 *
 * All functionality pertaining to the Testimonials Captcha Integration.
 *
 * @package WordPress
 * @subpackage Woothemes_Testimonials
 * @category Plugin
 * @author Danny
 * @since 1.6.0
 */
class Woothemes_Testimonials_Captcha_Integration {
	public $plugin_enabled;

	/**
	 * Constructor function.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function __construct( $file ) {
		add_action( 'woothemes_testimonials_process_shortcode_params', array( $this, 'process_captcha_option' ) );

		// Check if the plugin is active and if the "captcha" param was set in the shortcode.
		$this->captcha_check();

		if( $this->plugin_enabled == true ) {
			add_filter( 'woothemes_testimonials_submission_form_fields', array( $this, 'register_captcha_fields' ) );
			add_action( 'woothemes_testimonials_before_submit_field', array( $this, 'output_external_captcha_field' ) );
			add_filter( 'woothemes_testimonials_validate_hooked_data', array( $this, 'validate_captcha_field' ), 10, 3 );
		}
	}

	/**
	 * Check if the plugin is active and if the "captcha" param was set in the shortcode.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function captcha_check ( ) {
			if( get_option( '_woothemes_testimonials_captcha' ) != '' && get_option( '_woothemes_testimonials_captcha' ) == 'true' ) {
			// Captcha plugin integration.
			if( function_exists( 'cptch_display_captcha_custom' ) && function_exists( 'cptch_check_custom_form' ) ) {
				$this->plugin_enabled = true;
			} else {
				$this->plugin_enabled = false;
			}
		}
	} // End captcha_check()

	/**
	 * Handle the "captcha" parameter in the form shortcode.
	 *
	 * @param array $atts [woothemes_testimonials_form] shortcode parameters.
	 * @access public
	 * @since 1.6.0
	 * @return void
	 */
	public function process_captcha_option ( $atts ) {
		if( isset( $atts['captcha'] ) && $atts['captcha'] != '' ) {

			if ( $atts['captcha'] == true && $this->plugin_enabled == false ) {
				// In the case the "captcha" param is set in the shortcode, but the plugin is inactive print notice.
				add_filter( 'woothemes_testimonials_add_notice', array( $this, 'generate_plugin_inactive_notice' ) );
			}

			$captcha = get_option( '_woothemes_testimonials_captcha' );

			if( $captcha == '' ) {
			    add_option( '_woothemes_testimonials_captcha', $atts['captcha'] );

		    } elseif ( $captcha != $atts['captcha'] ) {
			    update_option( '_woothemes_testimonials_captcha', $atts['captcha'] );
		    }

		} else {
			delete_option( '_woothemes_testimonials_captcha' );
		}
	} // End process_captcha_option()

	/**
	 * Generate a notice in case "captcha" param is set in the shortcode, but the plugin is inactive.
	 *
	 * @param array $response_items
	 * @access public
	 * @since 1.6.0
	 * @return array
	 */
	public function generate_plugin_inactive_notice ( $response_items ) {
		$response_items[] = array(
								'message' => __( 'In order to use the captcha verification you will have to install & activate the <a href="https://wordpress.org/plugins/captcha/">Captcha</a> plugin.', 'woothemes-testimonials' ),
								'type' => 'error'
							);
		return $response_items;
	} // End generate_plugin_inactive_notice()

	/**
	 * Register captcha form fields.
	 *
	 * @param array $fields Form fields.
	 * @access public
	 * @since 1.6.0
	 * @return array
	 */
	public function register_captcha_fields ( $fields ) {
		$fields['cntctfrm_contact_action'] = array(
								  'type' => 'hidden',
								  'required' => false,
								  'value' => 'true'
							  );

		$fields['cptch_number'] = array(
								  'label' => __( 'Captcha', 'woothemes-testimonials' ),
								  'type' => 'external',
								  'required' => true
							  );
		return $fields;
	} // End register_captcha_fields()

	/**
	 * Register captcha form fields.
	 *
	 * @access public
	 * @since 1.6.0
	 * @return string
	 */
	public function output_external_captcha_field ( ) {
		$html = '<p class="form-row form-row-wide cntctfrm_contact_action">';
		$html .= cptch_display_captcha_custom();
		$html .=  __( '<span class="required">*</span>', 'woothemes-testimonials' );
		$html .= '</p>';

		echo $html;
	} // End output_external_captcha_field()

	/**
	 * Validate the captcha input.
	 *
	 * @param array $fields Form fields.
	 * @param array $data $_POST hooked testimonial data.
	 * @param array $errors Field errors.
	 * @access public
	 * @since 1.6.0
	 * @return array
	 */
	public function validate_captcha_field ( $fields, $data, $errors ) {
		// Makes sure the email has been submitted.
		if ( isset ( $data[ 'cptch_number' ] ) && $data[ 'cptch_number' ] != '' ) {

			if( cptch_check_custom_form() == true ) {
				// valid
			} else {
				$errors['invalid_content']['cptch_number'] = true;
			}

		} else {
			$errors['missing_content']['cptch_number'] = true;
			$errors['missing_required']['cptch_number'] = true;
		}

		return $errors;
	} // End validate_captcha_field()

}