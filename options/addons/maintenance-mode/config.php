<?php
/**
 * Copyright (c) 2016 sanpeity (https://github.com/sanpei1978)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace ThemeOptions;

require_once INCLUDES_PATH . '/class-wp-settings.php';

return [
	'display_name' => __( 'Maintenance Mode Settings', 'theme-options' ),
	'domain' => 'sanpeity',
	'data_store' => 'wp-settings', // The way of data store. "wp-options" or "wp-settings"
	'addons' => [], // Using other add-ons in the add-on. Next feature.
	'setting_sections' => [
		[
			'id'			=> 'setting_section_maintenance_mode_1',
			'title'		=> '',
			'summary'	=> '',
		],
		[
			'id'			=> 'setting_section_maintenance_mode_2',
			'title'		=> esc_html__( 'Display content', 'theme-options' ),
			'summary'	=> '',
		],
	],
	'input_fields' => [
		[
			'id' => 'is_maintenance_mode',
			'title' => '',
			'type' => 'checkbox',
			'label' => esc_html__( 'Be maintenance mode', 'theme-options' ),
			'section' => 'setting_section_maintenance_mode_1',
		],
		[
			'id' => 'is-non-logged-in',
			'title' => '',
			'type' => 'checkbox',
			'label' => esc_html__( 'Only non-logged in user', 'theme-options' ),
			'section' => 'setting_section_maintenance_mode_1',
		],
		[
			'id' => 'maintenance-period',
			'title' => esc_html__( 'Period', 'theme-options' ),
			'type' => 'text',
			'label' => esc_html__( 'Period of maintenance. Free text.', 'theme-options' ),
			'section' => 'setting_section_maintenance_mode_2',
		],
		[
			'id' => 'contact-name',
			'title' => esc_html__( 'Name', 'theme-options' ),
			'type' => 'text',
			'label' => esc_html__( 'Your name', 'theme-options' ),
			'section' => 'setting_section_maintenance_mode_2',
		],
		[
			'id' => 'contact-address',
			'title' => esc_html__( 'Address', 'theme-options' ),
			'type' => 'text',
			'label' => esc_html__( 'Your address', 'theme-options' ),
			'section' => 'setting_section_maintenance_mode_2',
		],
		[
			'id' => 'contact-tel',
			'title' => esc_html__( 'TEL', 'theme-options' ),
			'type' => 'text',
			'label' => esc_html__( 'Your tel number', 'theme-options' ),
			'section' => 'setting_section_maintenance_mode_2',
		],
		[
			'id' => 'contact-fax',
			'title' => esc_html__( 'FAX', 'theme-options' ),
			'type' => 'text',
			'label' => esc_html__( 'Your fax number', 'theme-options' ),
			'section' => 'setting_section_maintenance_mode_2',
		],
	],
];
