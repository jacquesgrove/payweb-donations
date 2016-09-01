<?php
/**
 * PayWeb Donations Settings.
 *
 * Class that renders out the HTML for the settings screen and contains helpful
 * methods to simply the maintainance of the admin screen.
 *
 * @package PayWeb Donations
 * @author  Jacques Grove
 */
class PayWebDonations_Admin
{
    private $plugin_options;
    private $currency_codes;

	const PAGE_SLUG = 'payweb-donations-options';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'scripts'));
    }

    /**
     * To be deprecated soon!
     */
    public function setOptions(
        $options,
        $code
    ) {
        $this->plugin_options = $options;
        $this->currency_codes = $code;
    }


    /**
     * Register the Menu.
     */
    public function menu()
    {
        add_options_page(
            'PayWeb Donations Options',
            'PayWeb Donations',
            'administrator',
            self::PAGE_SLUG,
            array($this, 'renderpage')
        );
    }

    public function renderpage()
    {
        $data = array(
            'pageSlug'    => PayWebDonations_Admin::PAGE_SLUG,
            'optionDBKey' => PayWebDonations::OPTION_DB_KEY,
        );
        echo PayWebDonations_View::render('admin', $data);
    }

    /**
     * Load CSS and JS on the settings page.
     */
    public function scripts($hook)
    {
        if ($hook != 'settings_page_payweb-donations-options') {
            return;
        }
        $plugin = get_plugin_data(PayWebDonations::FILE, false, false);
        $version = $plugin['Version'];

        wp_register_style(
            'payweb-donations',
            plugins_url('assets/tabs.css', PayWebDonations::FILE),
            array(),
            $version
        );
        wp_enqueue_style('payweb-donations');

        wp_enqueue_script(
            'payweb-donations',
            plugins_url('assets/tabs.js', PayWebDonations::FILE),
            array('jquery'),
            $version,
            false
        );
    }

    /**
     * Register the settings.
     */
    public function init()
    {
        add_settings_section(
            'account_setup_section',
            __('Account Setup', PayWebDonations::TEXT_DOMAIN),
            array($this, 'accountSetupCallback'),
            self::PAGE_SLUG
        );
        add_settings_field(
            'payweb_live_ID',
            __('PayWeb Live ID *', PayWebDonations::TEXT_DOMAIN),
            array($this, 'paywebLiveIdCallback'),
            self::PAGE_SLUG,
            'account_setup_section',
            array(
                'label_for' => 'payweb_live_ID',
                'description' => __(
                    'Your PayWeb Live ID.',
                    PayWebDonations::TEXT_DOMAIN
                ),
            )
        );
	    add_settings_field(
		    'payweb_live_key',
		    __('PayWeb Live Key *', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'paywebLiveKeyCallback'),
		    self::PAGE_SLUG,
		    'account_setup_section',
		    array(
			    'label_for' => 'payweb_live_key',
			    'description' => __(
				    'Your PayWeb Live Key.',
				    PayWebDonations::TEXT_DOMAIN
			    ),
		    )
	    );
	    add_settings_field(
		    'payweb_test_ID',
		    __('PayWeb Test ID *', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'paywebTestIdCallback'),
		    self::PAGE_SLUG,
		    'account_setup_section',
		    array(
			    'label_for' => 'payweb_test_ID',
			    'description' => __(
				    'Your PayWeb test ID.',
				    PayWebDonations::TEXT_DOMAIN
			    ),
		    )
	    );
	    add_settings_field(
		    'payweb_test_key',
		    __('PayWeb Test Key *', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'paywebTestKeyCallback'),
		    self::PAGE_SLUG,
		    'account_setup_section',
		    array(
			    'label_for' => 'payweb_test_key',
			    'description' => __(
				    'Your PayWeb Test Key.',
				    PayWebDonations::TEXT_DOMAIN
			    ),
		    )
	    );

	    add_settings_field(
		    'testmode',
		    __('Test Mode', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'PayWebTestModeCallback'),
		    self::PAGE_SLUG,
		    'account_setup_section',
		    array(
			    'label_for' => 'testmode',
			    'description' => sprintf(
				    __('Enable PayGate Test Mode.', PayWebDonations::TEXT_DOMAIN),
				    'Put the donations plugin in test mode.'
			    ),
		    )
	    );

	    add_settings_section(
		    'page_setup_section',
		    __('Page Setup', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'pageSetupCallback'),
		    self::PAGE_SLUG
	    );

	    add_settings_field(
		    'donate_url',
		    __('Donation Page URL *', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'paywebDonationUrlCallback'),
		    self::PAGE_SLUG,
		    'page_setup_section',
		    array(
			    'label_for' => 'donate_url',
			    'description' => __(
				    'The donation page URL.  Set up a page, and use "Donate Template" as the page template, and then enter the page slug here.  Example:  If the permalink (for the page) is "http://www.hello.com/donate", enter "donate" here.',
				    PayWebDonations::TEXT_DOMAIN
			    ),
		    )
	    );

	    add_settings_field(
		    'return_url',
		    __('Successful Transaction URL *', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'paywebReturnUrlCallback'),
		    self::PAGE_SLUG,
		    'page_setup_section',
		    array(
			    'label_for' => 'return_url',
			    'description' => __(
				    'Payment success page.  Set up a page, and use "Return Template" as the page template, and then enter the page slug here. Example:  If the permalink (for the page) is "http://www.hello.com/success", enter "success" here.',
				    PayWebDonations::TEXT_DOMAIN
			    ),
		    )
	    );

	    add_settings_field(
		    'fund_1_name',
		    __('Fund 1 Name *', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'paywebFundOneCallback'),
		    self::PAGE_SLUG,
		    'page_setup_section',
		    array(
			    'label_for' => 'fund_1_name',
			    'description' => __(
				    'Single string for a fund, no spaces i.e.',
				    PayWebDonations::TEXT_DOMAIN
			    ),
		    )
	    );

	    add_settings_section(
		    'button_setup_section',
		    __('Button Setup', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'buttonSetupCallback'),
		    self::PAGE_SLUG
	    );

	    add_settings_field(
		    'button_text',
		    __('Donation Button Text *', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'paywebDonationButtonCallback'),
		    self::PAGE_SLUG,
		    'button_setup_section',
		    array(
			    'label_for' => 'button_text',
			    'description' => __(
				    'The text displayed on the donate button (widget).',
				    PayWebDonations::TEXT_DOMAIN
			    ),
		    )
	    );

	    add_settings_section(
		    'email_setup_section',
		    __('Email Setup', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'emailSetupCallback'),
		    self::PAGE_SLUG
	    );

	    add_settings_field(
		    'email_subject',
		    __('Donation Email Subject *', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'paywebEmailSubjectCallback'),
		    self::PAGE_SLUG,
		    'email_setup_section',
		    array(
			    'label_for' => 'email_subject',
			    'description' => __(
				    'The subject for emails sent after donation.',
				    PayWebDonations::TEXT_DOMAIN
			    ),
		    )
	    );

	    add_settings_field(
		    'phone',
		    __('Phone *', PayWebDonations::TEXT_DOMAIN),
		    array($this, 'paywebPhoneCallback'),
		    self::PAGE_SLUG,
		    'email_setup_section',
		    array(
			    'label_for' => 'phone',
			    'description' => __(
				    'The phone number that is included in emails sent after donation.',
				    PayWebDonations::TEXT_DOMAIN
			    ),
		    )
	    );



        register_setting(
            PayWebDonations::OPTION_DB_KEY,
            PayWebDonations::OPTION_DB_KEY
        );
    }

    // -------------------------------------------------------------------------
    // Section Callbacks
    // -------------------------------------------------------------------------

    public function accountSetupCallback()
    {
        printf(
            '<p>%s</p>',
            __('Required fields are marked with a *.', PayWebDonations::TEXT_DOMAIN)
        );
    }


	public function pageSetupCallback()
	{
		printf(
			'<p>%s</p>',
			__('Required fields are marked with a *.', PayWebDonations::TEXT_DOMAIN)
		);
	}

	public function buttonSetupCallback()
	{
		printf(
			'<p>%s</p>',
			__('Required fields are marked with a *.', PayWebDonations::TEXT_DOMAIN)
		);
	}

	public function emailSetupCallback()
	{
		printf(
			'<p>%s</p>',
			__('Required fields are marked with a *.', PayWebDonations::TEXT_DOMAIN)
		);
	}

    // -------------------------------------------------------------------------
    // Fields Callbacks
    // -------------------------------------------------------------------------

    public function paywebLiveIdCallback($args)
    {
        $optionKey = PayWebDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        echo "<input class='regular-text' type='text' id='payweb_live_ID' ";
        echo "name='{$optionKey}[payweb_live_ID]'' ";
        echo "value='{$options['payweb_live_ID']}' />";

        echo "<p class='description'>{$args['description']}</p>";
    }

	public function paywebLiveKeyCallback($args)
	{
		$optionKey = PayWebDonations::OPTION_DB_KEY;
		$options = get_option($optionKey);
		echo "<input class='regular-text' type='text' id='payweb_live_key' ";
		echo "name='{$optionKey}[payweb_live_key]'' ";
		echo "value='{$options['payweb_live_key']}' />";

		echo "<p class='description'>{$args['description']}</p>";
	}

	public function paywebTestIdCallback($args)
	{
		$optionKey = PayWebDonations::OPTION_DB_KEY;
		$options = get_option($optionKey);
		echo "<input class='regular-text' type='text' id='payweb_test_ID' ";
		echo "name='{$optionKey}[payweb_test_ID]'' ";
		echo "value='{$options['payweb_test_ID']}' />";

		echo "<p class='description'>{$args['description']}</p>";
	}

	public function paywebTestKeyCallback($args)
	{
		$optionKey = PayWebDonations::OPTION_DB_KEY;
		$options = get_option($optionKey);
		echo "<input class='regular-text' type='text' id='payweb_test_key' ";
		echo "name='{$optionKey}[payweb_test_key]'' ";
		echo "value='{$options['payweb_test_key']}' />";

		echo "<p class='description'>{$args['description']}</p>";
	}

	public function paywebDonationUrlCallback($args)
	{
		$optionKey = PayWebDonations::OPTION_DB_KEY;
		$options = get_option($optionKey);
		echo "<input class='regular-text' type='text' id='donate_url' ";
		echo "name='{$optionKey}[donate_url]'' ";
		echo "value='{$options['donate_url']}' />";

		echo "<p class='description'>{$args['description']}</p>";
	}

	public function paywebReturnUrlCallback($args)
	{
		$optionKey = PayWebDonations::OPTION_DB_KEY;
		$options = get_option($optionKey);
		echo "<input class='regular-text' type='text' id='return_url' ";
		echo "name='{$optionKey}[return_url]'' ";
		echo "value='{$options['return_url']}' />";

		echo "<p class='description'>{$args['description']}</p>";
	}

    public function PayWebTestModeCallback($args)
    {
        $optionKey = PayWebDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        $checked = isset($options['testmode']) ?
            $options['testmode'] :
            false;
        echo "<input type='checkbox' id='testmode' ";
        echo "name='{$optionKey}[testmode]' value='1' ";
	    if ($checked) {
		    echo 'checked ';
	    }
        echo "/>";

        echo "<p class='description'>{$args['description']}</p>";
    }

	public function paywebDonationButtonCallback($args)
	{
		$optionKey = PayWebDonations::OPTION_DB_KEY;
		$options = get_option($optionKey);
		echo "<input class='regular-text' type='text' id='button_text' ";
		echo "name='{$optionKey}[button_text]'' ";
		echo "value='{$options['button_text']}' />";

		echo "<p class='description'>{$args['description']}</p>";
	}

	public function paywebEmailSubjectCallback($args)
	{
		$optionKey = PayWebDonations::OPTION_DB_KEY;
		$options = get_option($optionKey);
		echo "<input class='regular-text' type='text' id='email_subject' ";
		echo "name='{$optionKey}[email_subject]'' ";
		echo "value='{$options['email_subject']}' />";

		echo "<p class='description'>{$args['description']}</p>";
	}

	public function paywebPhoneCallback($args)
	{
		$optionKey = PayWebDonations::OPTION_DB_KEY;
		$options = get_option($optionKey);
		echo "<input class='regular-text' type='text' id='phone' ";
		echo "name='{$optionKey}[phone]'' ";
		echo "value='{$options['phone']}' />";

		echo "<p class='description'>{$args['description']}</p>";
	}

	// -------------------------------------------------------------------------
    // HTML and Form element methods
    // -------------------------------------------------------------------------

    /**
     * Checkbox.
     * Renders the HTML for an input checkbox.
     *
     * @param   string  $label      The label rendered to screen
     * @param   string  $name       The unique name to identify the input
     * @param   boolean $checked    If the input is checked or not
     */
    public static function checkbox($label, $name, $checked)
    {
        printf('<input type="checkbox" name="%s" value="true"', $name);
        if ($checked) {
            echo ' checked';
        }
        echo ' />';
        echo ' '.$label;
    }
}
