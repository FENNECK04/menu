<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Interslide_Vertical_Menu_Plugin {
	private static $instance = null;
	private $option_name = 'ivm_settings';
	private $displayed = false;
	private $menu_locations = array(
		'ivm_primary'   => 'ivm_primary',
		'ivm_secondary' => 'ivm_secondary',
		'ivm_bottom'    => 'ivm_bottom',
	);

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_textdomain' ) );
		add_action( 'init', array( $this, 'register_menu_locations' ) );
		add_action( 'init', array( $this, 'register_shortcode' ) );
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_assets' ) );
		add_action( 'wp_body_open', array( $this, 'render_global_menu' ) );
		add_filter( 'body_class', array( $this, 'filter_body_class' ) );
	}

	public function register_textdomain() {
		load_plugin_textdomain( 'interslide-vertical-menu', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages' );
	}

	public function get_default_settings() {
		return array(
			'enabled'            => 1,
			'display_mode'       => 'global',
			'logo_type'          => 'text',
			'logo_text'          => 'Interslide.',
			'logo_image_url'     => '',
			'logo_link'          => home_url( '/' ),
			'pill_enabled'       => 1,
			'pill_text'          => __( 'Interslide. 2026', 'interslide-vertical-menu' ),
			'pill_url'           => home_url( '/' ),
			'primary_items'      => $this->get_default_primary_items(),
			'secondary_items'    => $this->get_default_secondary_items(),
			'bottom_items'       => $this->get_default_bottom_items(),
			'background_color'   => '#0f0f10',
			'text_color'         => '#f5f5f5',
			'hover_color'        => '#1a1b1d',
			'pill_color'         => '#2f6feb',
			'divider_color'      => '#26282b',
			'font_family'        => '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
			'font_size'          => 16,
			'link_padding_y'     => 8,
			'link_padding_x'     => 6,
			'border_radius'      => 10,
			'panel_shadow'       => 0,
			'header_bg'          => '#0f0f10',
			'header_text_color'  => '#f5f5f5',
			'toggle_color'       => '#f5f5f5',
			'sidebar_width'      => 280,
			'mobile_breakpoint'  => 900,
			'search_mode'        => 'link',
			'search_url'         => home_url( '/' ),
			'edition_enabled'    => 1,
			'edition_options'    => $this->get_default_editions(),
			'edition_default'    => 0,
			'breaking_enabled'   => 0,
			'breaking_text'      => __( 'Breaking news', 'interslide-vertical-menu' ),
			'breaking_url'       => home_url( '/' ),
			'live_enabled'       => 0,
			'live_text'          => __( 'Live', 'interslide-vertical-menu' ),
			'live_url'           => home_url( '/' ),
			'date_enabled'       => 1,
			'newsletter_enabled' => 0,
			'newsletter_text'    => __( 'Subscribe', 'interslide-vertical-menu' ),
			'newsletter_url'     => home_url( '/' ),
			'utility_links'      => $this->get_default_utility_links(),
			'trending_topics'    => $this->get_default_trending_topics(),
			'trending_label'     => __( 'Trending', 'interslide-vertical-menu' ),
			'account_enabled'    => 0,
			'account_text'       => __( 'Sign in', 'interslide-vertical-menu' ),
			'account_url'        => wp_login_url(),
			'footer_text'        => '',
			'breaking_badge_label' => __( 'Breaking', 'interslide-vertical-menu' ),
			'search_label'       => __( 'Search', 'interslide-vertical-menu' ),
			'edition_label'      => __( 'Édition', 'interslide-vertical-menu' ),
			'section_order'      => 'utility,meta,primary,trending,divider,secondary,search,divider,bottom,newsletter,account,edition,footer',
			'bottom_sections'    => array( 'newsletter', 'account', 'edition', 'footer' ),
			'mobile_menu_id'     => 0,
			'use_wp_menus'       => 1,
			'primary_menu_id'    => 0,
			'secondary_menu_id'  => 0,
			'bottom_menu_id'     => 0,
			'hide_theme_menu'    => 0,
			'hide_selectors'     => '.site-header, .main-navigation, nav[aria-label="Primary"], nav.wp-block-navigation',
			'custom_css'         => '',
			'cleanup_on_uninstall' => 0,
		);
	}

	public function get_settings() {
		$defaults = $this->get_default_settings();
		$settings = get_option( $this->option_name, array() );
		return wp_parse_args( $settings, $defaults );
	}

	public function register_shortcode() {
		add_shortcode( 'interslide_vertical_menu', array( $this, 'shortcode_handler' ) );
	}

	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'ivm-block',
			IVM_PLUGIN_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n' ),
			IVM_VERSION,
			true
		);

		register_block_type(
			IVM_PLUGIN_DIR . 'includes/block.json',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	public function register_menu_locations() {
		register_nav_menus(
			array(
				'ivm_primary'   => __( 'Interslide Primary Menu', 'interslide-vertical-menu' ),
				'ivm_secondary' => __( 'Interslide Secondary Menu', 'interslide-vertical-menu' ),
				'ivm_bottom'    => __( 'Interslide Bottom Menu', 'interslide-vertical-menu' ),
			)
		);
	}

	public function register_settings_page() {
		add_options_page(
			__( 'Interslide Vertical Menu', 'interslide-vertical-menu' ),
			__( 'Interslide Vertical Menu', 'interslide-vertical-menu' ),
			'manage_options',
			'interslide-vertical-menu',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'ivm_settings_group',
			$this->option_name,
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'ivm_general',
			__( 'General Settings', 'interslide-vertical-menu' ),
			'__return_false',
			'interslide-vertical-menu'
		);

		add_settings_field(
			'enabled',
			__( 'Enable Menu', 'interslide-vertical-menu' ),
			array( $this, 'render_checkbox_field' ),
			'interslide-vertical-menu',
			'ivm_general',
			array(
				'label_for'   => 'ivm_enabled',
				'option_key'  => 'enabled',
				'description' => __( 'Enable the menu output.', 'interslide-vertical-menu' ),
			)
		);

		add_settings_field(
			'display_mode',
			__( 'Display Mode', 'interslide-vertical-menu' ),
			array( $this, 'render_display_mode_field' ),
			'interslide-vertical-menu',
			'ivm_general'
		);

		add_settings_section(
			'ivm_branding',
			__( 'Branding', 'interslide-vertical-menu' ),
			'__return_false',
			'interslide-vertical-menu'
		);

		add_settings_field(
			'logo_type',
			__( 'Logo Type', 'interslide-vertical-menu' ),
			array( $this, 'render_logo_type_field' ),
			'interslide-vertical-menu',
			'ivm_branding'
		);

		add_settings_field(
			'logo_text',
			__( 'Logo Text', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_branding',
			array(
				'label_for'  => 'ivm_logo_text',
				'option_key' => 'logo_text',
			)
		);

		add_settings_field(
			'logo_image',
			__( 'Logo Image', 'interslide-vertical-menu' ),
			array( $this, 'render_logo_image_field' ),
			'interslide-vertical-menu',
			'ivm_branding'
		);

		add_settings_field(
			'logo_link',
			__( 'Logo Link', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_branding',
			array(
				'label_for'  => 'ivm_logo_link',
				'option_key' => 'logo_link',
			)
		);

		add_settings_section(
			'ivm_pill',
			__( 'Pill Button', 'interslide-vertical-menu' ),
			'__return_false',
			'interslide-vertical-menu'
		);

		add_settings_field(
			'pill_enabled',
			__( 'Enable Pill', 'interslide-vertical-menu' ),
			array( $this, 'render_checkbox_field' ),
			'interslide-vertical-menu',
			'ivm_pill',
			array(
				'label_for'   => 'ivm_pill_enabled',
				'option_key'  => 'pill_enabled',
				'description' => __( 'Show the pill badge under the logo.', 'interslide-vertical-menu' ),
			)
		);

		add_settings_field(
			'pill_text',
			__( 'Pill Text', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_pill',
			array(
				'label_for'  => 'ivm_pill_text',
				'option_key' => 'pill_text',
			)
		);

		add_settings_field(
			'pill_url',
			__( 'Pill URL', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_pill',
			array(
				'label_for'  => 'ivm_pill_url',
				'option_key' => 'pill_url',
			)
		);

		add_settings_section(
			'ivm_menu_items',
			__( 'Menu Items', 'interslide-vertical-menu' ),
			'__return_false',
			'interslide-vertical-menu'
		);

		add_settings_field(
			'section_order',
			__( 'Section order', 'interslide-vertical-menu' ),
			array( $this, 'render_section_order_field' ),
			'interslide-vertical-menu',
			'ivm_menu_items',
			array(
				'label_for'  => 'ivm_section_order',
				'option_key' => 'section_order',
			)
		);

		add_settings_field(
			'bottom_sections',
			__( 'Bottom-aligned sections', 'interslide-vertical-menu' ),
			array( $this, 'render_bottom_sections_field' ),
			'interslide-vertical-menu',
			'ivm_menu_items'
		);

		add_settings_field(
			'utility_links',
			__( 'Utility links', 'interslide-vertical-menu' ),
			array( $this, 'render_items_field' ),
			'interslide-vertical-menu',
			'ivm_menu_items',
			array(
				'option_key' => 'utility_links',
				'label'      => __( 'Label|URL (one per line).', 'interslide-vertical-menu' ),
			)
		);

		add_settings_field(
			'trending_topics',
			__( 'Trending topics', 'interslide-vertical-menu' ),
			array( $this, 'render_items_field' ),
			'interslide-vertical-menu',
			'ivm_menu_items',
			array(
				'option_key' => 'trending_topics',
				'label'      => __( 'Label|URL (one per line).', 'interslide-vertical-menu' ),
			)
		);

		add_settings_field(
			'trending_label',
			__( 'Trending label', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_menu_items',
			array(
				'label_for'  => 'ivm_trending_label',
				'option_key' => 'trending_label',
			)
		);

		add_settings_field(
			'use_wp_menus',
			__( 'Use WordPress menus', 'interslide-vertical-menu' ),
			array( $this, 'render_checkbox_field' ),
		'interslide-vertical-menu',
		'ivm_menu_items',
		array(
			'label_for'   => 'ivm_use_wp_menus',
			'option_key'  => 'use_wp_menus',
			'description' => __( 'Use menus defined in Appearance → Menus for the menu sections.', 'interslide-vertical-menu' ),
		)
	);

	add_settings_field(
		'primary_menu_id',
		__( 'Primary menu', 'interslide-vertical-menu' ),
		array( $this, 'render_menu_select_field' ),
		'interslide-vertical-menu',
		'ivm_menu_items',
		array(
			'label_for'  => 'ivm_primary_menu_id',
			'option_key' => 'primary_menu_id',
		)
	);

	add_settings_field(
		'secondary_menu_id',
		__( 'Secondary menu', 'interslide-vertical-menu' ),
		array( $this, 'render_menu_select_field' ),
		'interslide-vertical-menu',
		'ivm_menu_items',
		array(
			'label_for'  => 'ivm_secondary_menu_id',
			'option_key' => 'secondary_menu_id',
		)
	);

	add_settings_field(
		'bottom_menu_id',
		__( 'Bottom menu', 'interslide-vertical-menu' ),
		array( $this, 'render_menu_select_field' ),
		'interslide-vertical-menu',
		'ivm_menu_items',
		array(
			'label_for'  => 'ivm_bottom_menu_id',
			'option_key' => 'bottom_menu_id',
		)
	);

	add_settings_field(
		'primary_items',
		__( 'Primary Items', 'interslide-vertical-menu' ),
			array( $this, 'render_items_field' ),
			'interslide-vertical-menu',
			'ivm_menu_items',
			array(
				'option_key' => 'primary_items',
				'label'      => __( 'Label|URL|Icon (one per line).', 'interslide-vertical-menu' ),
			)
		);

		add_settings_field(
			'secondary_items',
			__( 'Secondary Items', 'interslide-vertical-menu' ),
			array( $this, 'render_items_field' ),
			'interslide-vertical-menu',
			'ivm_menu_items',
			array(
				'option_key' => 'secondary_items',
				'label'      => __( 'Label|URL|Icon (one per line).', 'interslide-vertical-menu' ),
			)
		);

		add_settings_field(
			'bottom_items',
			__( 'Bottom Items', 'interslide-vertical-menu' ),
			array( $this, 'render_items_field' ),
			'interslide-vertical-menu',
			'ivm_menu_items',
			array(
				'option_key' => 'bottom_items',
				'label'      => __( 'Label|URL (one per line).', 'interslide-vertical-menu' ),
			)
		);

		add_settings_section(
			'ivm_style',
			__( 'Style', 'interslide-vertical-menu' ),
			'__return_false',
			'interslide-vertical-menu'
		);

		add_settings_section(
			'ivm_features',
			__( 'Editorial Features', 'interslide-vertical-menu' ),
			'__return_false',
			'interslide-vertical-menu'
		);

		add_settings_field(
			'breaking_enabled',
			__( 'Enable breaking bar', 'interslide-vertical-menu' ),
			array( $this, 'render_checkbox_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_breaking_enabled',
				'option_key' => 'breaking_enabled',
			)
		);

		add_settings_field(
			'breaking_text',
			__( 'Breaking text', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_breaking_text',
				'option_key' => 'breaking_text',
			)
		);

		add_settings_field(
			'breaking_badge_label',
			__( 'Breaking badge label', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_breaking_badge_label',
				'option_key' => 'breaking_badge_label',
			)
		);

		add_settings_field(
			'breaking_url',
			__( 'Breaking URL', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_breaking_url',
				'option_key' => 'breaking_url',
			)
		);

		add_settings_field(
			'live_enabled',
			__( 'Enable live badge', 'interslide-vertical-menu' ),
			array( $this, 'render_checkbox_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_live_enabled',
				'option_key' => 'live_enabled',
			)
		);

		add_settings_field(
			'live_text',
			__( 'Live text', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_live_text',
				'option_key' => 'live_text',
			)
		);

		add_settings_field(
			'live_url',
			__( 'Live URL', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_live_url',
				'option_key' => 'live_url',
			)
		);

		add_settings_field(
			'date_enabled',
			__( 'Show date', 'interslide-vertical-menu' ),
			array( $this, 'render_checkbox_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_date_enabled',
				'option_key' => 'date_enabled',
			)
		);

		add_settings_field(
			'newsletter_enabled',
			__( 'Enable newsletter link', 'interslide-vertical-menu' ),
			array( $this, 'render_checkbox_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_newsletter_enabled',
				'option_key' => 'newsletter_enabled',
			)
		);

		add_settings_field(
			'newsletter_text',
			__( 'Newsletter text', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_newsletter_text',
				'option_key' => 'newsletter_text',
			)
		);

		add_settings_field(
			'newsletter_url',
			__( 'Newsletter URL', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_newsletter_url',
				'option_key' => 'newsletter_url',
			)
		);

		add_settings_field(
			'account_enabled',
			__( 'Enable account link', 'interslide-vertical-menu' ),
			array( $this, 'render_checkbox_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_account_enabled',
				'option_key' => 'account_enabled',
			)
		);

		add_settings_field(
			'account_text',
			__( 'Account text', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_account_text',
				'option_key' => 'account_text',
			)
		);

		add_settings_field(
			'account_url',
			__( 'Account URL', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_account_url',
				'option_key' => 'account_url',
			)
		);

		add_settings_field(
			'footer_text',
			__( 'Footer text', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_features',
			array(
				'label_for'  => 'ivm_footer_text',
				'option_key' => 'footer_text',
			)
		);

		add_settings_field(
			'mobile_menu_id',
			__( 'Mobile menu', 'interslide-vertical-menu' ),
			array( $this, 'render_menu_select_field' ),
			'interslide-vertical-menu',
			'ivm_menu_items',
			array(
				'label_for'  => 'ivm_mobile_menu_id',
				'option_key' => 'mobile_menu_id',
			)
		);

		add_settings_field(
			'background_color',
			__( 'Background Color', 'interslide-vertical-menu' ),
			array( $this, 'render_color_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_background_color',
				'option_key' => 'background_color',
			)
		);

		add_settings_field(
			'divider_color',
			__( 'Divider Color', 'interslide-vertical-menu' ),
			array( $this, 'render_color_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_divider_color',
				'option_key' => 'divider_color',
			)
		);

		add_settings_field(
			'text_color',
			__( 'Text Color', 'interslide-vertical-menu' ),
			array( $this, 'render_color_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_text_color',
				'option_key' => 'text_color',
			)
		);

		add_settings_field(
			'font_family',
			__( 'Font Family', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_font_family',
				'option_key' => 'font_family',
			)
		);

		add_settings_field(
			'font_size',
			__( 'Base Font Size (px)', 'interslide-vertical-menu' ),
			array( $this, 'render_number_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_font_size',
				'option_key' => 'font_size',
				'min'        => 12,
				'max'        => 18,
			)
		);

		add_settings_field(
			'hover_color',
			__( 'Hover Color', 'interslide-vertical-menu' ),
			array( $this, 'render_color_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_hover_color',
				'option_key' => 'hover_color',
			)
		);

		add_settings_field(
			'border_radius',
			__( 'Border Radius (px)', 'interslide-vertical-menu' ),
			array( $this, 'render_number_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_border_radius',
				'option_key' => 'border_radius',
				'min'        => 0,
				'max'        => 16,
			)
		);

		add_settings_field(
			'link_padding_y',
			__( 'Link Padding Y (px)', 'interslide-vertical-menu' ),
			array( $this, 'render_number_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_link_padding_y',
				'option_key' => 'link_padding_y',
				'min'        => 4,
				'max'        => 16,
			)
		);

		add_settings_field(
			'link_padding_x',
			__( 'Link Padding X (px)', 'interslide-vertical-menu' ),
			array( $this, 'render_number_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_link_padding_x',
				'option_key' => 'link_padding_x',
				'min'        => 6,
				'max'        => 20,
			)
		);

		add_settings_field(
			'pill_color',
			__( 'Pill Color', 'interslide-vertical-menu' ),
			array( $this, 'render_color_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_pill_color',
				'option_key' => 'pill_color',
			)
		);

		add_settings_field(
			'panel_shadow',
			__( 'Panel Shadow (0/1)', 'interslide-vertical-menu' ),
			array( $this, 'render_number_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_panel_shadow',
				'option_key' => 'panel_shadow',
				'min'        => 0,
				'max'        => 1,
			)
		);

		add_settings_field(
			'header_bg',
			__( 'Mobile Header Background', 'interslide-vertical-menu' ),
			array( $this, 'render_color_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_header_bg',
				'option_key' => 'header_bg',
			)
		);

		add_settings_field(
			'header_text_color',
			__( 'Mobile Header Text', 'interslide-vertical-menu' ),
			array( $this, 'render_color_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_header_text_color',
				'option_key' => 'header_text_color',
			)
		);

		add_settings_field(
			'toggle_color',
			__( 'Toggle Icon Color', 'interslide-vertical-menu' ),
			array( $this, 'render_color_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_toggle_color',
				'option_key' => 'toggle_color',
			)
		);

		add_settings_field(
			'sidebar_width',
			__( 'Sidebar Width (px)', 'interslide-vertical-menu' ),
			array( $this, 'render_number_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_sidebar_width',
				'option_key' => 'sidebar_width',
				'min'        => 200,
				'max'        => 400,
			)
		);

		add_settings_field(
			'mobile_breakpoint',
			__( 'Mobile Breakpoint (px)', 'interslide-vertical-menu' ),
			array( $this, 'render_number_field' ),
			'interslide-vertical-menu',
			'ivm_style',
			array(
				'label_for'  => 'ivm_mobile_breakpoint',
				'option_key' => 'mobile_breakpoint',
				'min'        => 600,
				'max'        => 1400,
			)
		);

		add_settings_section(
			'ivm_search',
			__( 'Search', 'interslide-vertical-menu' ),
			'__return_false',
			'interslide-vertical-menu'
		);

		add_settings_field(
			'search_mode',
			__( 'Search Mode', 'interslide-vertical-menu' ),
			array( $this, 'render_search_mode_field' ),
			'interslide-vertical-menu',
			'ivm_search'
		);

		add_settings_field(
			'search_label',
			__( 'Search label', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_search',
			array(
				'label_for'  => 'ivm_search_label',
				'option_key' => 'search_label',
			)
		);

		add_settings_field(
			'search_url',
			__( 'Search URL', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_search',
			array(
				'label_for'  => 'ivm_search_url',
				'option_key' => 'search_url',
			)
		);

		add_settings_section(
			'ivm_edition',
			__( 'Edition Selector', 'interslide-vertical-menu' ),
			'__return_false',
			'interslide-vertical-menu'
		);

		add_settings_field(
			'edition_enabled',
			__( 'Enable Editions', 'interslide-vertical-menu' ),
			array( $this, 'render_checkbox_field' ),
			'interslide-vertical-menu',
			'ivm_edition',
			array(
				'label_for'  => 'ivm_edition_enabled',
				'option_key' => 'edition_enabled',
			)
		);

		add_settings_field(
			'edition_options',
			__( 'Editions', 'interslide-vertical-menu' ),
			array( $this, 'render_items_field' ),
			'interslide-vertical-menu',
			'ivm_edition',
			array(
				'option_key' => 'edition_options',
				'label'      => __( 'Label|URL (one per line).', 'interslide-vertical-menu' ),
			)
		);

		add_settings_field(
			'edition_default',
			__( 'Default Edition (index)', 'interslide-vertical-menu' ),
			array( $this, 'render_number_field' ),
			'interslide-vertical-menu',
			'ivm_edition',
			array(
				'label_for'  => 'ivm_edition_default',
				'option_key' => 'edition_default',
				'min'        => 0,
				'max'        => 4,
			)
		);

		add_settings_field(
			'edition_label',
			__( 'Edition label', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_edition',
			array(
				'label_for'  => 'ivm_edition_label',
				'option_key' => 'edition_label',
			)
		);

		add_settings_section(
			'ivm_layout',
			__( 'Layout Integration', 'interslide-vertical-menu' ),
			'__return_false',
			'interslide-vertical-menu'
		);

		add_settings_field(
			'hide_theme_menu',
			__( 'Hide existing theme menu', 'interslide-vertical-menu' ),
			array( $this, 'render_checkbox_field' ),
			'interslide-vertical-menu',
			'ivm_layout',
			array(
				'label_for'   => 'ivm_hide_theme_menu',
				'option_key'  => 'hide_theme_menu',
				'description' => __( 'Hide the current theme header/navigation so the vertical menu becomes primary.', 'interslide-vertical-menu' ),
			)
		);

		add_settings_field(
			'hide_selectors',
			__( 'Theme menu selectors', 'interslide-vertical-menu' ),
			array( $this, 'render_text_field' ),
			'interslide-vertical-menu',
			'ivm_layout',
			array(
				'label_for'  => 'ivm_hide_selectors',
				'option_key' => 'hide_selectors',
			)
		);

		add_settings_section(
			'ivm_advanced',
			__( 'Advanced', 'interslide-vertical-menu' ),
			'__return_false',
			'interslide-vertical-menu'
		);

		add_settings_field(
			'custom_css',
			__( 'Custom CSS', 'interslide-vertical-menu' ),
			array( $this, 'render_textarea_field' ),
			'interslide-vertical-menu',
			'ivm_advanced',
			array(
				'label_for'  => 'ivm_custom_css',
				'option_key' => 'custom_css',
				'rows'       => 6,
			)
		);

		add_settings_section(
			'ivm_cleanup',
			__( 'Cleanup', 'interslide-vertical-menu' ),
			'__return_false',
			'interslide-vertical-menu'
		);

		add_settings_field(
			'cleanup_on_uninstall',
			__( 'Delete data on uninstall', 'interslide-vertical-menu' ),
			array( $this, 'render_checkbox_field' ),
			'interslide-vertical-menu',
			'ivm_cleanup',
			array(
				'label_for'   => 'ivm_cleanup_on_uninstall',
				'option_key'  => 'cleanup_on_uninstall',
				'description' => __( 'Remove settings when the plugin is uninstalled.', 'interslide-vertical-menu' ),
			)
		);
	}

	public function sanitize_settings( $input ) {
		$defaults = $this->get_default_settings();
		$output   = array();

		$output['enabled']              = isset( $input['enabled'] ) ? 1 : 0;
		$output['display_mode']         = $this->sanitize_display_mode( $input['display_mode'] ?? $defaults['display_mode'] );
		$output['logo_type']            = ( 'image' === ( $input['logo_type'] ?? '' ) ) ? 'image' : 'text';
		$output['logo_text']            = sanitize_text_field( $input['logo_text'] ?? $defaults['logo_text'] );
		$output['logo_image_url']       = esc_url_raw( $input['logo_image_url'] ?? '' );
		$output['logo_link']            = esc_url_raw( $input['logo_link'] ?? $defaults['logo_link'] );
		$output['pill_enabled']         = isset( $input['pill_enabled'] ) ? 1 : 0;
		$output['pill_text']            = sanitize_text_field( $input['pill_text'] ?? '' );
		$output['pill_url']             = esc_url_raw( $input['pill_url'] ?? '' );
		$output['primary_items']        = $this->sanitize_items( $input['primary_items'] ?? array(), true );
		$output['secondary_items']      = $this->sanitize_items( $input['secondary_items'] ?? array(), true );
		$output['bottom_items']         = $this->sanitize_items( $input['bottom_items'] ?? array(), false );
		$output['background_color']     = sanitize_hex_color( $input['background_color'] ?? $defaults['background_color'] );
		$output['divider_color']        = sanitize_hex_color( $input['divider_color'] ?? $defaults['divider_color'] );
		$output['text_color']           = sanitize_hex_color( $input['text_color'] ?? $defaults['text_color'] );
		$output['hover_color']          = sanitize_hex_color( $input['hover_color'] ?? $defaults['hover_color'] );
		$output['pill_color']           = sanitize_hex_color( $input['pill_color'] ?? $defaults['pill_color'] );
		$output['font_family']          = sanitize_text_field( $input['font_family'] ?? $defaults['font_family'] );
		$output['font_size']            = $this->sanitize_number( $input['font_size'] ?? $defaults['font_size'], 12, 20 );
		$output['link_padding_y']       = $this->sanitize_number( $input['link_padding_y'] ?? $defaults['link_padding_y'], 4, 20 );
		$output['link_padding_x']       = $this->sanitize_number( $input['link_padding_x'] ?? $defaults['link_padding_x'], 6, 24 );
		$output['border_radius']        = $this->sanitize_number( $input['border_radius'] ?? $defaults['border_radius'], 0, 20 );
		$output['panel_shadow']         = $this->sanitize_number( $input['panel_shadow'] ?? $defaults['panel_shadow'], 0, 1 );
		$output['header_bg']            = sanitize_hex_color( $input['header_bg'] ?? $defaults['header_bg'] );
		$output['header_text_color']    = sanitize_hex_color( $input['header_text_color'] ?? $defaults['header_text_color'] );
		$output['toggle_color']         = sanitize_hex_color( $input['toggle_color'] ?? $defaults['toggle_color'] );
		$output['sidebar_width']        = $this->sanitize_number( $input['sidebar_width'] ?? $defaults['sidebar_width'], 200, 400 );
		$output['mobile_breakpoint']    = $this->sanitize_number( $input['mobile_breakpoint'] ?? $defaults['mobile_breakpoint'], 600, 1600 );
		$output['search_mode']          = ( 'inline' === ( $input['search_mode'] ?? '' ) ) ? 'inline' : 'link';
		$output['search_url']           = esc_url_raw( $input['search_url'] ?? $defaults['search_url'] );
		$output['edition_enabled']      = isset( $input['edition_enabled'] ) ? 1 : 0;
		$output['edition_options']      = $this->sanitize_items( $input['edition_options'] ?? array(), false );
		$output['edition_default']      = $this->sanitize_number( $input['edition_default'] ?? $defaults['edition_default'], 0, 5 );
		$output['breaking_enabled']     = isset( $input['breaking_enabled'] ) ? 1 : 0;
		$output['breaking_text']        = sanitize_text_field( $input['breaking_text'] ?? $defaults['breaking_text'] );
		$output['breaking_url']         = esc_url_raw( $input['breaking_url'] ?? $defaults['breaking_url'] );
		$output['live_enabled']         = isset( $input['live_enabled'] ) ? 1 : 0;
		$output['live_text']            = sanitize_text_field( $input['live_text'] ?? $defaults['live_text'] );
		$output['live_url']             = esc_url_raw( $input['live_url'] ?? $defaults['live_url'] );
		$output['date_enabled']         = isset( $input['date_enabled'] ) ? 1 : 0;
		$output['newsletter_enabled']   = isset( $input['newsletter_enabled'] ) ? 1 : 0;
		$output['newsletter_text']      = sanitize_text_field( $input['newsletter_text'] ?? $defaults['newsletter_text'] );
		$output['newsletter_url']       = esc_url_raw( $input['newsletter_url'] ?? $defaults['newsletter_url'] );
		$output['utility_links']        = $this->sanitize_items( $input['utility_links'] ?? array(), false );
		$output['trending_topics']      = $this->sanitize_items( $input['trending_topics'] ?? array(), false );
		$output['trending_label']       = sanitize_text_field( $input['trending_label'] ?? $defaults['trending_label'] );
		$output['account_enabled']      = isset( $input['account_enabled'] ) ? 1 : 0;
		$output['account_text']         = sanitize_text_field( $input['account_text'] ?? $defaults['account_text'] );
		$output['account_url']          = esc_url_raw( $input['account_url'] ?? $defaults['account_url'] );
		$output['footer_text']          = wp_kses_post( $input['footer_text'] ?? $defaults['footer_text'] );
		$output['breaking_badge_label'] = sanitize_text_field( $input['breaking_badge_label'] ?? $defaults['breaking_badge_label'] );
		$output['search_label']         = sanitize_text_field( $input['search_label'] ?? $defaults['search_label'] );
		$output['edition_label']        = sanitize_text_field( $input['edition_label'] ?? $defaults['edition_label'] );
		$output['section_order']        = $this->sanitize_section_order( $input['section_order'] ?? $defaults['section_order'] );
		$output['bottom_sections']      = $this->sanitize_bottom_sections( $input['bottom_sections'] ?? $defaults['bottom_sections'] );
		$output['mobile_menu_id']       = absint( $input['mobile_menu_id'] ?? 0 );
		$output['use_wp_menus']         = isset( $input['use_wp_menus'] ) ? 1 : 0;
		$output['primary_menu_id']      = absint( $input['primary_menu_id'] ?? 0 );
		$output['secondary_menu_id']    = absint( $input['secondary_menu_id'] ?? 0 );
		$output['bottom_menu_id']       = absint( $input['bottom_menu_id'] ?? 0 );
		$output['hide_theme_menu']      = isset( $input['hide_theme_menu'] ) ? 1 : 0;
		$output['hide_selectors']       = $this->sanitize_selectors( $input['hide_selectors'] ?? $defaults['hide_selectors'] );
		$output['custom_css']           = sanitize_textarea_field( $input['custom_css'] ?? $defaults['custom_css'] );
		$output['cleanup_on_uninstall'] = isset( $input['cleanup_on_uninstall'] ) ? 1 : 0;

		return $output;
	}

	private function sanitize_display_mode( $value ) {
		$allowed = array( 'global', 'shortcode', 'block' );
		if ( in_array( $value, $allowed, true ) ) {
			return $value;
		}
		return 'global';
	}

	private function sanitize_number( $value, $min, $max ) {
		$value = intval( $value );
		if ( $value < $min ) {
			return $min;
		}
		if ( $value > $max ) {
			return $max;
		}
		return $value;
	}

	private function sanitize_selectors( $value ) {
		$value = sanitize_text_field( $value );
		$value = preg_replace( '/[^\\w\\s\\#\\.\\,\\-\\[\\]\\=\\\"\\:\\*\\>\\+\\~\\(\\)\\@]/', '', $value );
		return trim( $value );
	}

	private function get_section_keys() {
		return array(
			'utility',
			'meta',
			'primary',
			'trending',
			'divider',
			'secondary',
			'search',
			'bottom',
			'newsletter',
			'account',
			'edition',
			'footer',
		);
	}

	private function get_section_labels() {
		return array(
			'utility'    => __( 'Utility links', 'interslide-vertical-menu' ),
			'meta'       => __( 'Breaking/live/date', 'interslide-vertical-menu' ),
			'primary'    => __( 'Primary menu', 'interslide-vertical-menu' ),
			'trending'   => __( 'Trending topics', 'interslide-vertical-menu' ),
			'divider'    => __( 'Divider', 'interslide-vertical-menu' ),
			'secondary'  => __( 'Secondary menu', 'interslide-vertical-menu' ),
			'search'     => __( 'Search', 'interslide-vertical-menu' ),
			'bottom'     => __( 'Bottom menu', 'interslide-vertical-menu' ),
			'newsletter' => __( 'Newsletter link', 'interslide-vertical-menu' ),
			'account'    => __( 'Account link', 'interslide-vertical-menu' ),
			'edition'    => __( 'Edition selector', 'interslide-vertical-menu' ),
			'footer'     => __( 'Footer text', 'interslide-vertical-menu' ),
		);
	}

	private function get_section_order_tokens( $settings ) {
		$defaults = $this->get_default_settings();
		$order = $settings['section_order'] ?? '';
		$order = $this->sanitize_section_order( $order );
		if ( '' === $order ) {
			$order = $this->sanitize_section_order( $defaults['section_order'] );
		}
		return array_filter( array_map( 'trim', explode( ',', $order ) ) );
	}

	private function get_section_order( $settings ) {
		$items = $this->get_section_order_tokens( $settings );
		$filtered = array();
		$seen = array();
		foreach ( $items as $item ) {
			if ( 'divider' === $item ) {
				$filtered[] = $item;
				continue;
			}
			if ( isset( $seen[ $item ] ) ) {
				continue;
			}
			$seen[ $item ] = true;
			$filtered[] = $item;
		}
		return $filtered;
	}

	private function sanitize_section_order( $value ) {
		$allowed = $this->get_section_keys();
		$items = array();
		if ( is_string( $value ) ) {
			$items = array_map( 'trim', explode( ',', $value ) );
		} elseif ( is_array( $value ) ) {
			$items = $value;
		}
		$items = array_filter( $items, function ( $item ) use ( $allowed ) {
			return in_array( $item, $allowed, true );
		} );
		return implode( ',', $items );
	}

	private function sanitize_bottom_sections( $value ) {
		$allowed = $this->get_section_keys();
		$items = array();
		if ( is_array( $value ) ) {
			$items = $value;
		} elseif ( is_string( $value ) ) {
			$items = array_map( 'trim', explode( ',', $value ) );
		}
		$items = array_filter( $items, function ( $item ) use ( $allowed ) {
			return 'divider' !== $item && in_array( $item, $allowed, true );
		} );
		return array_values( array_unique( $items ) );
	}

	private function sanitize_items( $items, $allow_icon ) {
		$output = array();

		if ( is_string( $items ) ) {
			$items = $this->parse_items_string( $items, $allow_icon );
		}

		if ( ! is_array( $items ) ) {
			return $output;
		}

		foreach ( $items as $item ) {
			$label = sanitize_text_field( $item['label'] ?? '' );
			$url   = esc_url_raw( $item['url'] ?? '' );
			if ( '' === $label || '' === $url ) {
				continue;
			}
			$clean = array(
				'label' => $label,
				'url'   => $url,
			);
			if ( $allow_icon ) {
				$clean['icon'] = sanitize_key( $item['icon'] ?? '' );
			}
			$output[] = $clean;
		}

		return $output;
	}

	private function parse_items_string( $value, $allow_icon ) {
		$lines = preg_split( '/\r\n|\r|\n/', $value );
		$items = array();
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line ) );
			$items[] = array(
				'label' => $parts[0] ?? '',
				'url'   => $parts[1] ?? '',
				'icon'  => $allow_icon ? ( $parts[2] ?? '' ) : '',
			);
		}
		return $items;
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_interslide-vertical-menu' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script(
			'ivm-admin',
			IVM_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			IVM_VERSION,
			true
		);
	}

	public function enqueue_front_assets() {
		$settings = $this->get_settings();
		$should_enqueue = false;

		if ( $settings['enabled'] && 'global' === $settings['display_mode'] ) {
			$should_enqueue = true;
		}

		if ( $this->displayed ) {
			$should_enqueue = true;
		}

		if ( ! $should_enqueue ) {
			return;
		}

		wp_enqueue_style(
			'ivm-styles',
			IVM_PLUGIN_URL . 'assets/css/ivm-styles.css',
			array(),
			IVM_VERSION
		);

		wp_enqueue_script(
			'ivm-scripts',
			IVM_PLUGIN_URL . 'assets/js/ivm-scripts.js',
			array(),
			IVM_VERSION,
			true
		);

		$inline_css = sprintf(
			':root{--ivm-width:%dpx;}@media (max-width:%dpx){.ivm__panel{transform:translateX(-100%%);transition:transform .25s ease;position:fixed;padding-top:80px;}.ivm--open .ivm__panel{transform:translateX(0);}.ivm__mobile-header{display:flex;}.ivm-body{margin-left:0;}.ivm__mobile-only{display:block;}}',
			intval( $settings['sidebar_width'] ),
			intval( $settings['mobile_breakpoint'] )
		);
		if ( $settings['hide_theme_menu'] && $settings['hide_selectors'] ) {
			$inline_css .= sprintf( '%s{display:none !important;}', $settings['hide_selectors'] );
		}
		if ( ! empty( $settings['custom_css'] ) ) {
			$inline_css .= "\n" . $settings['custom_css'];
		}
		wp_add_inline_style( 'ivm-styles', $inline_css );

		wp_localize_script(
			'ivm-scripts',
			'ivmSettings',
			array(
				'breakpoint' => (int) $settings['mobile_breakpoint'],
			)
		);
	}

	public function filter_body_class( $classes ) {
		$settings = $this->get_settings();
		if ( $settings['enabled'] && 'global' === $settings['display_mode'] ) {
			$classes[] = 'ivm-body';
		}
		return $classes;
	}

	public function render_global_menu() {
		$settings = $this->get_settings();
		if ( ! $settings['enabled'] || 'global' !== $settings['display_mode'] ) {
			return;
		}
		$this->displayed = true;
		echo $this->get_menu_markup( $settings, array( 'mode' => 'fixed' ) );
	}

	public function shortcode_handler( $atts ) {
		$settings = $this->get_settings();
		if ( ! $settings['enabled'] ) {
			return '';
		}

		$atts = shortcode_atts(
			array(
				'mode'  => 'fixed',
				'width' => $settings['sidebar_width'],
				'theme' => 'light',
			),
			$atts,
			'interslide_vertical_menu'
		);

		$settings['sidebar_width'] = intval( $atts['width'] );
		$this->displayed = true;
		$this->enqueue_front_assets();
		return $this->get_menu_markup( $settings, $atts );
	}

	public function render_block( $attributes ) {
		$settings = $this->get_settings();
		if ( ! $settings['enabled'] ) {
			return '';
		}
		$this->displayed = true;
		$this->enqueue_front_assets();
		$mode = isset( $attributes['mode'] ) ? $attributes['mode'] : 'fixed';
		return $this->get_menu_markup( $settings, array( 'mode' => $mode ) );
	}

	public function render_settings_page() {
		$settings = $this->get_settings();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Interslide Vertical Menu', 'interslide-vertical-menu' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'ivm_settings_group' );
				do_settings_sections( 'interslide-vertical-menu' );
				submit_button();
				?>
			</form>
			<p><?php echo esc_html__( 'Available icons: flag, globe, leaf, robot, ticket, health, economy, sport, search, article, doc, podcast.', 'interslide-vertical-menu' ); ?></p>
			<p><?php echo esc_html__( 'To show an icon on a menu item, add a CSS class like ivm-icon-flag in Appearance → Menus.', 'interslide-vertical-menu' ); ?></p>
		</div>
		<?php
	}

	public function render_checkbox_field( $args ) {
		$settings = $this->get_settings();
		$key = $args['option_key'];
		$id  = 'ivm_' . $key;
		?>
		<label for="<?php echo esc_attr( $id ); ?>">
			<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $key . ']' ); ?>" value="1" <?php checked( $settings[ $key ], 1 ); ?> />
			<?php if ( ! empty( $args['description'] ) ) : ?>
				<?php echo esc_html( $args['description'] ); ?>
			<?php endif; ?>
		</label>
		<?php
	}

	public function render_text_field( $args ) {
		$settings = $this->get_settings();
		$key = $args['option_key'];
		$id  = 'ivm_' . $key;
		?>
		<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $settings[ $key ] ); ?>" class="regular-text" />
		<?php
	}

	public function render_number_field( $args ) {
		$settings = $this->get_settings();
		$key = $args['option_key'];
		$id  = 'ivm_' . $key;
		?>
		<input type="number" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $settings[ $key ] ); ?>" min="<?php echo esc_attr( $args['min'] ); ?>" max="<?php echo esc_attr( $args['max'] ); ?>" />
		<?php
	}

	public function render_color_field( $args ) {
		$settings = $this->get_settings();
		$key = $args['option_key'];
		$id  = 'ivm_' . $key;
		?>
		<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $settings[ $key ] ); ?>" class="regular-text" />
		<?php
	}

	public function render_textarea_field( $args ) {
		$settings = $this->get_settings();
		$key = $args['option_key'];
		$id  = 'ivm_' . $key;
		$rows = isset( $args['rows'] ) ? (int) $args['rows'] : 4;
		?>
		<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $key . ']' ); ?>" rows="<?php echo esc_attr( $rows ); ?>" class="large-text code"><?php echo esc_textarea( $settings[ $key ] ); ?></textarea>
		<?php
	}

	public function render_section_order_field() {
		$settings = $this->get_settings();
		$order_items = $this->get_section_order_tokens( $settings );
		$labels = $this->get_section_labels();
		$used = array();
		$items = array();

		foreach ( $order_items as $item ) {
			if ( ! isset( $labels[ $item ] ) ) {
				continue;
			}
			if ( 'divider' !== $item ) {
				if ( isset( $used[ $item ] ) ) {
					continue;
				}
				$used[ $item ] = true;
			}
			$items[] = array(
				'key'     => $item,
				'label'   => $labels[ $item ],
				'checked' => true,
			);
		}

		foreach ( $labels as $key => $label ) {
			if ( 'divider' === $key || isset( $used[ $key ] ) ) {
				continue;
			}
			$items[] = array(
				'key'     => $key,
				'label'   => $label,
				'checked' => false,
			);
		}

		$id = 'ivm_section_order';
		?>
		<div class="ivm-section-order" data-target="<?php echo esc_attr( $id ); ?>" data-divider-label="<?php echo esc_attr__( 'Divider', 'interslide-vertical-menu' ); ?>" data-remove-label="<?php echo esc_attr__( 'Remove', 'interslide-vertical-menu' ); ?>">
			<style>
				.ivm-section-order__list{margin:0;padding:0;list-style:none;max-width:520px;}
				.ivm-section-order__item{display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #e5e5e5;}
				.ivm-section-order__handle{color:#888;}
				.ivm-section-order__actions{margin-left:auto;display:inline-flex;gap:6px;}
				.ivm-section-order__remove{margin-left:auto;}
			</style>
			<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->option_name . '[section_order]' ); ?>" value="<?php echo esc_attr( $settings['section_order'] ); ?>" />
			<ul class="ivm-section-order__list">
				<?php foreach ( $items as $item ) : ?>
					<li class="ivm-section-order__item" data-key="<?php echo esc_attr( $item['key'] ); ?>">
						<span class="ivm-section-order__handle" aria-hidden="true">⋮⋮</span>
						<?php if ( 'divider' === $item['key'] ) : ?>
							<span class="ivm-section-order__label"><?php echo esc_html( $item['label'] ); ?></span>
							<button type="button" class="button-link ivm-section-order__remove"><?php echo esc_html__( 'Remove', 'interslide-vertical-menu' ); ?></button>
						<?php else : ?>
							<label>
								<input type="checkbox" class="ivm-section-order__toggle" <?php checked( $item['checked'] ); ?> />
								<span class="ivm-section-order__label"><?php echo esc_html( $item['label'] ); ?></span>
							</label>
						<?php endif; ?>
						<span class="ivm-section-order__actions">
							<button type="button" class="button-link ivm-section-order__up" aria-label="<?php echo esc_attr__( 'Move up', 'interslide-vertical-menu' ); ?>">↑</button>
							<button type="button" class="button-link ivm-section-order__down" aria-label="<?php echo esc_attr__( 'Move down', 'interslide-vertical-menu' ); ?>">↓</button>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
			<button type="button" class="button ivm-section-order__add-divider"><?php echo esc_html__( 'Add divider', 'interslide-vertical-menu' ); ?></button>
			<p class="description"><?php echo esc_html__( 'Toggle sections to show/hide them, then use the arrows to reorder.', 'interslide-vertical-menu' ); ?></p>
		</div>
		<?php
	}

	public function render_bottom_sections_field() {
		$settings = $this->get_settings();
		$selected = $settings['bottom_sections'] ?? array();
		$labels = $this->get_section_labels();
		?>
		<fieldset>
			<?php foreach ( $labels as $key => $label ) : ?>
				<?php if ( 'divider' === $key ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php $id = 'ivm_bottom_section_' . $key; ?>
				<label for="<?php echo esc_attr( $id ); ?>" style="display:block;margin-bottom:6px;">
					<input
						type="checkbox"
						id="<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( $this->option_name . '[bottom_sections][]' ); ?>"
						value="<?php echo esc_attr( $key ); ?>"
						<?php checked( in_array( $key, $selected, true ) ); ?>
					/>
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<?php
	}

	public function render_items_field( $args ) {
		$settings = $this->get_settings();
		$key = $args['option_key'];
		$id  = 'ivm_' . $key;
		$items = $settings[ $key ];
		if ( is_array( $items ) ) {
			$lines = array();
			foreach ( $items as $item ) {
				$line = $item['label'] . '|' . $item['url'];
				if ( isset( $item['icon'] ) && '' !== $item['icon'] ) {
					$line .= '|' . $item['icon'];
				}
				$lines[] = $line;
			}
			$items = implode( "\n", $lines );
		}
		?>
		<p><em><?php echo esc_html( $args['label'] ); ?></em></p>
		<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $key . ']' ); ?>" rows="6" cols="60" class="large-text code"><?php echo esc_textarea( $items ); ?></textarea>
		<?php
	}

	public function render_menu_select_field( $args ) {
		$settings = $this->get_settings();
		$key = $args['option_key'];
		$id  = 'ivm_' . $key;
		$menus = wp_get_nav_menus();
		?>
		<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->option_name . '[' . $key . ']' ); ?>">
			<option value="0"><?php echo esc_html__( 'Select a menu', 'interslide-vertical-menu' ); ?></option>
			<?php foreach ( $menus as $menu ) : ?>
				<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $settings[ $key ], $menu->term_id ); ?>>
					<?php echo esc_html( $menu->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public function render_logo_type_field() {
		$settings = $this->get_settings();
		?>
		<select name="<?php echo esc_attr( $this->option_name . '[logo_type]' ); ?>">
			<option value="text" <?php selected( $settings['logo_type'], 'text' ); ?>><?php echo esc_html__( 'Text', 'interslide-vertical-menu' ); ?></option>
			<option value="image" <?php selected( $settings['logo_type'], 'image' ); ?>><?php echo esc_html__( 'Image', 'interslide-vertical-menu' ); ?></option>
		</select>
		<?php
	}

	public function render_logo_image_field() {
		$settings = $this->get_settings();
		?>
		<div class="ivm-logo-upload">
			<input type="text" id="ivm_logo_image_url" name="<?php echo esc_attr( $this->option_name . '[logo_image_url]' ); ?>" value="<?php echo esc_attr( $settings['logo_image_url'] ); ?>" class="regular-text" />
			<button type="button" class="button ivm-upload-button"><?php echo esc_html__( 'Upload', 'interslide-vertical-menu' ); ?></button>
		</div>
		<?php
	}

	public function render_display_mode_field() {
		$settings = $this->get_settings();
		?>
		<select name="<?php echo esc_attr( $this->option_name . '[display_mode]' ); ?>">
			<option value="global" <?php selected( $settings['display_mode'], 'global' ); ?>><?php echo esc_html__( 'Global (auto inject)', 'interslide-vertical-menu' ); ?></option>
			<option value="shortcode" <?php selected( $settings['display_mode'], 'shortcode' ); ?>><?php echo esc_html__( 'Shortcode only', 'interslide-vertical-menu' ); ?></option>
			<option value="block" <?php selected( $settings['display_mode'], 'block' ); ?>><?php echo esc_html__( 'Block only', 'interslide-vertical-menu' ); ?></option>
		</select>
		<?php
	}

	public function render_search_mode_field() {
		$settings = $this->get_settings();
		?>
		<select name="<?php echo esc_attr( $this->option_name . '[search_mode]' ); ?>">
			<option value="link" <?php selected( $settings['search_mode'], 'link' ); ?>><?php echo esc_html__( 'Link', 'interslide-vertical-menu' ); ?></option>
			<option value="inline" <?php selected( $settings['search_mode'], 'inline' ); ?>><?php echo esc_html__( 'Inline Search Field', 'interslide-vertical-menu' ); ?></option>
		</select>
		<?php
	}

	private function get_default_primary_items() {
		return array(
			array( 'label' => __( 'Maroc', 'interslide-vertical-menu' ), 'url' => home_url( '/maroc/' ), 'icon' => 'flag' ),
			array( 'label' => __( 'International', 'interslide-vertical-menu' ), 'url' => home_url( '/international/' ), 'icon' => 'globe' ),
			array( 'label' => __( 'Environnement', 'interslide-vertical-menu' ), 'url' => home_url( '/environnement/' ), 'icon' => 'leaf' ),
			array( 'label' => __( 'Technologie', 'interslide-vertical-menu' ), 'url' => home_url( '/technologie/' ), 'icon' => 'robot' ),
			array( 'label' => __( 'Culture', 'interslide-vertical-menu' ), 'url' => home_url( '/culture/' ), 'icon' => 'ticket' ),
			array( 'label' => __( 'Santé', 'interslide-vertical-menu' ), 'url' => home_url( '/sante/' ), 'icon' => 'health' ),
			array( 'label' => __( 'Économie', 'interslide-vertical-menu' ), 'url' => home_url( '/economie/' ), 'icon' => 'economy' ),
			array( 'label' => __( 'Sport', 'interslide-vertical-menu' ), 'url' => home_url( '/sport/' ), 'icon' => 'sport' ),
		);
	}

	private function get_default_secondary_items() {
		return array(
			array( 'label' => __( 'Articles', 'interslide-vertical-menu' ), 'url' => home_url( '/articles/' ), 'icon' => 'article' ),
			array( 'label' => __( 'Documentaires', 'interslide-vertical-menu' ), 'url' => home_url( '/documentaires/' ), 'icon' => 'doc' ),
			array( 'label' => __( 'Podcast', 'interslide-vertical-menu' ), 'url' => home_url( '/podcast/' ), 'icon' => 'podcast' ),
			array( 'label' => __( 'Jeux concours', 'interslide-vertical-menu' ), 'url' => home_url( '/jeux-concours/' ), 'icon' => 'ticket' ),
			array( 'label' => __( 'Rechercher', 'interslide-vertical-menu' ), 'url' => home_url( '/recherche/' ), 'icon' => 'search' ),
		);
	}

	private function get_default_bottom_items() {
		return array(
			array( 'label' => __( 'Annonceurs', 'interslide-vertical-menu' ), 'url' => 'https://interslide.afrique.media/annonceurs/' ),
			array( 'label' => __( 'Nous rejoindre', 'interslide-vertical-menu' ), 'url' => 'https://interslide.media/nous-rejoindre/' ),
		);
	}

	private function get_default_utility_links() {
		return array(
			array( 'label' => __( 'Contact', 'interslide-vertical-menu' ), 'url' => home_url( '/contact/' ) ),
			array( 'label' => __( 'À propos', 'interslide-vertical-menu' ), 'url' => home_url( '/a-propos/' ) ),
		);
	}

	private function get_default_trending_topics() {
		return array(
			array( 'label' => __( 'Économie', 'interslide-vertical-menu' ), 'url' => home_url( '/economie/' ) ),
			array( 'label' => __( 'Tech', 'interslide-vertical-menu' ), 'url' => home_url( '/technologie/' ) ),
			array( 'label' => __( 'Culture', 'interslide-vertical-menu' ), 'url' => home_url( '/culture/' ) ),
		);
	}

	private function get_default_editions() {
		return array(
			array( 'label' => __( 'Édition Maroc', 'interslide-vertical-menu' ), 'url' => 'https://interslide.ma/' ),
			array( 'label' => __( 'Édition France', 'interslide-vertical-menu' ), 'url' => 'https://interslide.fr/' ),
			array( 'label' => __( 'Édition International', 'interslide-vertical-menu' ), 'url' => 'https://interslide.com/' ),
			array( 'label' => __( 'Édition Afrique', 'interslide-vertical-menu' ), 'url' => 'https://interslide.afrique.media/' ),
		);
	}

	private function get_icon_svg( $key ) {
		$icons = array(
			'flag'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h2l1 2h10l-2 4 2 4H8l-1-2H5v9H3V3h2zm3 2 1 2h8l-1 2 1 2H9l-1-2H5V5h3z"/></svg>',
			'globe'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm7.5 9h-3.1a15.7 15.7 0 00-1.4-6.1A8 8 0 0119.5 11zM12 4c1 1.5 1.8 3.5 2.2 7H9.8C10.2 7.5 11 5.5 12 4zm-3.5 1a15.7 15.7 0 00-1.4 6H4.5A8 8 0 018.5 5zM4.5 13h3.1a15.7 15.7 0 001.4 6A8 8 0 014.5 13zM12 20c-1-1.5-1.8-3.5-2.2-7h4.4c-.4 3.5-1.2 5.5-2.2 7zm3.5-1a15.7 15.7 0 001.4-6h3.1a8 8 0 01-4.5 6z"/></svg>',
			'leaf'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4c-6.5 0-12.3 3.4-14 9-.6 2-.6 4.1-.1 6.1 2-2.4 4.4-4.4 7.4-5.8l.7 1.4c-3.5 1.6-6.1 4.2-8 7.3l-1.4-.7C2.7 16.5 2.9 12.3 4.3 9 6.4 3.8 11.7 1 20 1v3z"/></svg>',
			'robot'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 2h2v2h3a4 4 0 014 4v6a4 4 0 01-4 4H8a4 4 0 01-4-4V8a4 4 0 014-4h3V2zm-1 6a2 2 0 100 4 2 2 0 000-4zm6 0a2 2 0 100 4 2 2 0 000-4zM8 16h8v2H8v-2z"/></svg>',
			'ticket'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6a2 2 0 012-2h14a2 2 0 012 2v3a2 2 0 100 4v3a2 2 0 01-2 2H5a2 2 0 01-2-2v-3a2 2 0 100-4V6zm4 2h2v2H7V8zm0 6h2v2H7v-2zm4-6h6v2h-6V8zm0 6h6v2h-6v-2z"/></svg>',
			'health'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7-4.4-9.5-8.9A5.5 5.5 0 0112 5a5.5 5.5 0 019.5 7.1C19 16.6 12 21 12 21z"/></svg>',
			'economy' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 5V6h-2v2a3 3 0 000 6h2a1 1 0 110 2H9v2h2v1h2v-1a3 3 0 000-6h-2a1 1 0 110-2h4V7h-2z"/></svg>',
			'sport'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 2a8 8 0 015.7 13.7l-2.3-2.3a4 4 0 00-6.8-4.4L6.3 9A8 8 0 0112 4zm-5.7 6.3l2.3 2.3a4 4 0 006.8 4.4l2.3 2.3A8 8 0 016.3 10.3z"/></svg>',
			'search'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 2a8 8 0 105.3 14l4.4 4.4 1.4-1.4-4.4-4.4A8 8 0 0010 2zm0 2a6 6 0 110 12 6 6 0 010-12z"/></svg>',
			'article' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2zm2 4v2h10V7H7zm0 4v2h10v-2H7zm0 4v2h6v-2H7z"/></svg>',
			'doc'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h9l5 5v15a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2zm8 1.5V8h4.5L14 3.5zM8 12h8v2H8v-2zm0 4h8v2H8v-2z"/></svg>',
			'podcast' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a8 8 0 00-5 14.2V22h3v-4h4v4h3v-5.8A8 8 0 0012 2zm0 2a6 6 0 014.7 9.7l-.7.6V16h-2v-2h-4v2H8v-1.7l-.7-.6A6 6 0 0112 4zm0 4a2 2 0 00-2 2 2 2 0 004 0 2 2 0 00-2-2z"/></svg>',
		);

		return $icons[ $key ] ?? '';
	}

	private function render_section_group( $section_order, $section_markup, $bottom_sections, $is_bottom ) {
		$output = '';
		$sections = array();
		foreach ( $section_order as $section ) {
			$in_bottom = in_array( $section, $bottom_sections, true );
			if ( $is_bottom === $in_bottom ) {
				$sections[] = $section;
			}
		}

		$rendered_any = false;
		$total = count( $sections );
		foreach ( $sections as $index => $section ) {
			$markup = $section_markup[ $section ] ?? '';
			if ( 'divider' === $section ) {
				if ( ! $rendered_any ) {
					continue;
				}
				$has_next = false;
				for ( $next = $index + 1; $next < $total; $next++ ) {
					$next_section = $sections[ $next ];
					$next_markup = $section_markup[ $next_section ] ?? '';
					if ( 'divider' !== $next_section && '' !== $next_markup ) {
						$has_next = true;
						break;
					}
				}
				if ( ! $has_next ) {
					continue;
				}
				$output .= $markup;
				continue;
			}
			if ( '' === $markup ) {
				continue;
			}
			$rendered_any = true;
			$output .= $markup;
		}
		return $output;
	}

	private function render_section_markup( $settings, $section ) {
		switch ( $section ) {
			case 'utility':
				if ( empty( $settings['utility_links'] ) ) {
					return '';
				}
				return $this->render_links_list( $settings['utility_links'], 'ivm__utility' );
			case 'meta':
				if ( ! $settings['breaking_enabled'] && ! $settings['live_enabled'] && ! $settings['date_enabled'] ) {
					return '';
				}
				$breaking_badge_label = $settings['breaking_badge_label'] ? $settings['breaking_badge_label'] : __( 'Breaking', 'interslide-vertical-menu' );
				ob_start();
				?>
				<div class="ivm__meta">
					<?php if ( $settings['breaking_enabled'] && $settings['breaking_text'] ) : ?>
						<a class="ivm__breaking" href="<?php echo esc_url( $settings['breaking_url'] ); ?>">
							<span class="ivm__badge"><?php echo esc_html( $breaking_badge_label ); ?></span>
							<span class="ivm__breaking-text"><?php echo esc_html( $settings['breaking_text'] ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $settings['live_enabled'] && $settings['live_text'] ) : ?>
						<a class="ivm__live" href="<?php echo esc_url( $settings['live_url'] ); ?>">
							<span class="ivm__live-dot"></span>
							<span class="ivm__live-text"><?php echo esc_html( $settings['live_text'] ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $settings['date_enabled'] ) : ?>
						<span class="ivm__date"><?php echo esc_html( date_i18n( get_option( 'date_format' ) ) ); ?></span>
					<?php endif; ?>
				</div>
				<?php
				return ob_get_clean();
			case 'primary':
				ob_start();
				?>
				<div class="ivm__section">
					<?php echo $this->render_menu_section( $settings, 'primary', $settings['primary_items'] ); ?>
					<?php if ( $settings['mobile_menu_id'] ) : ?>
						<div class="ivm__mobile-only">
							<?php echo $this->render_menu_section( $settings, 'mobile', $settings['primary_items'] ); ?>
						</div>
					<?php endif; ?>
				</div>
				<?php
				return ob_get_clean();
			case 'trending':
				if ( empty( $settings['trending_topics'] ) ) {
					return '';
				}
				$trending_label = $settings['trending_label'] ? $settings['trending_label'] : __( 'Trending', 'interslide-vertical-menu' );
				ob_start();
				?>
				<div class="ivm__trending">
					<span class="ivm__trending-label"><?php echo esc_html( $trending_label ); ?></span>
					<div class="ivm__trending-items">
						<?php foreach ( $settings['trending_topics'] as $item ) : ?>
							<a class="ivm__chip" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
				return ob_get_clean();
			case 'divider':
				return '<hr class="ivm__divider" />';
			case 'secondary':
				ob_start();
				?>
				<div class="ivm__section">
					<?php echo $this->render_menu_section( $settings, 'secondary', $settings['secondary_items'] ); ?>
				</div>
				<?php
				return ob_get_clean();
			case 'search':
				$search_label = $settings['search_label'] ? $settings['search_label'] : __( 'Search', 'interslide-vertical-menu' );
				if ( 'link' === $settings['search_mode'] && $settings['search_url'] ) {
					ob_start();
					?>
					<ul class="ivm__list">
						<li>
							<a href="<?php echo esc_url( $settings['search_url'] ); ?>" class="ivm__link">
								<span class="ivm__icon" aria-hidden="true"><?php echo $this->get_icon_svg( 'search' ); ?></span>
								<span class="ivm__label"><?php echo esc_html( $search_label ); ?></span>
							</a>
						</li>
					</ul>
					<?php
					return ob_get_clean();
				}
				if ( 'inline' === $settings['search_mode'] ) {
					ob_start();
					?>
					<ul class="ivm__list">
						<li class="ivm__search">
							<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
								<label class="screen-reader-text" for="ivm-search-input"><?php echo esc_html( $search_label ); ?></label>
								<input id="ivm-search-input" type="search" name="s" placeholder="<?php echo esc_attr( $search_label . '…' ); ?>" />
							</form>
						</li>
					</ul>
					<?php
					return ob_get_clean();
				}
				return '';
			case 'bottom':
				ob_start();
				?>
				<div class="ivm__section">
					<?php echo $this->render_menu_section( $settings, 'bottom', $settings['bottom_items'] ); ?>
				</div>
				<?php
				return ob_get_clean();
			case 'newsletter':
				if ( ! $settings['newsletter_enabled'] || ! $settings['newsletter_text'] ) {
					return '';
				}
				ob_start();
				?>
				<div class="ivm__newsletter">
					<a class="ivm__newsletter-link" href="<?php echo esc_url( $settings['newsletter_url'] ); ?>">
						<?php echo esc_html( $settings['newsletter_text'] ); ?>
					</a>
				</div>
				<?php
				return ob_get_clean();
			case 'account':
				if ( ! $settings['account_enabled'] || ! $settings['account_text'] ) {
					return '';
				}
				return sprintf(
					'<a class="ivm__account" href="%s">%s</a>',
					esc_url( $settings['account_url'] ),
					esc_html( $settings['account_text'] )
				);
			case 'edition':
				if ( ! $settings['edition_enabled'] || empty( $settings['edition_options'] ) ) {
					return '';
				}
				$edition_label = $settings['edition_label'] ? $settings['edition_label'] : __( 'Édition', 'interslide-vertical-menu' );
				ob_start();
				?>
				<div class="ivm__edition">
					<label for="ivm-edition-select" class="ivm__edition-label"><?php echo esc_html( $edition_label ); ?></label>
					<select id="ivm-edition-select" class="ivm__edition-select">
						<?php foreach ( $settings['edition_options'] as $index => $option ) : ?>
							<option value="<?php echo esc_url( $option['url'] ); ?>" <?php selected( $settings['edition_default'], $index ); ?>><?php echo esc_html( $option['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php
				return ob_get_clean();
			case 'footer':
				if ( ! $settings['footer_text'] ) {
					return '';
				}
				return sprintf(
					'<div class="ivm__footer">%s</div>',
					wp_kses_post( $settings['footer_text'] )
				);
			default:
				return '';
		}
	}

	private function get_menu_markup( $settings, $atts ) {
		$mode = isset( $atts['mode'] ) ? $atts['mode'] : 'fixed';
		$mode = ( 'drawer' === $mode ) ? 'drawer' : 'fixed';
		$wrapper_classes = 'ivm ivm--' . $mode;
		$panel_id = 'ivm-panel-' . wp_rand( 1000, 9999 );
		$logo = '';
		if ( 'image' === $settings['logo_type'] && $settings['logo_image_url'] ) {
			$logo = '<img src="' . esc_url( $settings['logo_image_url'] ) . '" alt="' . esc_attr( $settings['logo_text'] ) . '" class="ivm__logo-image" />';
		} else {
			$logo = '<span class="ivm__logo-text">' . esc_html( $settings['logo_text'] ) . '</span>';
		}

		$inline_style = sprintf(
			'--ivm-width:%dpx;--ivm-bg:%s;--ivm-text:%s;--ivm-hover:%s;--ivm-pill:%s;--ivm-divider:%s;--ivm-font:%s;--ivm-font-size:%dpx;--ivm-link-pad-y:%dpx;--ivm-link-pad-x:%dpx;--ivm-radius:%dpx;--ivm-shadow:%s;--ivm-header-bg:%s;--ivm-header-text:%s;--ivm-toggle:%s;',
			intval( $settings['sidebar_width'] ),
			esc_attr( $settings['background_color'] ),
			esc_attr( $settings['text_color'] ),
			esc_attr( $settings['hover_color'] ),
			esc_attr( $settings['pill_color'] ),
			esc_attr( $settings['divider_color'] ),
			esc_attr( $settings['font_family'] ),
			intval( $settings['font_size'] ),
			intval( $settings['link_padding_y'] ),
			intval( $settings['link_padding_x'] ),
			intval( $settings['border_radius'] ),
			( 1 === (int) $settings['panel_shadow'] ) ? '0 10px 30px rgba(0,0,0,0.15)' : 'none',
			esc_attr( $settings['header_bg'] ),
			esc_attr( $settings['header_text_color'] ),
			esc_attr( $settings['toggle_color'] )
		);

		$section_order = $this->get_section_order( $settings );
		$bottom_sections = $settings['bottom_sections'] ?? array();
		$section_markup = array();
		foreach ( $section_order as $section ) {
			$section_markup[ $section ] = $this->render_section_markup( $settings, $section );
		}

		ob_start();
		?>
		<nav class="<?php echo esc_attr( $wrapper_classes ); ?>" style="<?php echo esc_attr( $inline_style ); ?>" aria-label="<?php echo esc_attr__( 'Interslide menu', 'interslide-vertical-menu' ); ?>">
				<div class="ivm__mobile-header">
					<button type="button" class="ivm__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
						<span class="ivm__toggle-icon" aria-hidden="true"></span>
						<span class="screen-reader-text"><?php echo esc_html__( 'Open menu', 'interslide-vertical-menu' ); ?></span>
					</button>
					<a class="ivm__mobile-logo" href="<?php echo esc_url( $settings['logo_link'] ); ?>">
						<?php echo $logo; ?>
					</a>
				</div>
			<div class="ivm__overlay" tabindex="-1" hidden></div>
			<div class="ivm__panel" id="<?php echo esc_attr( $panel_id ); ?>" role="dialog" aria-modal="true" aria-hidden="<?php echo esc_attr( 'fixed' === $mode ? 'false' : 'true' ); ?>">
				<div class="ivm__header">
					<a class="ivm__logo" href="<?php echo esc_url( $settings['logo_link'] ); ?>">
						<?php echo $logo; ?>
					</a>
					<?php if ( $settings['pill_enabled'] && $settings['pill_text'] ) : ?>
						<a class="ivm__pill" href="<?php echo esc_url( $settings['pill_url'] ); ?>">
							<?php echo esc_html( $settings['pill_text'] ); ?>
						</a>
					<?php endif; ?>
				</div>
				<div class="ivm__content">
					<div class="ivm__content-main">
						<?php echo $this->render_section_group( $section_order, $section_markup, $bottom_sections, false ); ?>
					</div>
					<div class="ivm__content-bottom">
						<?php echo $this->render_section_group( $section_order, $section_markup, $bottom_sections, true ); ?>
					</div>
				</div>
			</div>
		</nav>
		<?php
		return ob_get_clean();
	}

	private function render_menu_section( $settings, $section, $fallback_items ) {
		$menu_id = 0;
		if ( 'primary' === $section ) {
			$menu_id = (int) $settings['primary_menu_id'];
		} elseif ( 'secondary' === $section ) {
			$menu_id = (int) $settings['secondary_menu_id'];
		} elseif ( 'bottom' === $section ) {
			$menu_id = (int) $settings['bottom_menu_id'];
		} elseif ( 'mobile' === $section ) {
			$menu_id = (int) $settings['mobile_menu_id'];
		}

		if ( ( $settings['use_wp_menus'] || 'mobile' === $section ) && $menu_id ) {
			return wp_nav_menu(
				array(
					'menu'        => $menu_id,
					'container'   => false,
					'echo'        => false,
					'menu_class'  => 'ivm__list',
					'fallback_cb' => '__return_false',
					'walker'      => new Interslide_Vertical_Menu_Walker( $this ),
				)
			);
		}

		ob_start();
		?>
		<ul class="ivm__list">
			<?php foreach ( $fallback_items as $item ) : ?>
				<?php $emoji_class = ! empty( $item['icon'] ) ? ' ivm-emoji-' . esc_attr( $item['icon'] ) : ''; ?>
				<li>
					<a href="<?php echo esc_url( $item['url'] ); ?>" class="ivm__link<?php echo esc_attr( $emoji_class ); ?>">
						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<span class="ivm__icon" aria-hidden="true"><?php echo $this->get_icon_svg( $item['icon'] ); ?></span>
						<?php endif; ?>
						<span class="ivm__label"><?php echo esc_html( $item['label'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		return ob_get_clean();
	}

	private function render_links_list( $items, $class ) {
		ob_start();
		?>
		<ul class="<?php echo esc_attr( $class ); ?>">
			<?php foreach ( $items as $item ) : ?>
				<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
		<?php
		return ob_get_clean();
	}
}

class Interslide_Vertical_Menu_Walker extends Walker_Nav_Menu {
	private $plugin;

	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$icon = '';
		$link_classes = array( 'ivm__link' );
		$item_classes = array();
		if ( ! empty( $item->classes ) && is_array( $item->classes ) ) {
			foreach ( $item->classes as $class ) {
				$clean_class = sanitize_html_class( $class );
				if ( '' !== $clean_class ) {
					$item_classes[] = $clean_class;
					$link_classes[] = $clean_class;
				}
				if ( 0 === strpos( $class, 'ivm-icon-' ) ) {
					$icon_key = substr( $class, strlen( 'ivm-icon-' ) );
					$icon = $this->plugin->get_icon_svg( sanitize_key( $icon_key ) );
					break;
				}
			}
		}

		$item_class_attr = ! empty( $item_classes ) ? ' class="' . esc_attr( implode( ' ', $item_classes ) ) . '"' : '';
		$output .= '<li' . $item_class_attr . '>';
		$output .= '<a class="' . esc_attr( implode( ' ', array_unique( $link_classes ) ) ) . '" href="' . esc_url( $item->url ) . '">';
		if ( $icon ) {
			$output .= '<span class="ivm__icon" aria-hidden="true">' . $icon . '</span>';
		}
		$output .= '<span class="ivm__label">' . esc_html( $item->title ) . '</span>';
		$output .= '</a>';
		$output .= '</li>';
	}
}
