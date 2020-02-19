<?php
/**
 * Tags Box
 *
 * @package P4MT
 */

?>
<ul id="p4_tags_list" style="height: 150px; overflow: auto; border: 1px solid #dfdfdf; margin: 1em 0; padding: .5em;">
	<?php foreach ( $tags as $tag_obj ) { ?>
		<li>
			<input type="checkbox" data-wp-taxonomy="post_tag" value="<?php echo esc_attr( $tag_obj->name ); ?>"
				name="tax_input[post_tag][]" id="tagged_<?php echo esc_attr( $tag_obj->term_id ); ?>"
				<?php
				if ( in_array( $tag_obj->term_id, $assigned_tags, true ) ) {
					echo ' checked="true"';
				}
				?>
				/>
			<label for="tagged_<?php echo esc_attr( $tag_obj->term_id ); ?>"><?php echo esc_html( $tag_obj->name ); ?></label>
		</li>
	<?php } ?>
</ul>
<p>New tags can only be added by an administrator.</p>
