<?php

	//ini_set('display_errors','on');
	require_once('global.inc.php');
	require_once('paygate.payweb3.php');
	require_once('../../../../../wp-config.php');

	$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$prefix =  $table_prefix;

	$sql = <<<SQL
	    SELECT option_value
	    FROM `wp_options`
	    WHERE `option_name` = 'payweb_donations_options'
SQL;

	if(!$result = $db->query($sql)){
		die('There was an error running the query [' . $db->error . ']');
	}

	while($row = $result->fetch_assoc()){
		$pd_options = unserialize($row['option_value']);
	}

	if ( isset($_SERVER['HTTPS'])) {
		$site_url = 'https://' . $_SERVER["SERVER_NAME"];
	}
	else
	{
		$site_url = 'http://' . $_SERVER["SERVER_NAME"];
	}

	if(isset($pd_options['testmode'])) {
		$testmode = $pd_options['testmode'];
	}

	if(isset($testmode) && $testmode == 1)
	{
		$PAYGATE_ID = $pd_options['payweb_test_ID'];
		$PAYGATE_KEY = $pd_options['payweb_test_key'];
	}
	else
	{
		$PAYGATE_ID = $pd_options['payweb_live_ID'];
		$PAYGATE_KEY = $pd_options['payweb_live_key'];
	}


	$purpose = $_REQUEST['purpose'];
	$amount = 0;
	$reference = generateReference($purpose);
	$transaction_date = getDateTime('Y-m-d H:i:s');
	$locale = 'en-us';
	$country = 'ZAF';
	$currency = 'ZAR';

	if(isset($_REQUEST['amount']))
	{
		$amount = number_format( (float) $_REQUEST['amount'], 2, '.', '' ) * 100;
	}

	if(isset($_REQUEST['email']))
	{
		$email = $_REQUEST['email'];
	}

	if(isset($_REQUEST['donor_name']))
	{
		$donor_name = $_REQUEST['donor_name'];
	}



	$mandatoryFields = array(
		'PAYGATE_ID'        => filter_var($PAYGATE_ID, FILTER_SANITIZE_NUMBER_INT),
		'REFERENCE'         => filter_var($reference, FILTER_SANITIZE_STRING),
		'AMOUNT'            => filter_var($amount, FILTER_SANITIZE_NUMBER_INT),
		'CURRENCY'          => filter_var($currency, FILTER_SANITIZE_STRING),
		'RETURN_URL'        => filter_var($site_url.'/'.$pd_options['return_url'], FILTER_SANITIZE_URL),
		'TRANSACTION_DATE'  => filter_var($transaction_date, FILTER_SANITIZE_STRING),
		'LOCALE'            => filter_var($locale, FILTER_SANITIZE_STRING),
		'COUNTRY'           => filter_var($country, FILTER_SANITIZE_STRING),
		'EMAIL'             => filter_var($email, FILTER_SANITIZE_EMAIL)
	);

	$optionalFields = array(
		'USER1'             => (isset($email) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : ''),
		'USER2'             => (isset($donor_name) ? filter_var($_POST['donor_name'], FILTER_SANITIZE_STRING) : '')
	);

	$data = array_merge($mandatoryFields, $optionalFields);
	unset($_COOKIE['reference']);
    setcookie('reference', $data['REFERENCE'], time() + (1200 * 30), "/");
	$PayWeb3 = new PayGate_PayWeb3();
	/*
	 * if debug is set to true, the curl request and result as well as the calculated checksum source will be logged to the php error log
	 */
	$PayWeb3->setDebug(true);
	/*
	 * Set the encryption key of your PayGate PayWeb3 configuration
	 */
	$PayWeb3->setEncryptionKey($PAYGATE_KEY);
	/*
	 * Set the array of fields to be posted to PayGate
	 */
	$PayWeb3->setInitiateRequest($data);

	/*
	 * Do the curl post to PayGate
	 */
	$returnData = $PayWeb3->doInitiate();


	/*
	* If the checksums match loop through the returned fields and create the redirect from
	*/

	echo '<form id="paywebform" name="paygate_process_form" method="post" action="' . $PayWeb3::$process_url . '">';
	if(isset($PayWeb3->processRequest) || isset($PayWeb3->lastError)) {
		if ( ! isset( $PayWeb3->lastError ) ) {
			/*
			 * Check that the checksum returned matches the checksum we generate
			 */
			$isValid = $PayWeb3->validateChecksum( $PayWeb3->initiateResponse );
			if ( $isValid ) {
				/*
				 * If the checksums match loop through the returned fields and create the redirect from
				 */
				foreach ( $PayWeb3->processRequest as $key => $value ) {
					echo <<<HTML
						<input type="hidden" name="{$key}" value="{$value}" />
HTML;
				}
				echo '</form>';
				echo "<script type='text/javascript'>";
				echo "document.getElementById('paywebform').submit()";
				echo "</script>";
			} else {
				echo 'Checksums do not match';
			}
		}

	}
