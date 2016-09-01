<?php
/**
 * Template Name: Donate Template
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

require_once( plugin_dir_path( __FILE__ ) . 'template-donate.php' );
add_action( 'plugins_loaded', array( 'PayWebDonations', 'get_instance' ) );
get_header();
?>

<!-- FORM SECTION START- HTML AND CODE BELOW CAN BE EDITED / ALTERED / STYLED -->
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<!-- PHP SECTION START - DO NOT ALTER ANYTHING CONTAINED IN THIS PHP TAG - FORM CAN BE EDITED BELOW IN "FORM SECTION" -->
		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();

			// Include the page content template.
			get_template_part( 'template-parts/content', 'page' );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			// End of the loop.
		endwhile;
		require_once('wp-content/plugins/payweb-donations/src/PayWebDonations/global.inc.php');
		?>
		<form method="post" action="/wp-content/plugins/payweb-donations/src/PayWebDonations/process.php">
			<div class="payweb-donations">
				<input type="hidden" name="submitted" value="1" />
				<div class="form-group">
					<label for="name">Your Name *</label>
					<input type="text" id="donor_name" name="donor_name">
				</div>
				<div class="form-group">
					<label for="name">Your Email *</label>
					<input type="text" id="email" name="email">
				</div>
				<div class="form-group">
					<label for="name">Amount</label>
					<input type="number" id="amount" name="amount">
				</div>
				<div class="form-group">
					<label for="purpose">Donation Purpose</label><br>
					<select name="purpose" id="purpose" class="form-field">
						<option value="bursaries">Bursary Fund</option>
						<option value="legacy">Legacy Project</option>
					</select>
				</div>
				<div class="form-group" style="margin-top:10px;">
					<button type="submit" class="fusion-button-text">Donate</button>
				</div>


			</div>
		</form>

		<!-- FORM SECTION END -->
	</main><!-- .site-main -->
	<?php get_sidebar( 'content-bottom' ); ?>
</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
