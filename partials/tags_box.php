<ul id="p4_tags_list" style="height: 150px; overflow: auto; border: 1px solid #dfdfdf; margin: 1em 0; padding: .5em;">
	<?php foreach ( $tags as $tag ) { ?>
		<li>
			<input type="checkbox" data-wp-taxonomy="post_tag" value="<?php echo $tag->name; ?>"
			       name="tax_input[post_tag][]" id="tagged_<?php echo $tag->term_id; ?>"
				<?php if ( in_array( $tag->term_id, $assigned_tags, true ) ) {
					echo ' checked="true"';
				} ?>/>
			<label for="tagged_<?php echo $tag->term_id; ?>"><?php echo $tag->name; ?></label>
		</li>
	<?php } ?>
</ul>
<p>New tags can only be added by an administrator.</p>
