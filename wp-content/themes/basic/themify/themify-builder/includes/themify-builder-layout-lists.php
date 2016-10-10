<div class="themify_builder_options_tab_wrapper">
	<div class="themify_builder_options_tab_content">
		<form id="themify_builder_load_template_form" method="POST">

			<div id="themify_builder_lightbox_options_tab_items">
				<li class="title"><?php _e('Layouts', 'themify'); ?></li>
			</div>

			<div id="themify_builder_lightbox_actions_items">
			</div>

			<p><?php _e('Builder Layouts are the pre-designed layouts which you can apply to any page for quicker prototyping. Click on the thumbnail to apply. <strong>Warning:</strong> your current Builder content on the page will be override with the Layout.', 'themify') ?></p>

			<?php if ( count( $layouts ) > 0 ): ?>
			<ul class="themify_builder_layout_lists">

				<?php foreach( $layouts as $layout ): ?>
				<li class="layout_preview_list">
					<div class="layout_preview" data-layout-slug="<?php echo esc_attr( $layout['slug'] ); ?>" data-builtin="<?php echo $layout['prebuilt'] == true ? 'yes' : 'no' ?>">
						<div class="thumbnail"><?php echo $layout['thumbnail']; ?></div>
						<!-- /thumbnail -->
						<div class="layout_action">
							<div class="layout_title"><?php echo $layout['title']; ?></div>
							<!-- /template_title -->
						</div>
						<!-- /template_action -->
					</div>
					<!-- /template_preview -->
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
			<div class="clearfix"></div>


			<a href="<?php echo admin_url('post-new.php?post_type=' . $this->layout->post_type_name); ?>" target="_blank"
			   class="add_new">
				<span class="themify_builder_icon add"></span>
				<?php _e('Add new layout', 'themify') ?>
			</a>
			<a href="<?php echo admin_url('edit.php?post_type=' . $this->layout->post_type_name); ?>" target="_blank"
			   class="add_new">
				<span class="themify_builder_icon ti-folder"></span>
				<?php _e('Manage Layouts', 'themify') ?>
			</a>

		</form>
	</div>
</div>