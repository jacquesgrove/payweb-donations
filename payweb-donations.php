<?php
/*
Plugin Name: PayWeb Donations
Plugin URI: http://www.jacquesgrove.co.za/
Description: Easy and simple setup and insertion of PayWeb donate buttons with a shortcode or through a sidebar Widget. Donation purpose can be set for each button.
Author:  Jacques Grove
Author URI: http://www.jacquesgrove.co.za/
Version: 1.0.0
License: GPLv2 or later
Text Domain: payweb-donations
*/

/** Load all of the necessary class files for the plugin */
spl_autoload_register('PayWebDonations::autoload');
/**
 * Init Singleton Class for PayWeb Donations.
 *
 * @package PayWeb Donations
 * @author  Jacques Grove
 */


class PayWebDonations
{
    /** Holds the plugin instance */
    private static $instance = false;

	/**
	 * The array of templates that this plugin tracks.
	 *
	 * @var      array
	 */
	protected $templates;

	/**
	 * Unique identifier for the plugin.
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug;

    /** Define plugin constants */
    const MIN_PHP_VERSION  = '5.2.4';
    const MIN_WP_VERSION   = '3.0';
    const OPTION_DB_KEY    = 'payweb_donations_options';
    const TEXT_DOMAIN      = 'payweb-donations';
    const FILE             = __FILE__;
	public $pd_options;
    // -------------------------------------------------------------------------
    // Define constant data arrays
    // -------------------------------------------------------------------------
    private $currency_codes = array(
        'ZAR' => 'South African Rand',
    );

    /**
     * Singleton class
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     * Initializes the plugin by setting localization, filters, and
     * administration functions.
     */
    private function __construct()
    {
        add_action('init', array($this, 'textDomain'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

        $admin = new PayWebDonations_Admin();
        $admin->setOptions(
            get_option(self::OPTION_DB_KEY),
            $this->currency_codes
        );

        add_filter('widget_text', 'do_shortcode');
        add_shortcode('payweb-donation', array(&$this,'paywebShortcode'));
        add_action('wp_head', array($this, 'addCss'), 999);

        add_action(
            'widgets_init',
            create_function('', 'register_widget("PayWebDonations_Widget");')
        );

	    // Add a filter to the page attributes metabox to inject our template into the page template cache.
	    add_filter('page_attributes_dropdown_pages_args', array( $this, 'register_project_templates' ) );
	    // Add a filter to the save post in order to inject out template into the page cache
	    add_filter('wp_insert_post_data', array( $this, 'register_project_templates' ) );

	    // Add a filter to the template include in order to determine if the page has our template assigned and return it's path
	    add_filter('template_include', array( $this, 'view_project_template') );

	    // Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
	    register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

	    // Add your templates to this array.
	    $this->templates = array(
		    'template-donate.php'     => __( 'Donate Page Template', $this->plugin_slug ),
		    'template-success.php'     => __( 'Return Page Template', $this->plugin_slug ),
	    );

	    // adding support for theme templates to be merged and shown in dropdown
	    $templates = wp_get_theme()->get_page_templates();
	    $templates = array_merge( $templates, $this->templates );




    }

	/**
     * PSR-0 compliant autoloader to load classes as needed.
     *
     * @param  string  $classname  The name of the class
     * @return null    Return early if the class name does not start with the
     *                 correct prefix
     */
    public static function autoload($className)
    {
        if (__CLASS__ !== mb_substr($className, 0, strlen(__CLASS__))) {
            return;
        }
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
            $fileName .= DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, 'src_'.$className);
        $fileName .='.php';

        require $fileName;
    }

    /**
     * Loads the plugin text domain for translation
     */
    public function textDomain()
    {
        $domain = self::TEXT_DOMAIN;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
        load_textdomain(
            $domain,
            WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo'
        );
        load_plugin_textdomain(
            $domain,
            false,
            dirname(plugin_basename(__FILE__)).'/lang/'
        );
    }

    /**
     * Fired when the plugin is uninstalled.
     */
    public function uninstall()
    {
        delete_option('payweb_donations_options');
        delete_option('widget_payweb_donations');
    }

    /**
     * Adds inline CSS code to the head section of the html pages to center the
     * PayWeb button.
     */
    public function addCss()
    {
        $opts = get_option(self::OPTION_DB_KEY);
        if (isset($opts['center_button']) and $opts['center_button'] == true) {
            echo '<style type="text/css">'."\n";
            echo '.payweb-donations { text-align: center !important }'."\n";
            echo '</style>'."\n";
        }
    }

    /**
     * Create and register the PayWeb shortcode
     */
    public function paywebShortcode($atts)
    {

	    extract(
            shortcode_atts(
                array(
                    'purpose' => '',
                    'reference' => '',
                    'amount' => '',
                    'return_page' => '',
                ),
                $atts
            )
        );

        return $this->generateHtml(
            $purpose
        );
    }

    /**
     * Generate the PayWeb button HTML code
     */
    public function generateHtml(
        $purpose = null
    ) {
        $pd_options = get_option(self::OPTION_DB_KEY);

        // Set overrides for purpose and reference if defined


        $data = array(
            'pd_options' => $pd_options,
        );

        return PayWebDonations_View::render('payweb-button', $data);
    }

    // -------------------------------------------------------------------------
    // Environment Checks
    // -------------------------------------------------------------------------

    /**
     * Displays a warning when installed on an old PHP version.
     */
    public function phpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %3$s requires PHP version %1$s or greater.<br/>'.
            'Your installed PHP version: %2$s',
            self::MIN_PHP_VERSION,
            PHP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }

    /**
     * Displays a warning when installed in an old Wordpress version.
     */
    public function wpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %2$s requires WordPress version %1$s or greater.',
            self::MIN_WP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }

    /**
     * Get the name of this plugin.
     *
     * @return string The plugin name.
     */
    private function getPluginName()
    {
        $data = get_plugin_data(self::FILE);
        return $data['Name'];
    }

	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. If it doesn't exist, or it's empty prepare an array
		$templates = wp_cache_get( $cache_key, 'themes' );
		if ( empty( $templates ) ) {
			$templates = array();
		} // end if

		// Since we've updated the cache, we need to delete the old cache
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	} // end register_project_templates


	public function view_project_template( $template ) {

		global $post;

		// If no posts found, return to
		// avoid "Trying to get property of non-object" error
		if ( !isset( $post ) ) return $template;

		if ( ! isset( $this->templates[ get_post_meta( $post->ID, '_wp_page_template', true ) ] ) ) {
			return $template;
		} // end if

		$file = plugin_dir_path( __FILE__ ) . 'templates/' . get_post_meta( $post->ID, '_wp_page_template', true );

		// Just to be safe, we check if the file exist first
		if( file_exists( $file ) ) {
			return $file;
		} // end if

		return $template;

	} // end view_project_template


	/*--------------------------------------------*
	 * Delete Templates from Theme
	*---------------------------------------------*/
	public function delete_template( $filename ){
		$theme_path = get_template_directory();
		$template_path = $theme_path . '/' . $filename;
		if( file_exists( $template_path ) ) {
			unlink( $template_path );
		}

		// we should probably delete the old cache
		wp_cache_delete( $cache_key , 'themes');
	}

	/*--------------------------------------------*
	 * deactivate the plugin
	*---------------------------------------------*/
	public function deactivate( $network_wide ) {
		// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

	} // end deactivate

}

add_action('plugins_loaded', array('PayWebDonations', 'getInstance'));
