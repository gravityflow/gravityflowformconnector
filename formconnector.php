<?php
/*
Plugin Name: Gravity Flow Form Connector
Plugin URI: https://gravityflow.io
Description: Form Connector Extension for Gravity Flow.
Version: 1.7.5-dev
Author: Gravity Flow
Author URI: https://gravityflow.io
License: GPL-2.0+

------------------------------------------------------------------------
Copyright 2015-2020 Steven Henty S.L.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'GRAVITY_FLOW_FORM_CONNECTOR_VERSION', '1.7.5-dev' );
define( 'GRAVITY_FLOW_FORM_CONNECTOR_EDD_ITEM_ID', 3375 );
define( 'GRAVITY_FLOW_FORM_CONNECTOR_EDD_ITEM_NAME', 'Form Connector' );

add_action( 'gravityflow_loaded', array( 'Gravity_Flow_Form_Connector_Bootstrap', 'load' ), 1 );

class Gravity_Flow_Form_Connector_Bootstrap {

	public static function load() {
		require_once( 'includes/class-dynamic-hook.php' );
		require_once( 'includes/class-common-step-settings.php' );
		require_once( 'includes/class-step-form-submission.php' );
		require_once( 'includes/class-step-new-entry.php' );
		require_once( 'includes/class-step-update-entry.php' );
		require_once( 'includes/class-step-update-fields.php' );
		require_once( 'includes/class-step-delete-entry.php' );
		require_once( 'includes/class-merge-tag-form-submission.php' );
		require_once( 'class-form-connector.php' );

		// Registers the class name with GFAddOn.
		GFAddOn::register( 'Gravity_Flow_Form_Connector' );

		if ( defined( 'GRAVITY_FLOW_FORM_CONNECTOR_LICENSE_KEY' ) ) {
			gravity_flow_form_connector()->license_key = GRAVITY_FLOW_FORM_CONNECTOR_LICENSE_KEY;
		}
	}
}

function gravity_flow_form_connector() {
	if ( class_exists( 'Gravity_Flow_Form_Connector' ) ) {
		return Gravity_Flow_Form_Connector::get_instance();
	}
}


add_action( 'admin_init', 'gravityflow_form_connector_edd_plugin_updater', 0 );

function gravityflow_form_connector_edd_plugin_updater() {

	if ( ! function_exists( 'gravity_flow_form_connector' ) ) {
		return;
	}

	$gravity_flow_form_connector = gravity_flow_form_connector();
	if ( $gravity_flow_form_connector ) {

		if ( defined( 'GRAVITY_FLOW_FORM_CONNECTOR_LICENSE_KEY' ) ) {
			$license_key = GRAVITY_FLOW_FORM_CONNECTOR_LICENSE_KEY;
		} else {
			$settings = $gravity_flow_form_connector->get_app_settings();
			$license_key = trim( rgar( $settings, 'license_key' ) );
		}

		$edd_updater = new Gravity_Flow_EDD_SL_Plugin_Updater( GRAVITY_FLOW_EDD_STORE_URL, __FILE__, array(
			'version'   => GRAVITY_FLOW_FORM_CONNECTOR_VERSION,
			'license'   => $license_key,
			'item_id' => GRAVITY_FLOW_FORM_CONNECTOR_EDD_ITEM_ID,
			'author'    => 'Gravity Flow',
		) );
	}

}
