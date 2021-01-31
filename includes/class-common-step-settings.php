<?php
/**
 * Gravity Flow Form Connector Common Step Settings Functions
 *
 * @since       1.7.5
 * @copyright   Copyright (c) 2015-2020, Steven Henty S.L.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     GravityFlow
 */

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

/**
 * Class Gravity_Flow_Form_Connector_Common_Step_Settings
 *
 * @since 1.7.5
 */
class Gravity_Flow_Form_Connector_Common_Step_Settings {

	/**
	 * The current step
	 *
	 * @since 1.7.5
	 *
	 * @var Gravity_Flow_Step_New_Entry
	 */
	private $_step;

	/**
	 * Gravity_Flow_Form_Connector_Common_Step_Settings constructor.
	 *
	 * @since 1.7.5
	 */
	public function __construct( $step ) {
		$this->_step = $step;
	}

	/**
	 * Returns the common server fields.
	 *
	 * @since 1.7.5
	 *
	 * @return array[]
	 */
	public function get_server_fields() {
		$is_api_v2 = $this->_step->is_api_v2();

		return array(
			array(
				'name'          => 'server_type',
				'label'         => esc_html__( 'Site', 'gravityflowformconnector' ),
				'type'          => 'radio',
				'default_value' => 'local',
				'horizontal'    => true,
				'onchange'      => 'jQuery(this).closest("form").submit();',
				'choices'       => array(
					array( 'label' => esc_html__( 'This site', 'gravityflowformconnector' ), 'value' => 'local' ),
					array(
						'label' => esc_html__( 'A different site', 'gravityflowformconnector' ),
						'value' => 'remote',
					),
				),
			),
			array(
				'name'          => 'api_version',
				'label'         => esc_html__( 'REST API', 'gravityflowformconnector' ),
				'type'          => 'radio',
				'default_value' => '1',
				'horizontal'    => true,
				'onchange'      => 'jQuery(this).closest("form").submit();',
				'choices'       => array(
					array(
						'label' => esc_html__( 'Version 1', 'gravityflowformconnector' ),
						'value' => '1',
					),
					array(
						'label' => esc_html__( 'Version 2', 'gravityflowformconnector' ),
						'value' => '2',
					),
				),
				'dependency'    => array(
					'field'  => 'server_type',
					'values' => array( 'remote' ),
				),
			),
			array(
				'name'       => 'remote_site_url',
				'label'      => esc_html__( 'Site Url', 'gravityflowformconnector' ),
				'type'       => 'text',
				'dependency' => array(
					'field'  => 'server_type',
					'values' => array( 'remote' ),
				),
			),
			array(
				'name'       => 'remote_public_key',
				'label'      => $is_api_v2 ? esc_html__( 'Consumer Key', 'gravityflowformconnector' ) : esc_html__( 'Public Key', 'gravityflowformconnector' ),
				'type'       => 'text',
				'dependency' => array(
					'field'  => 'server_type',
					'values' => array( 'remote' ),
				),
			),
			array(
				'name'       => 'remote_private_key',
				'label'      => $is_api_v2 ? esc_html__( 'Secret Key', 'gravityflowformconnector' ) : esc_html__( 'Private Key', 'gravityflowformconnector' ),
				'type'       => 'text',
				'dependency' => array(
					'field'  => 'server_type',
					'values' => array( 'remote' ),
				),
			),
		);
	}

	/**
	 * Returns the entry lookup field.
	 *
	 * @since 1.7.5
	 *
	 * @param array|string $dependency The field dependency.
	 *
	 * @return array
	 */
	public function get_lookup_method_field( $dependency ) {
		return array(
			'name'          => 'lookup_method',
			'label'         => esc_html__( 'Entry Lookup', 'gravityflowformconnector' ),
			'type'          => 'radio',
			'default_value' => 'select_entry_id_field',
			'horizontal'    => true,
			'onchange'      => 'jQuery(this).closest("form").submit();',
			'choices'       => array(
				array(
					'label' => esc_html__( 'Conditional Logic', 'gravityflowformconnector' ),
					'value' => 'filter',
				),
				array(
					'label' => esc_html__( 'Select a field containing the source entry ID.', 'gravityflowformconnector' ),
					'value' => 'select_entry_id_field',
				),
			),
			'dependency'    => $this->fields_dependency( $dependency ),
		);
	}

	/**
	 * Returns the Lookup Conditional Logic field.
	 *
	 * @since 1.7.5
	 *
	 * @param int|string   $form_id    The ID of the selected form.
	 * @param array|string $dependency The field dependency.
	 *
	 * @return array
	 */
	public function get_entry_filter_field( $form_id, $dependency = array() ) {
		$is_remote = $this->_step->get_setting( 'server_type' ) === 'remote';

		$dependency['fields'][] = array(
			'field'  => 'lookup_method',
			'values' => array( 'filter' ),
		);
		$dependency             = $this->fields_dependency( $dependency );

		if ( $is_remote ) {
			if ( empty( $form_id ) || ! $this->_step->is_api_v2() ) {
				return $this->get_unsupported_entry_filter_field( $dependency );
			}

			$filters = $this->get_remote_field_filters( $form_id );
			if ( empty( $filters ) ) {
				return $this->get_unsupported_entry_filter_field( $dependency );
			}
		}

		$field = array(
			'name'                 => 'entry_filter',
			'show_sorting_options' => true,
			'label'                => esc_html__( 'Lookup Conditional Logic', 'gravityflowformconnector' ),
			'type'                 => 'entry_filter',
			'filter_text'          => esc_html__( 'Look up the first entry matching {0} of the following criteria:', 'gravityflowformconnector' ),
			'dependency'           => $dependency,
		);

		if ( $is_remote ) {
			$field['filter_settings'] = $filters;
		} else {
			$field['form_id'] = $form_id;
		}

		return $field;
	}

	/**
	 * Returns the Lookup Conditional Logic field when not supported.
	 *
	 * @since 1.7.5
	 *
	 * @param array|string $dependency The field dependency.
	 *
	 * @return array
	 */
	private function get_unsupported_entry_filter_field( $dependency ) {
		$html = sprintf( '<div class="delete-alert alert_yellow"><i class="fa fa-exclamation-triangle gf_invalid"></i> %s</div>', esc_html__( 'To use this setting with a remote site you must select REST API Version 2, the remote site must be running Gravity Forms 2.4.22 or greater, and this site must be running Gravity Flow 2.7.1 or greater.', 'gravityflowformconnector' ) );

		// Use a hidden input to retain existing value.
		$html .= gravity_flow()->settings_hidden( array(
			'name' => 'entry_filter',
			'type' => 'hidden',
		), false );

		return array(
			'name'       => 'entry_filter',
			'label'      => esc_html__( 'Lookup Conditional Logic', 'gravityflowformconnector' ),
			'type'       => 'html',
			'dependency' => $dependency,
			'html'       => $html,
		);
	}

	/**
	 * Returns the field filter settings for a remote form.
	 *
	 * @since 1.7.5
	 *
	 * @param int|string $form_id The ID of the selected form.
	 *
	 * @return false|array
	 */
	private function get_remote_field_filters( $form_id ) {
		return $this->_step->remote_request_v2( "forms/{$form_id}/field-filters", 'GET', null, array( '_admin_labels' => 1 ) );
	}

	/**
	 * Returns a GF 2.5 settings compatible dependency array or evaluates the dependency to return one of the WP boolean callbacks for older versions.
	 *
	 * @since 1.7.5
	 *
	 * @param array $dependency The field dependency.
	 *
	 * @return array|string
	 */
	public function fields_dependency( $dependency ) {
		if ( ! gravity_flow()->is_gravityforms_supported( '2.5-beta-1' ) ) {
			foreach ( $dependency['fields'] as $field ) {
				if ( ! gravity_flow()->setting_dependency_met( $field ) ) {
					return '__return_false';
				}
			}

			return '__return_true';
		}

		return $dependency;
	}

}
