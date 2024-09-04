<?php

namespace P4\MasterTheme\JedisCopilot;

use P4\MasterTheme\JedisCopilot\JedisCopilotConstants;

class JedisCopilotMetabox
{
    public function __construct()
    {
        add_action("add_meta_boxes", [$this, "create_meta_box"]);
    }

    public function create_meta_box()
    {
        $title = JedisCopilotConstants::JEDIS . "Analysis";

        add_meta_box(
            JedisCopilotConstants::PAGE_NAME,
            $title,
            [$this, "my_custom_meta_box_callback"],
            JedisCopilotConstants::ALL_POST_TYPES,
            "normal",
            "high" 
        );
    }

    public function my_custom_meta_box_callback($post)
    {
        echo '
        <table class="wp-list-table widefat striped table-view-list">
            <tr>
                <th>Principle</th>
                <th>Score</th>
                <th>Details</th>
            </tr>';

        foreach (JedisCopilotConstants::PRINCIPLES_META as $key => $value) { ?>
            <tr>
                <td><?php echo $key; ?></td>
                <td><?php echo get_post_meta($post->ID, $value[JedisCopilotConstants::SCORE], true); ?></td>
                <td><?php echo get_post_meta($post->ID, $value[JedisCopilotConstants::COMMENT], true); ?></td>
            </tr>
        <?php }

        echo '</table>';
    }
}
