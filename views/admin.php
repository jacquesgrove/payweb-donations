<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">
    <div id="icon-plugins" class="icon32"></div>
    <h2>PayWeb Donations</h2>
    <h2 class="nav-tab-wrapper">
        <ul id="payweb-donations-tabs">
            <li id="payweb-donations-tab_1" class="nav-tab nav-tab-active"><?php _e('Settings', 'payweb-donations'); ?></li>

        </ul>
    </h2>
    <form method="post" action="options.php">
        <?php settings_fields($optionDBKey); ?>
        <div id="payweb-donations-tabs-content">
            <div id="payweb-donations-tab-content-1">
                <?php do_settings_sections($pageSlug); ?>
            </div>
        </div>
        <?php submit_button(); ?>
    </form>