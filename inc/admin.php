<?php
/**
 * Whippet Admin
 *
 * @category Whippet
 * @package  Whippet
 * @author   Jake Henshall
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.hashbangcode.com/
 */

/**
 * Exit if accessed directly
 *
 * @var [type]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// if no tab is set, default to first/options tab.
if ( empty( $_GET['tab'] ) ) {
	$_GET['tab'] = 'options';
}
?>

<div class="wrap whippet-admin">

	<!-- Plugin Admin Page Title -->
	<h2>Whippet Settings</h2>

	<!-- Documentation Notice -->
	<!-- <div class="notice notice-info">
		<p><?php echo __( 'Documentation is coming soon :)', 'whippet' ); ?></p>
	</div> -->

	<!-- Tab Navigation -->
	<h2 class="nav-tab-wrapper">
		<a href="?page=whippet&tab=options" class="nav-tab <?php echo 'options' === $_GET['tab'] || '' ? 'nav-tab-active' : ''; ?>">Options</a>
		<a href="?page=whippet&tab=analytic" class="nav-tab <?php echo 'analytic' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>">Analytics</a>
		<a href="?page=whippet&tab=importExport" class="nav-tab <?php echo 'importExport' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>">Import/Export</a>
		<a href="?page=whippet&tab=support" class="nav-tab <?php echo 'support' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>">Support</a>
	</h2>

	<!-- Main Options Tab -->
	<?php if ( 'options' === $_GET['tab'] ) { ?>
	<form method="post" action="options.php">
		<?php settings_fields( 'whippet_options' ); ?>
		<?php do_settings_sections( 'whippet_options' ); ?>
		<?php submit_button(); ?>
	</form>

	<!-- Analytics Tab -->
	<?php } elseif ( 'analytic' === $_GET['tab'] ) { ?>
	<form method="post" action="options.php">
		<?php
			save_ga_locally_settings_page();
		?>
	</form>

	<!-- Import/Export Tab -->
	<?php } elseif ( 'importExport' === $_GET['tab'] ) { ?>

		<h2>Import/Export Whippet Plugin Settings</h2>
		<?php whippet_settings_page(); ?>

	<!-- Support Tab -->
	<?php } elseif ( 'support' === $_GET['tab'] ) { ?>

	<h2>Support</h2>
	<p>For plugin support and documentation, please visit <a href='https://whippetwp.com/' title='whippet' target='_blank' rel='noopener noreferrer'>Whippet Docs</a>.</p>

	<?php } ?>

</div>
