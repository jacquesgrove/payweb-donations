<?php
/**
 * Template Name: Return Template
 *
 * A template used to demonstrate how to include the template
 * using this plugin.
 *
 * @package payweb-donations
 * @since 	1.0.0
 * @version	1.0.0
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} // end if

require_once( plugin_dir_path( __FILE__ ) . 'template-success.php' );
add_action( 'plugins_loaded', array( 'PayWebDonations', 'get_instance' ) );

$site_url = get_site_url();
$pd_options = get_option( 'payweb_donations_options' );
require_once('wp-content/plugins/payweb-donations/src/PayWebDonations/global.inc.php');
require_once('wp-content/plugins/payweb-donations/src/PayWebDonations/paygate.payweb3.php');
get_header();

$pd_options = get_option( 'payweb_donations_options' );

if(isset($pd_options['testmode'])) {
	$testmode = $pd_options['testmode'];
}

if(isset($testmode) && $testmode == 1){
	$PAYGATE_ID = $pd_options['payweb_test_ID'];
	$PAYGATE_KEY = $pd_options['payweb_test_key'];
} else {
	$PAYGATE_ID = $pd_options['payweb_live_ID'];
	$PAYGATE_KEY = $pd_options['payweb_live_key'];
}

?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<!-- Payment Return page start -->
		<?php
		$data = array(
			'PAYGATE_ID'         => $PAYGATE_ID,
			'PAY_REQUEST_ID'     => $_POST['PAY_REQUEST_ID'],
			'TRANSACTION_STATUS' => $_POST['TRANSACTION_STATUS'],
			'REFERENCE'          => $_COOKIE['reference'],
			'CHECKSUM'           => $_POST['CHECKSUM']
		);

		$encryption_key = $PAYGATE_KEY;

		/*
	    * initiate the PayWeb 3 helper class
	    */
		$PayWeb3 = new PayGate_PayWeb3();
		/*
		 * Set the encryption key of your PayGate PayWeb3 configuration
		 */
		$PayWeb3->setEncryptionKey($encryption_key);
		/*
		 * Check that the checksum returned matches the checksum we generate
		 */
		$isValid = $PayWeb3->validateChecksum($data);

		while ( have_posts() ) : the_post();

			// Include the page content template.
			get_template_part( 'template-parts/content', 'page' );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			// End of the loop.
		endwhile;
		?>

		<h4>Transaction Result: <?php echo $PayWeb3->getTransactionStatusDescription($data['TRANSACTION_STATUS']) ?></h4>

		<?php

		$data = array(
			'PAYGATE_ID'     => $PAYGATE_ID,
			'PAY_REQUEST_ID' => $_POST['PAY_REQUEST_ID'],
			'REFERENCE'      => $_COOKIE['reference']
		);

		/*
		 * Initiate the PayWeb 3 helper class
		 */
		$PayWeb3 = new PayGate_PayWeb3();
		/*
		 * Set the encryption key of your PayGate PayWeb3 configuration
		 */
		$PayWeb3->setEncryptionKey($encryption_key);
		/*
		 * Set the array of fields to be posted to PayGate
		 */
		$PayWeb3->setQueryRequest($data);
		/*
		 * Do the curl post to PayGate
		 */
		$returnData = $PayWeb3->doQuery();


		if(isset($PayWeb3->queryResponse) || isset($PayWeb3->lastError)){
			/*
			* We have received a response from PayWeb3
			*/
			if(!isset($PayWeb3->lastError)){
				/*
				 * It is not an error, so continue
				 */
				foreach($PayWeb3->queryResponse as $key => $value){
					/*
					 * Loop through the key / value pairs returned
					 */

					if($key == "AMOUNT"){
						$amount = $value / 100;
						$amount = "R ".$amount;
					}
					if($key == "USER1"){
						$to = $value;
					}
					if($key == "USER2"){
						$donor_name = $value;
					}
					if($key == "PAY_METHOD_DETAIL"){
						$payment_method = $value;
					}
					if($key == "REFERENCE"){
						$reference = $value;
					}
					$sitename = get_bloginfo ('name');
					$admin_email = get_bloginfo ('admin_email');
					$subject = $pd_options['email_subject'];
					$phone = $pd_options['phone'];

					$headers = "From: " . strip_tags($admin_email) . "\r\n";
					$headers .= "Reply-To: ". strip_tags($admin_email) . "\r\n";
					$headers .= "CC: ".$admin_email."\r\n";
					$headers .= "MIME-Version: 1.0\r\n";
					$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

					$message = file_get_contents('/wp-content/plugins/payweb-donations/templates/template-email.php');
					$message = str_replace('{SITENAME}', $sitename, $message);
					$message = str_replace('{DONOR_NAME}', $donor_name, $message);
					$message = str_replace('{AMOUNT}', $amount, $message);
					$message = str_replace('{PAYMENT_METHOD}', $payment_method, $message);
					$message = str_replace('{EMAIL}', $to, $message);
					$message = str_replace('{REFERENCE}', $reference, $message);
					$message = str_replace('{PHONE}', $phone, $message);
					$message = str_replace('{MAIL_FROM}', $admin_email, $message);

					wp_mail( $to, $subject, $message, $headers);
				}
			} else if(isset($PayWeb3->lastError)){
				/*
				* otherwise handle the error response
				*/
				echo $PayWeb3->lastError;
			} ?>

		<?php } ?>
	</main><!-- .site-main -->

	<?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
