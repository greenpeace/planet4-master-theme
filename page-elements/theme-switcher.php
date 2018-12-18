<div id="theme-switcher">
	<?php _e( 'Switch theme', 'planet4-master-theme' ); ?>:
	<?php

	$campaign_args  = array(
		'post_type'  => 'page',
		'meta_key'   => 'is_campaign_page',
		'meta_value' => 'on',
	);
	$campaing_pages = new WP_Query( $campaign_args );

	if ( $campaing_pages->have_posts() ) { ?>
        <select>
			<?php
			while ( $campaing_pages->have_posts() ) {
				$campaing_pages->the_post();
				?>
                <option value="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></option><?php
			}
			?>
        </select>
	<?php }
	wp_reset_query();
	?>
</div>
