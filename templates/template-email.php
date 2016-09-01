<style type="text/css">
	body,
	html,
	.body {
		background: #f3f3f3 !important;
	}
</style>

<container>
	<spacer size="16">{SITENAME}</spacer>
	<row>
		<columns>
			<h1>Dear {DONOR_NAME}></h1>
			<p>Put your mail text here (templates/template-email.php)</p>

			<spacer size="16"></spacer>

			<callout class="secondary">
				<row>
					<columns large="12">
						<p>
							<strong>Donation Amount</strong><br/>
							{AMOUNT}
						</p>
						<p>
							<strong>Payment Method</strong><br/>
							{PAYMENT_METHOD}
						</p>
						<p>
							<strong>Email Address</strong><br/>
							{EMAIL}
						</p>
						<p>
							<strong>Donation ID</strong><br/>
							{REFERENCE}
						</p>
					</columns>

				</row>
			</callout>
			<hr/>
		</columns>
	</row>
	<row class="footer text-center">
		<columns large="6">
			<p>
				Call us at {PHONE}<br/>
				Email us at <a href="mailto:{MAIL_FROM}">{MAIL_FROM}</a>
			</p>
		</columns>
		<columns large="6">
			<p>
				Put your address here (templates/template-email.php)
			</p>
		</columns>
	</row>
</container>