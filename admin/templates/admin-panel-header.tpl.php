<div id="<?php echo $page_id ?>" class="wrap">
	<div class="page-content">
		<h2 class="awpcp-page-header"><?php echo $page_title ?></h2>

		<?php echo $sidebar = awpcp_admin_sidebar(); ?>

		<div class="awpcp-main-content <?php echo (empty($sidebar) ? 'without-sidebar' : 'with-sidebar') ?>">