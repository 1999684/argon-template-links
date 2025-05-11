<article class="post post-full card bg-white shadow-sm border-0" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="post-content" id="post_content">
		<?php if (post_password_required()){ ?>
			<div class="text-center container">
				<form action="<?php echo $GLOBALS['wp_path']; ?>wp-login.php?action=postpass" class="post-password-form" method="post">
					<div class="post-password-form-text"><?php _e('这是一篇受密码保护的文章，您需要提供访问密码', 'argon');?></div>
					<div class="row">
						<div class="form-group col-lg-6 col-md-8 col-sm-10 col-xs-12 post-password-form-input">
							<div class="input-group input-group-alternative">
								<div class="input-group-prepend">
									<span class="input-group-text"><i class="fa fa-key"></i></span>
								</div>
								<input name="post_password" class="form-control" placeholder="<?php _e('密码', 'argon');?>" type="password">
							</div>
							<?php
								$post_password_hint = get_post_meta(get_the_ID(), 'password_hint', true);
								if (!empty($post_password_hint)){
									echo '<div class="post-password-hint">' . $post_password_hint . '</div>';
								}
							?>
						</div>
					</div>
					<input class="btn btn-primary" type="submit" name="Submit" value="<?php _e('确认', 'argon');?>">
				</form>
			</div>
		<?php
			}else{
				global $post_references, $post_reference_keys_first_index, $post_reference_contents_first_index;
				$post_references = array();
				$post_reference_keys_first_index = array();
				$post_reference_contents_first_index = array();

				the_content();
			}
		?>
	</div>

	<?php
		$referenceList = get_reference_list();
		if ($referenceList != ""){
			echo $referenceList;
		}
	?>

	<?php if (has_tag()) { ?>
		<div class="post-tags">
			<i class="fa fa-tags" aria-hidden="true"></i>
			<?php
				$tags = get_the_tags();
				foreach ($tags as $tag) {
					echo "<a href='" . get_category_link($tag -> term_id) . "' target='_blank' class='tag badge badge-secondary post-meta-detail-tag'>" . $tag -> name . "</a>";
				}
			?>
		</div>
	<?php } ?>
</article>
