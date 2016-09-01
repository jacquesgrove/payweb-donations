<!-- Begin PayWeb Donations Button Widget by http://www.jacquesgrove.co.za/ -->
<?php
$donate_page = $pd_options['donate_url'];
$button_text = $pd_options['button_text'];
$donate_page = get_site_url().'/'.$donate_page;
?>
<form method="post" action="<?php echo $donate_page;?>">
	<button type="submit" class="fusion-button-text"><?php echo $button_text;?></button>
</form>

<!-- End PayWeb Donations Button Widget-->