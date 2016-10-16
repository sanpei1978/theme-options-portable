<?php

namespace ThemeOptions;

require_once LIB_PATH . '/class-config.php';
require_once LIB_PATH . '/class-addon.php';

class Theme_Options {

	private $options_page = ''; // page slug name of page
	private $options_group = ''; // page slug name of page, also referred to in Settings API as option group name
	private $options_name = '';

	private $obj_options;
	private $addons_actived = [];

	private $config = [];

	public function __construct( stdClass &$themeinfo = null ) {
		$this->config = Config::get();
		$this->options_page = $this->config['loader_id'];
		$this->options_group = $this->config['loader_id'];
		$this->options_name = $this->config['domain'] . '_' . $this->options_group;

		$this->obj_options = $this->config['obj_options'];

		add_action( 'load-themes.php', array( $this, 'activate_admin_notice' ) );
		add_action( 'admin_init', array( $this, 'initialize_theme_admin' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );

		$options = $this->obj_options->get_option( $this->options_name );
		if ( ! empty( $options ) ) {
			foreach ( $options as $addon_id => $is_active ) {
				if ( 'on' === $is_active ) {
					$this->addons_actived[ $addon_id ]['config'] = Config::get( $addon_id );
					$this->addons_actived[ $addon_id ]['addon'] = new Addon( $addon_id, $this->config['loader_id'], $this->addons_actived[ $addon_id ]['config'] ); // Load add-on.
					require_once( ADDON_PATH . '/' . $addon_id . '/' . $addon_id . '.php' );
					LoginPage\Login_Page::get_instance( $this->addons_actived[ $addon_id ]['addon'] );
				}
			}
		}
	}

	public function activate_admin_notice() {
		global $pagenow;
		if ( 'themes.php' == $pagenow && isset( $_GET['activated'] ) ) {
			add_action( 'admin_notices', array( $this, 'write_welcome_admin_notice' ), 99 );
		}
	}

	public function write_welcome_admin_notice() {
		?>
		<div class="updated notice is-dismissible">
			<p>
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=' . $this->options_page ) ); ?>">
					<?php echo esc_html__( 'Welcome. Here is theme options.', 'sanpeity' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	public function initialize_theme_admin() {

		$input_fields = array();

		$i = 0;
		foreach ( $this->config['addons'] as $addon_id ) {
			$config = Config::get( $addon_id );
			$label = __( '(You cannot use.)', 'sanpeity' );
			$type = 'disabled';
			if ( ! empty( $config ) ) {
				$label = 'Use ' . $config['display_name'];
				$type = 'checkbox';
			}
			$input_fields[] = array(
				'id'		=> $addon_id,
				'title'	=> (0 === $i) ? 'Usable add-ons' : '',
				'label'	=> $label,
				'type'	=> $type,
				'section' => 'setting_section_1',
			);
			$i++;
		}

		$this->obj_options->initialize(
			$this->options_page,
			$this->options_group,
			$this->config['setting_sections'],
			$this->options_name,
			$input_fields
		);

		$options = $this->obj_options->get_option( $this->options_name );
		if ( ! empty( $options ) ) {
			foreach ( $options as $addon_id => $is_active ) {
				if ( 'on' === $is_active ) {
					$this->addons_actived[ $addon_id ]['addon']->initialize();
				}
			}
		}

	}

	function register_admin_menu() {
	 	$page_slug = add_theme_page( THEME_NAME, esc_html__( 'Theme Options', 'sanpeity' ), 'edit_theme_options', $this->options_page, array( $this, 'write_page' ) );
		add_action( 'admin_print_scripts-' . $page_slug, array( $this, 'enqueue_script' ) );
		add_action( 'admin_print_styles-' . $page_slug, array( $this, 'enqueue_style' ) );
	}

	public function write_page() {
		$options = $this->obj_options->get_option( $this->options_name );
		?>
		<div class="wrap">
			<h2><?php echo esc_html__( 'Theme Options', 'sanpeity' ); ?></h2>
			<div class="mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
			  <div class="mdl-tabs__tab-bar">
					<a href="#panel-setting" class="mdl-tabs__tab is-active"><?php echo esc_html( $this->config['display_name'] ); ?></a>
			<?php
			if ( ! empty( $options ) ) {
				foreach ( $options as $addon_id => $is_active ) {
					if ( 'on' === $is_active ) {
						echo '<a href="#panel-' , $addon_id , '" class="mdl-tabs__tab">' , $this->addons_actived[ $addon_id ]['addon']->display_name , '</a>';
					}
				}
			}
			?>
			  </div>
				<div class="mdl-tabs__panel is-active" id="panel-setting">
					<?php
					echo '<form method="post" action="' , esc_html( $this->obj_options->FORM_ACTION ) , '" id="' , $this->options_name , '">';
					$this->obj_options->fill();
					submit_button( __( 'Save Changes', 'sanpeity' ), 'primary large', 'submit', true, array( 'form' => $this->options_name ) );
					echo '</form>';
					?>
				</div>
					<?php
					if ( ! empty( $options ) ) {
						foreach ( $options as $addon_id => $is_active ) {
							if ( 'on' === $is_active ) {
								echo '<div class="mdl-tabs__panel" id="panel-' , $addon_id , '">';
								echo '<form method="post" action="' , esc_html( $this->addons_actived[ $addon_id ]['addon']->form_action ), '" id="' , $this->addons_actived[ $addon_id ]['addon']->options_name, '">';
								$this->addons_actived[ $addon_id ]['addon']->fill_fields();
								submit_button( __( 'Save Changes', 'sanpeity' ), 'primary large', 'submit', true, array( 'form' => $this->addons_actived[ $addon_id ]['addon']->options_name ) );
								echo '</form>';
								echo '</div>';
							}
						}
					}
					?>
			</div>
		</div>
		<?php
	}

	public function enqueue_script( $hook_suffix ) {
		//if ( 'appearance_page_' . $this->options_page === $hook_suffix ) {
		wp_enqueue_media();
		wp_enqueue_script( 'media-uploader', JS_DIR . '/media-uploader.js', array( 'jquery' ), filemtime( JS_PATH . '/media-uploader.js' ), false );
		wp_enqueue_script( 'material', 'https://code.getmdl.io/1.2.1/material.min.js', array(), '1.2.1', false );
		//}
	}

	public function enqueue_style( $hook_suffix ) {
		wp_enqueue_style( 'material-icon', 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), '', 'all' );
		wp_enqueue_style( 'material', 'https://code.getmdl.io/1.2.1/material.blue-light_blue.min.css', array( 'material-icon' ), '', 'all' );
	}

}
