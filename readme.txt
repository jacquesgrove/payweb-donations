=== PayWeb Donations ===
Contributors:  Jacques Grové
Developer URL link: http://www.jacquesgrove.co.za/
Tags: payweb, donation, donations, shortcode, widget, donate, button, sidebar, paygate donation, payment, paygate
Requires at least: 3.0
Tested up to: 4.5
Stable tag: 1.0.0
License: Commercial Copyright.

Easy, simple setup to add a PayWeb Donation button as a Widget or with a shortcode.

== Description ==

Adds a PayWeb donation shortcode, a donation page, success page and sidebar Widget to WordPress. The options menu lets you setup you PayWeb ID and a few other optional settings. You can choose which donation button you want to use or if you want to use your own button. You can also set an optional default purpose and reference which can be overridden on each inserted instance with the shortcode options or in the Widget settings. There is also options available for custom payment page style and the return page.

= Widget =

In the Appearance -> Widgets you'll find the PayWeb Donations Button widget. This displays a button in the position you assign it, or in the shortcode location that you can add using the shortcode above.
In the admin section, you can set the text for this button.

= Donate Page =

Add a new page and use 'Donate Template' as the page template.  You can also add HTML to the page in the page editor, which will appear above the donation form.
The form itself can also be edited / altered by editing wp-content/payweb-donations/templates/ template-donate.php - THE FORM IS CONTAINED IN THE LATTER SECTION OF THAT FILE.

IMPORTANT: The SLUG for the pages - after saving - must be entered in the administration page of the plugin, in the field called: "Successful Transaction URL."  Enter ONLY the slug, not the full site URL.
Example:  If the permalink is "http://mywebsite.com/donate" then enter "donate" into the aforementioned field.

= Result Page =

Add a new page and use 'Return Template' as the page template.  You can also add HTML to the page in the page editor, which will appear above the return page content.
IMPORTANT: The SLUG for the pages - after saving - must be entered in the administration page of the plugin, in the field called: "Donation Page URL."  Enter ONLY the slug, not the full site URL.
Example:  If the permalink is "http://mywebsite.com/success" then enter "success" into the aforementioned field.

= Email =

The email uses a template located at (templates/template-email.php)  - you can edit this, but do not replace the strings between "{}"  Curly brackets, or if you redesign the email template, remember to include them at the appropriate spots.
An email is sent to the donor as well as the site admin.

= Shortcode =

Insert the button in your pages or posts with this shortcode

`[payweb-donation]`

This donation plugin generates valid XHTML Transitional and Strict code.

== Installation ==

= Install =

1. Upload the 'payweb-donations' folder  to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Settings -> PayWeb Donations and start entering your info.

= Uninstall =

1. Deactivate PayWeb Donations in the 'Plugins' menu in Wordpress.
2. After Deactivation a 'Delete' link appears below the plugin name, follow the link and confim with 'Yes, Delete these files'.
3. This will delete all the plugin files from the server as well as erasing all options the plugin has stored in the database.

= Version 1.0 - 30 May 2016 =
 * Initial Release
