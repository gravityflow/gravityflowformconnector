<?php

/**
 * Gravity Flow Add Entry Step
 *
 *
 * @package     GravityFlow
 * @subpackage  Classes/Step
 * @copyright   Copyright (c) 2015-2018, Steven Henty S.L.
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 * @since       1.0
 */

if ( class_exists( 'Gravity_Flow_Step' ) ) {

	class Gravity_Flow_Step_New_Entry extends Gravity_Flow_Step {
		public $_step_type = 'new_entry';

		public function get_label() {
			return esc_html__( 'New Entry', 'gravityflowformconnector' );
		}

		public function get_settings() {

			$forms = $this->get_forms();
			$form_choices[] = array( 'label' => esc_html__( 'Select a Form', 'gravityflowformconnector' ), 'value' => '' );
			foreach ( $forms  as $form ) {
				$form_choices[] = array( 'label' => $form->title, 'value' => $form->id );
			}


			$settings = array(
				'title'  => esc_html__( 'New Entry', 'gravityflow' ),
				'fields' => array(
					array(
						'name' => 'server_type',
						'label' => esc_html__( 'Site', 'gravityflowformconnector' ),
						'type' => 'radio',
						'default_value' => 'local',
						'horizontal' => true,
						'onchange' => 'jQuery(this).closest("form").submit();',
						'choices' => array(
							array( 'label' => esc_html__( 'This site', 'gravityflowformconnector' ), 'value' => 'local' ),
							array( 'label' => esc_html__( 'A different site', 'gravityflowformconnector' ), 'value' => 'remote' ),
						),
					),
					array(
						'name' => 'remote_site_url',
						'label' => esc_html__( 'Site Url', 'gravityflowformconnector' ),
						'type' => 'text',
						'dependency' => array(
							'field'  => 'server_type',
							'values' => array( 'remote' ),
						),
					),
					array(
						'name' => 'remote_public_key',
						'label' => esc_html__( 'Public Key', 'gravityflowformconnector' ),
						'type' => 'text',
						'dependency' => array(
							'field'  => 'server_type',
							'values' => array( 'remote' ),
						),
					),
					array(
						'name' => 'remote_private_key',
						'label' => esc_html__( 'Private Key', 'gravityflowformconnector' ),
						'type' => 'text',
						'dependency' => array(
							'field'  => 'server_type',
							'values' => array( 'remote' ),
						),
					),
					array(
						'name' => 'target_form_id',
						'label' => esc_html__( 'Form', 'gravityflowformconnector' ),
						'type' => 'select',
						'onchange'    => "jQuery(this).closest('form').submit();",
						'choices' => $form_choices,
					),
				),
			);

			if ( version_compare( gravity_flow()->_version, '1.3.0.10', '>=' ) ) {
				// Use Generic Map setting to allow custom values.
				$mapping_field = array(
					'name' => 'mappings',
					'label' => esc_html__( 'Field Mapping', 'gravityflowformconnector' ),
					'type' => 'generic_map',
					//'callback'      => array( gravity_flow_form_connector(), 'generic_map' ),
					'enable_custom_key' => false,
					'enable_custom_value' => true,
					'key_field_title' => esc_html__( 'Field', 'gravityflowformconnector' ),
					'value_field_title' => esc_html__( 'Value', 'gravityflowformconnector' ),
					'value_choices' => $this->value_mappings(),
					'key_choices' => $this->field_mappings(),
					'tooltip'   => '<h6>' . esc_html__( 'Mapping', 'gravityflowformconnector' ) . '</h6>' . esc_html__( 'Map the fields of this form to the selected form. Values from this form will be saved in the entry in the selected form' , 'gravityflowformconnector' ),
					'dependency' => array(
						'field'  => 'target_form_id',
						'values' => array( '_notempty_' ),
					),
				);
			} else {
				$mapping_field = array(
					'name' => 'mappings',
					'label' => esc_html__( 'Field Mapping', 'gravityflowformconnector' ),
					'type'           => 'dynamic_field_map',
					'disable_custom' => true,
					'field_map'      => $this->field_mappings(),
					'tooltip'   => '<h6>' . esc_html__( 'Mapping', 'gravityflowformconnector' ) . '</h6>' . esc_html__( 'Map the fields of this form to the selected form. Values from this form will be saved in the entry in the selected form' , 'gravityflowformconnector' ),
					'dependency' => array(
						'field'  => 'target_form_id',
						'values' => array( '_notempty_' ),
					),
				);
			}

			$settings['fields'][] = $mapping_field;

			$entry_id_field = array(
				'name'       => 'store_new_entry_id',
				'label'    => esc_html__( 'Store New Entry ID', 'gravityflowformconnector' ),
				'type'     => 'checkbox_and_container',
				'checkbox' => array(
					'label' => esc_html__( 'Store the ID of the new entry.', 'gravityflowformconnector' ),
				),
				'settings' => array(
					array(
						'name'  => 'new_entry_id_field',
						'type'  => 'field_select',
						'args'  => array(
							'input_types' => array(
								'text',
								'textarea',
								'hidden',
							),
						),
					),
				),
			);

			$settings['fields'][] = $entry_id_field;

			return $settings;
		}

		/**
		 * Prepare field map.
		 *
		 * @return array
		 */
		public function field_mappings() {

			$target_form_id = $this->get_setting( 'target_form_id' );

			if ( empty( $target_form_id ) ) {
				return false;
			}

			$target_form = $this->get_target_form( $target_form_id );

			if ( empty( $target_form ) ) {
				return false;
			}

			$fields = $this->get_field_map_choices( $target_form );
			return $fields;
		}

		/**
		 * Prepare value map.
		 *
		 * @return array
		 */
		public function value_mappings() {

			$form = $this->get_form();

			$fields = $this->get_field_map_choices( $form );
			return $fields;
		}

		function process() {
			$server_type = $this->server_type;
			if ( $server_type == 'remote' ) {
				$result = $this->process_remote_action();
			} else {
				$result = $this->process_local_action();
			}
			$note = $this->get_name() . ': ' . esc_html__( 'Processed.', 'gravityflow' );
			$this->add_note( $note );
			return $result;
		}

		public function process_local_action() {
			$entry = $this->get_entry();

			$form = $this->filter_form( $this->get_form(), $entry );

			$new_entry = $this->do_mapping( $form, $entry );

			if ( ! empty( $new_entry ) ) {
				$new_entry['form_id'] = $this->target_form_id;
				$entry_id = GFAPI::add_entry( $new_entry );
				if ( is_wp_error( $entry_id ) ) {
					$this->log_debug( __METHOD__ .'(): failed to add entry' );
				} else {
					$this->maybe_store_new_entry_id( $entry_id );
				}
			}

			/**
			 * Fires after the New Entry FC step has been processed.
			 *
			 * @since 1.7.5
			 *
			 * @param int                          $entry_id The newly created entry ID.
			 * @param array                        $entry    The entry for which the step was processed.
			 * @param array                        $form     The form for which the entry was submitted.
			 * @param \Gravity_Flow_Step_New_Entry $this     The current instance of the Gravity_Flow_Step_New_Entry class.
			 */
			do_action( 'gravityflowformconnector_post_' . $this->get_type(), $entry_id, $entry, $form, $this );

			return true;
		}

		public function process_remote_action() {
			$entry = $this->get_entry();

			$form = $this->filter_form( $this->get_form(), $entry );

			$new_entry = $this->do_mapping( $form, $entry );

			if ( ! empty( $new_entry ) ) {
				$new_entry['form_id'] = $this->target_form_id;
				$entry_id = $this->add_remote_entry( $new_entry );
				$this->maybe_store_new_entry_id( $entry_id );
			}

			return true;
		}

		/**
		 * Stores the specified entry ID if the setting is enabled.
		 *
		 * @param $entry_id
		 */
		public function maybe_store_new_entry_id( $entry_id ) {

			if ( ! $this->store_new_entry_idEnable ) {
				$this->log_debug( __METHOD__ .'(): not storing the new entry ID because the setting is not enabled' );
				return;
			}

			$entry_id = absint( $entry_id );

			if ( empty( $entry_id ) ) {
				$this->log_debug( __METHOD__ .'(): failed to store new entry ID' );
				return;
			}

			$field_id = $this->new_entry_id_field;

			GFAPI::update_entry_field( $this->get_entry_id(), $field_id, $entry_id );
		}

		public function get_forms() {
			$server_type = $this->get_setting( 'server_type' );
			if ( $server_type == 'remote' ) {
				$forms = $this->get_remote_forms();
				$forms = json_decode( json_encode( $forms ) );
			} else {
				$forms = GFFormsModel::get_forms();
			}
			return $forms;
		}

		public function get_remote_forms() {
			$forms = $this->remote_request( 'forms' );

			if ( empty( $forms ) || is_wp_error( $forms ) ) {
				$forms = array();
			}

			return $forms;
		}

		function calculate_signature( $string, $private_key ) {
			$hash = hash_hmac( 'sha1', $string, $private_key, true );
			$sig = rawurlencode( base64_encode( $hash ) );
			return $sig;
		}

		public function get_target_form( $form_id ) {
			$server_type = $this->get_setting( 'server_type' );
			if ( $server_type == 'remote' ) {
				$form = $this->get_remote_form( $form_id );
			} else {
				$form = GFAPI::get_form( $form_id );
			}
			return $form;
		}

		public function get_remote_form( $form_id ) {
			$form = $this->remote_request( 'forms/' . $form_id );
			if ( empty( $form ) || is_wp_error( $form ) ) {
				$form = false;
			}
			$form = GFFormsModel::convert_field_objects( $form );
			return $form;
		}

		public function remote_request( $route, $method = 'GET', $body = null, $query_args = array() ) {

			$this->log_debug( __METHOD__ . '(): starting.' );

			$site_url = $this->get_setting( 'remote_site_url' );
			$api_key = $this->get_setting( 'remote_public_key' );
			$private_key = $this->get_setting( 'remote_private_key' );

			if ( empty( $site_url ) || empty( $api_key ) || empty( $private_key ) ) {
				return false;
			}

			$expires = strtotime( '+5 mins' );
			$string_to_sign = sprintf( '%s:%s:%s:%s', $api_key, $method, $route, $expires );

			$this->log_debug( __METHOD__ . '(): string to sign: ' . $string_to_sign );

			$sig = $this->calculate_signature( $string_to_sign, $private_key );
			$site_url = trailingslashit( $site_url );
			$route = trailingslashit( $route );
			$url = $site_url . 'gravityformsapi/' . $route . '?api_key=' . $api_key . '&signature=' . $sig . '&expires=' . $expires;
			if ( ! empty( $query_args ) ) {
				$url .= '&' . http_build_query( $query_args );
			}

			$args = array( 'method' => $method );

			if ( in_array( $method, array( 'POST', 'PUT' ) ) ) {
				$args['body'] = $body;
			}

			$response = wp_remote_request( $url, $args );

			$this->log_debug( __METHOD__ . '(): response: ' . print_r( $response, true ) );

			$response_body = wp_remote_retrieve_body( $response );

			if ( wp_remote_retrieve_response_code( $response ) != 200 || ( empty( $response_body ) ) ) {
				return false;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( $body['status'] > 202 ) {
				return false;
			}

			return $body['response'];
		}

		/**
		 * Add the remote entry
		 *
		 * @param $entry
		 *
		 * @return int The new Entry ID
		 */
		public function add_remote_entry( $entry ) {
			$target_form_id = $this->target_form_id;
			$route = 'forms/' . $target_form_id . '/entries';
			$method = 'POST';
			$body = json_encode( array( $entry ) );
			$entry_ids = $this->remote_request( $route, $method, $body );
			return $entry_ids[0];
		}

		/**
		 * Returns the field map choices.
		 *
		 * @param array             $form
		 * @param null|array|string $field_type
		 * @param null|array        $exclude_field_types
		 *
		 * @return array
		 */
		public function get_field_map_choices( $form, $field_type = null, $exclude_field_types = null ) {

			$fields = array();

			// Setup first choice
			if ( rgblank( $field_type ) || ( is_array( $field_type ) && count( $field_type ) > 1 ) ) {

				$first_choice_label = __( 'Select a Field', 'gravityflowformconnector' );

			} else {

				$type = is_array( $field_type ) ? $field_type[0] : $field_type;
				$type = ucfirst( GF_Fields::get( $type )->get_form_editor_field_title() );

				$first_choice_label = sprintf( __( 'Select a %s Field', 'gravityflowformconnector' ), $type );

			}

			$fields[] = array( 'value' => '', 'label' => $first_choice_label );

			// if field types not restricted add the default fields and entry meta
			if ( is_null( $field_type ) ) {
				$fields[] = array( 'value' => 'id', 'label' => esc_html__( 'Entry ID', 'gravityflowformconnector' ) );
				$fields[] = array( 'value' => 'date_created', 'label' => esc_html__( 'Entry Date', 'gravityflowformconnector' ) );
				$fields[] = array( 'value' => 'ip', 'label' => esc_html__( 'User IP', 'gravityflowformconnector' ) );
				$fields[] = array( 'value' => 'source_url', 'label' => esc_html__( 'Source Url', 'gravityflowformconnector' ) );
				$fields[] = array( 'value' => 'created_by', 'label' => esc_html__( 'Created By', 'gravityflowformconnector' ) );

				$server_type = $this->get_setting( 'server_type' );
				$entry_meta = $server_type == 'remote' ? array() : GFFormsModel::get_entry_meta( $form['id'] );
				foreach ( $entry_meta as $meta_key => $meta ) {
					$fields[] = array( 'value' => $meta_key, 'label' => rgars( $entry_meta, "{$meta_key}/label" ) );
				}
			}

			// Populate form fields
			if ( is_array( $form['fields'] ) ) {
				foreach ( $form['fields'] as $field ) {
					$input_type = $field->get_input_type();
					$inputs     = $field->get_entry_inputs();
					$field_is_valid_type = ( empty( $field_type ) || ( is_array( $field_type ) && in_array( $input_type, $field_type ) ) || ( ! empty( $field_type ) && $input_type == $field_type ) );

					if ( is_null( $exclude_field_types ) ) {
						$exclude_field = false;
					} elseif ( is_array( $exclude_field_types ) ) {
						if ( in_array( $input_type, $exclude_field_types ) ) {
							$exclude_field = true;
						} else {
							$exclude_field = false;
						}
					} else {
						//not array, so should be single string
						if ( $input_type == $exclude_field_types ) {
							$exclude_field = true;
						} else {
							$exclude_field = false;
						}
					}

					if ( is_array( $inputs ) && $field_is_valid_type && ! $exclude_field ) {
						//If this is an address field, add full name to the list
						if ( $input_type == 'address' ) {
							$fields[] = array(
								'value' => $field->id,
								'label' => GFCommon::get_label( $field ) . ' (' . esc_html__( 'Full', 'gravityflowformconnector' ) . ')',
							);
						}
						//If this is a name field, add full name to the list
						if ( $input_type == 'name' ) {
							$fields[] = array(
								'value' => $field->id,
								'label' => GFCommon::get_label( $field ) . ' (' . esc_html__( 'Full', 'gravityflowformconnector' ) . ')',
							);
						}
						//If this is a checkbox field, add to the list
						if ( $input_type == 'checkbox' ) {
							$fields[] = array(
								'value' => $field->id,
								'label' => GFCommon::get_label( $field ) . ' (' . esc_html__( 'Selected', 'gravityflowformconnector' ) . ')',
							);
						}

						foreach ( $inputs as $input ) {
							$fields[] = array(
								'value' => $input['id'],
								'label' => GFCommon::get_label( $field, $input['id'] ),
							);
						}
					} elseif ( ! rgar( $field, 'displayOnly' ) && $field_is_valid_type && ! $exclude_field ) {
						$fields[] = array( 'value' => $field->id, 'label' => GFCommon::get_label( $field ) );
					}
				}
			}

			return $fields;
		}

		/**
		 * @param $form
		 * @param $entry
		 *
		 * @return array $new_entry
		 */
		public function do_mapping( $form, $entry ) {
			$new_entry = array();

			if ( ! is_array( $this->mappings ) ) {

				return $new_entry;
			}

			$target_form = $this->get_target_form( $this->target_form_id );

			if ( ! $target_form ) {
				$this->log_debug( __METHOD__ . '(): aborting; unable to get target form.' );

				return $new_entry;
			}

			foreach ( $this->mappings as $mapping ) {
				if ( rgblank( $mapping['key'] ) ) {
					continue;
				}

				$new_entry = $this->add_mapping_to_entry( $mapping, $entry, $new_entry, $form, $target_form );
			}

			return apply_filters( 'gravityflowformconnector_' . $this->get_type(), $new_entry, $entry, $form, $target_form, $this );
		}

		/**
		 * Add the mapped value to the new entry.
		 *
		 * @param array $mapping The properties for the mapping being processed.
		 * @param array $entry The entry being processed by this step.
		 * @param array $new_entry The entry to be added or updated.
		 * @param array $form The form being processed by this step.
		 * @param array $target_form The target form for the entry being added or updated.
		 *
		 * @return array
		 */
		public function add_mapping_to_entry( $mapping, $entry, $new_entry, $form, $target_form ) {
			$target_field_id = (string) trim( $mapping['key'] );
			$source_field_id = (string) $mapping['value'];

			$source_field = GFFormsModel::get_field( $form, $source_field_id );

			if ( is_object( $source_field ) ) {
				$is_full_source      = $source_field_id === (string) intval( $source_field_id );
				$source_field_inputs = $source_field->get_entry_inputs();
				$target_field        = GFFormsModel::get_field( $target_form, $target_field_id );

				if ( $is_full_source && is_array( $source_field_inputs ) ) {
					$is_full_target      = $target_field_id === (string) intval( $target_field_id );
					$target_field_inputs = is_object( $target_field ) ? $target_field->get_entry_inputs() : false;

					if ( $is_full_target && is_array( $target_field_inputs ) ) {
						foreach ( $source_field_inputs as $input ) {
							$input_id               = str_replace( $source_field_id . '.', $target_field_id . '.', $input['id'] );
							$source_field_value     = $this->get_source_field_value( $entry, $source_field, $input['id'] );
							$new_entry[ $input_id ] = $this->get_target_field_value( $source_field_value, $target_field, $input_id );
						}
					} else {
						$new_entry[ $target_field_id ] = $this->get_source_field_value( $entry, $source_field, $source_field_id );
					}
				} else {
					$source_field_value            = $this->get_source_field_value( $entry, $source_field, $source_field_id );
					$new_entry[ $target_field_id ] = $this->get_target_field_value( $source_field_value, $target_field, $target_field_id );
				}
			} elseif ( $source_field_id == 'gf_custom' ) {
				$new_entry[ $target_field_id ] = GFCommon::replace_variables( $mapping['custom_value'], $form, $entry, false, false, false, 'text' );
			} else {
				$new_entry[ $target_field_id ] = $entry[ $source_field_id ];
			}

			return $new_entry;
		}

		/**
		 * Get the source field value.
		 *
		 * Returns the choice text instead of the unique value for choice based poll, quiz and survey fields.
		 *
		 * The source field choice unique value will not match the target field unique value.
		 *
		 * @param array $entry The entry being processed by this step.
		 * @param GF_Field $source_field The source field being processed.
		 * @param string $source_field_id The ID of the source field or input.
		 *
		 * @return string
		 */
		public function get_source_field_value( $entry, $source_field, $source_field_id ) {
			if ( in_array( $source_field->type, array( 'poll', 'quiz', 'survey' ) ) ) {
				$field_value = $entry[ $source_field_id ];

				if ( $source_field->inputType == 'rank' ) {
					$values = explode( ',', $field_value );
					foreach ( $values as &$value ) {
						$value = $this->get_source_choice_text( $value, $source_field );
					}

					return implode( ',', $values );
				}

				if ( $source_field->inputType == 'likert' && $source_field->gsurveyLikertEnableMultipleRows ) {
					list( $row_value, $field_value ) = rgexplode( ':', $field_value, 2 );
				}

				return $this->get_source_choice_text( $field_value, $source_field );
			} elseif ( in_array( $source_field->type, array( 'list', 'fileupload' ) ) && isset( $entry[ $source_field_id ] ) ) {
				$field_value = $entry[ $source_field_id ];
			} else {
				/**
				 * Allow choice text to be returned when retrieving the source field value.
				 *
				 * @since 1.3.1-dev
				 *
				 * @param bool              $use_choice_text When processing choice based fields should the choice text be returned instead of the value. Default is false.
				 * @param GF_Field          $source_field    The source field being processed.
				 * @param array             $entry           The entry being processed by this step.
				 * @param Gravity_Flow_Step $this            The current step.
				 */
				$use_choice_text = apply_filters( 'gravityflowformconnector_' . $this->get_type() . '_use_choice_text', false, $source_field, $entry, $this );
				$field_value = $source_field->get_value_export( $entry, $source_field_id, $use_choice_text );
			}

			return $field_value;
		}

		/**
		 * Get the value to be set for the target field.
		 *
		 * Returns the target fields choice unique value instead of the source field choice text for choice based poll, quiz and survey fields.
		 *
		 * @param string $field_value The source field value.
		 * @param GF_Field $target_field The target field being processed.
		 * @param string $target_field_id The ID of the target field or input.
		 *
		 * @return string
		 */
		public function get_target_field_value( $field_value, $target_field, $target_field_id ) {
			if ( is_object( $target_field ) && in_array( $target_field->type, array( 'poll', 'quiz', 'survey' ) ) ) {
				if ( $target_field->inputType == 'rank' ) {
					$values = explode( ',', $field_value );
					foreach ( $values as &$value ) {
						$value = $this->get_target_choice_value( $value, $target_field );
					}

					return implode( ',', $values );
				}

				$field_value = $this->get_target_choice_value( $field_value, $target_field );

				if ( $target_field->inputType == 'likert' && $target_field->gsurveyLikertEnableMultipleRows ) {
					$row_value   = $target_field->get_row_id( $target_field_id );
					$field_value = sprintf( '%s:%s', $row_value, $field_value );
				}
			}

			return $field_value;
		}

		/**
		 * Gets the choice text for the supplied choice value.
		 *
		 * @param string $selected_choice The choice value from the source field.
		 * @param GF_Field $source_field The source field being processed.
		 *
		 * @return string
		 */
		public function get_source_choice_text( $selected_choice, $source_field ) {
			return $this->get_choice_property( $selected_choice, $source_field->choices, 'value', 'text' );
		}

		/**
		 * Gets the choice value for the supplied choice text.
		 *
		 * @param string $selected_choice The choice text from the source field.
		 * @param GF_Field $target_field The target field being processed.
		 *
		 * @return string
		 */
		public function get_target_choice_value( $selected_choice, $target_field ) {
			return $this->get_choice_property( $selected_choice, $target_field->choices, 'text', 'value' );
		}

		/**
		 * Helper to get the specified choice property for the selected choice.
		 *
		 * @param string $selected_choice The selected choice value or text.
		 * @param array  $choices The field choices.
		 * @param string $compare_property The choice property the $selected_choice is to be compared against.
		 * @param string $return_property The choice property to be returned.
		 *
		 * @return string
		 */
		public function get_choice_property( $selected_choice, $choices, $compare_property, $return_property ) {
			if ( $selected_choice && is_array( $choices ) ) {
				foreach ( $choices as $choice ) {
					if ( $choice[ $compare_property ] == $selected_choice ) {
						return $choice[ $return_property ];
					}
				}
			}

			return $selected_choice;
		}

		/**
		 * Allows a form to be filtered before it used to process the step.
		 *
		 * @since 1.7.4
		 *
		 * @param array $form  The form to be processed.
		 * @param array $entry The entry being processed.
		 *
		 * @return array
		 */
		public function filter_form( $form, $entry ) {
			/**
			 * Allows the form to be modified before it is processed.
			 *
			 * @since 1.7.4
			 *
			 * @param array $form  The form to be processed.
			 * @param array $entry The entry being processed.
			 */
			return apply_filters( "gravityflowformconnector_{$this->get_type()}_form", $form, $entry );
		}

	}
}

Gravity_Flow_Steps::register( new Gravity_Flow_Step_New_Entry() );
