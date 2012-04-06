<?php $page_id = 'awpcp-admin-debug' ?>
<?php $page_title = __('AWPCP Debug', 'AWPCP') ?>

<?php include(AWPCP_DIR . 'admin/templates/admin-panel-header.tpl.php') ?>

		<p><?php _e('This information can help AWPCP Developers to debug possible problems. If you are submitting a bug report please take a minute to copy the information below to <a href="http://fpaste.org" target="_blank">http://fpaste.org</a> and provide the resulting URL in your report.', 'AWPCP') ?></p>

		<h3><?php _e('AWPCP Settings', 'AWPCP') ?></h3>
		<table>
			<thead>
				<tr>
					<th><?php _e('Option Name', 'AWPCP') ?></th>
					<th><?php _e('Option Value', 'AWPCP') ?></th>
				</tr>
			</thead>
			<tbody>
		<?php foreach($options as $name => $value): ?>
				<tr><td><?php echo $name ?></td><td><?php echo htmlentities($value) ?></td></tr>
		<?php endforeach ?> 
			</tbody>
		</table>

		<h3><?php _e('AWPCP Pages', 'AWPCP') ?></h3>
		<table>
			<thead>
				<tr>
					<th><?php _e('Page ID', 'AWPCP') ?></th>
					<th><?php _e('Title', 'AWPCP') ?></th>
					<th><?php _e('Reference', 'AWPCP') ?></th>
					<th><?php _e('Stored ID', 'AWPCP') ?></th>
				</tr>
			</thead>
			<tbody>
		<?php foreach($pages as $page): ?>
				<tr>
					<td><?php echo $page->post ?></td>
					<td><?php echo $page->title ?></td>
					<td><?php echo $page->ref ?></td>
					<td><?php echo $page->id ?></td>
				</tr>
		<?php endforeach ?> 
			</tbody>
		</table>

		<h3><?php _e('Rewrite Rules', 'AWPCP') ?></h3>
		<table>
			<thead>
				<tr>
					<th><?php _e('Pattern', 'AWPCP') ?></th>
					<th><?php _e('Replacement', 'AWPCP') ?></th>
				</tr>
			</thead>
			<tbody>
		<?php foreach($rules as $pattern => $rule): ?>
				<tr>
					<td><?php echo $pattern ?></td>
					<td><?php echo $rule ?></td>
				</tr>
		<?php endforeach ?> 
			</tbody>
		</table>

		</div><!-- end of .awpcp-main-content -->
	</div><!-- end of .page-content -->
</div><!-- end of #page_id -->