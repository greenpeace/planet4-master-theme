<?php

/**
 * Blocks Usage class
 *
 * @package P4BKS\Controllers\Menu
 * @since 1.40.0
 */

namespace P4\MasterTheme\Controllers\Menu;

/**
 * Show posts using classic blocks.
 */
class ClassicBlocksUsage extends Controller
{
    /**
     * Create menu/submenu entry.
     */
    public function create_admin_menu(): void
    {

        $current_user = wp_get_current_user();

        if (!in_array('administrator', $current_user->roles, true) || !current_user_can('manage_options')) {
            return;
        }

        add_submenu_page(
            BlocksReportController::P4BKS_REPORTS_SLUG_NAME,
            __('Classic block usage', 'planet4-blocks-backend'),
            __('Classic block usage', 'planet4-blocks-backend'),
            'manage_options',
            'classic_block_usage',
            [ $this, 'classic_block_usage' ]
        );
    }

    /**
     * Show all posts that use classic "blocks", which isn't really a block but the absence of one.
     * All consecutive content that is not inside of a block comment is regarded as a single classic block when parsed.
     * So in order to find classic blocks, we parse the content as blocks and then check for all blocks where the name
     * is null and the content is more than just whitespace.
     */
    public function classic_block_usage(): void
    {
        $all_posts = get_posts(
            [
                'post_type' => [ 'post', 'page', 'campaign' ],
                'numberposts' => - 1,
            ]
        );

        $has_classic_block = static function ($post) {
            $blocks = parse_blocks($post->post_content);
            foreach ($blocks as $block) {
                if (null === $block['blockName'] && '' !== trim($block['innerHTML'])) {
                    return true;
                }
            }

            return false;
        };

        $with_classic_blocks = array_filter($all_posts, $has_classic_block);

        // Copied this code from https://github.com/greenpeace/planet4-plugin-gutenberg-blocks/blob/add-classic-block-to-report/classes/controller/menu/class-blocks-usage-controller.php#L107-L107.
        // So disabling CS here as well as this is only a temporary dev tool.
		//phpcs:disable
		echo '<hr>';
		echo '<h2>Posts using classic blocks</h2>';
		echo '<p>Following posts are detected to use classic blocks. It is recommended to convert them to new blocks.</p>';
		echo '<p>You can convert classic blocks in the post editor by following the steps described in <a target="_blank" href="https://planet4.greenpeace.org/create/manage/convert-posts-to-gutenberg-editor/">this article</a>.</p>';
		echo '<table>';
		echo '<tr style="text-align: left">
							<th>' . __( 'ID', 'planet4-blocks-backend' ) . '</th>
							<th>' . __( 'Title', 'planet4-blocks-backend' ) . '</th>
					</tr>';
		foreach ( $with_classic_blocks as $post_with_classic ) {
			echo  '<tr><td><a href="' . get_permalink( $post_with_classic->ID ) . '" >' . $post_with_classic->ID . '</a></td>';
			echo  '<td><a href="post.php?post=' . $post_with_classic->ID . '&action=edit" >' . $post_with_classic->post_title . '</a></td></tr>';
		}
		echo '</table>';
		//phpcs:enable
    }
}
