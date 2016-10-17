<?php

namespace ThemeOptions;

require_once LIB_PATH . '/class-config.php';
require_once LIB_PATH . '/class-addon.php';
require_once FRONTEND_PATH . '/class-frontend.php';

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

		add_filter( 'script_loader_tag', array( $this, 'change_enqueued_script_type' ), 10, 2 );
		add_filter( 'style_loader_tag', array( $this, 'change_enqueued_style_type' ), 10, 2 );

		$options = $this->obj_options->get_option( $this->options_name );
		if ( ! empty( $options ) ) {
			foreach ( $options as $addon_id => $is_active ) {
				if ( 'on' === $is_active ) {
					$this->addons_actived[ $addon_id ] = new Addon( $addon_id, $this->config['loader_id'], Config::get( '', $addon_id ) ); // Load add-on.
					require_once( ADDON_PATH . '/' . $addon_id . '/' . $addon_id . '.php' );
					LoginPage\Login_Page::get_instance( $this->addons_actived[ $addon_id ] );
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
			$config = Config::get( '', $addon_id );
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
					$this->addons_actived[ $addon_id ]->initialize();
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
		\FrontEnd\Front_End::write_container( $options, $this->addons_actived, $this->obj_options, $this->options_name, $this->config['display_name'] );
	}

	public function enqueue_script( $hook_suffix ) {
		//if ( 'appearance_page_' . $this->options_page === $hook_suffix ) {
		wp_enqueue_media();
		wp_enqueue_script( 'media-uploader', JS_DIR . '/media-uploader.js', array( 'jquery' ), filemtime( JS_PATH . '/media-uploader.js' ), false );
		if ( 'material' === $this->config['frontend'] ) {
			wp_enqueue_script( 'material', 'https://code.getmdl.io/1.2.1/material.min.js', array(), '1.2.1', false );
		}
		if ( 'bootstrap' === $this->config['frontend'] ) {
			wp_enqueue_script( 'tether', 'https://raw.githubusercontent.com/HubSpot/tether/master/dist/js/tether.min.js', array(), '', false );
			wp_enqueue_script( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/js/bootstrap.min.js' , array( 'tether' ), '4.0.0', false );
		}
		//}
	}

	public function enqueue_style( $hook_suffix ) {
		wp_enqueue_style( 'material-icon', 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), '', 'all' );
		if ( 'material' === $this->config['frontend'] ) {
			wp_enqueue_style( 'material', 'https://code.getmdl.io/1.2.1/material.blue-light_blue.min.css', array( 'material-icon' ), '', 'all' );
		}
		if ( 'bootstrap' === $this->config['frontend'] ) {
			wp_enqueue_style( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.4/css/bootstrap.min.css', array(), '', 'all' );
		}
	}

	public function change_enqueued_script_type( $tag, $handle ) {
		if ( 'bootstrap' === $handle ) {
			return str_replace( "type='text/javascript'", 'integrity="sha384-VjEeINv9OSwtWFLAtmc4JCtEJXXBub00gtSnszmspDLCtC0I4z4nqz7rEFbIZLLU" crossorigin="anonymous"', $tag );
		} else {
			return $tag;
		}
	}

	public function change_enqueued_style_type( $tag, $handle ) {
		if ( 'bootstrap' === $handle ) {
			return  preg_replace( array( "| type='.+?'s*|","| id='.+?'s*|", '| />|' ), array( 'integrity="sha384-2hfp1SzUoho7/TsGGGDaFdsuuDL0LX2hnUp6VkX3CUQ2K4K+xjboZdsXyp4oUHZj" crossorigin="anonymous"', ' ', '>' ), $tag );
		} else {
			return $tag;
		}
	}

}
