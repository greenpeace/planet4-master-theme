<?php

namespace P4\MasterTheme\JedisCopilot;

use WP_Query;

class JedisCopilotReportPage
{
    public function __construct()
    {
        add_action("admin_menu", [$this, "create_admin_page"]);
    }

    function create_admin_page()
    {
        add_menu_page(
            JedisCopilotConstants::JEDIS,
            JedisCopilotConstants::JEDIS,
            "manage_options",
            JedisCopilotConstants::PAGE_NAME,
            [$this, "create_jedis_report_page"],
            "dashicons-admin-generic"
        );
    }

    public function create_jedis_report_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap" style="max-width: 900px">';
        echo '<h1>'. JedisCopilotConstants::JEDIS .'</h1>';

        echo '<div></div>';
        echo '<div></div>';
    
        $args = [
            "post_type" => JedisCopilotConstants::ALL_POST_TYPES,
            "posts_per_page" => -1,
        ];

        $query = new WP_Query($args);

        if ($query->have_posts()) {

            echo '<div style="max-height: 70vh; overflow-y: scroll">';
            echo '<table class="wp-list-table widefat striped table-view-list">';
            echo '<tr><th>Page</th>';

            foreach (JedisCopilotConstants::PRINCIPLE_NAME as $name) {
                echo '<th>'. $name["large"] .'</th>';
            }
            
            echo '<th></th></tr>';
            
            while ($query->have_posts()) { 
                
                $query->the_post(); 
                        
                echo '<tr><td>';
                echo '<a href="' . get_edit_post_link( get_the_ID() ) . '">';
                echo get_the_title();
                echo '</a></td>';
                
                foreach (JedisCopilotConstants::PRINCIPLE_NAME as $key => $value) {
                    echo '<td title="' . self::get_details(get_the_ID(), $key) .'" style="vertical-align: middle">';
                    echo '<div style="height: 10px; width: 10px; background-color: ' . self::get_scores(get_the_ID(), $key) . '; border-radius: 50%;">';
                    echo '</div>';
                    echo '</td>';
                }
                
                echo '<td>';
                echo '<form method="post" action="">';
                echo '<input type="hidden" name="'. JedisCopilotConstants::CALL_OPENAI_API_ACTION .'" value="'. JedisCopilotConstants::EXECUTE_OPENAI_API_ACTION .'" />';
                echo '<input type="hidden" name="post_id" value="'. get_the_ID() .'" />';
                submit_button('Ask Copilot');
                echo '</form>';
                echo '</td></td></tr>';
                
                wp_reset_postdata(); 
            } 
            echo '</table></div>';
        }
        echo '</div>';
    }

    private function get_scores($post_id, $principle)
    {
        $meta = get_post_meta(
            $post_id, 
            JedisCopilotConstants::PRINCIPLES_META[JedisCopilotConstants::PRINCIPLE_NAME[$principle]["large"]][JedisCopilotConstants::SCORE], 
            true
        );
        if ($meta === "good") {
            return "red";
        }
        if ($meta === "needs improvement") {
            return "orange";
        }
        if ($meta === "bad") {
            return "red";
        }
        return "grey";
    }

    private function get_details($post_id, $principle)
    {
        $details = get_post_meta(
            $post_id, 
            JedisCopilotConstants::PRINCIPLES_META[JedisCopilotConstants::PRINCIPLE_NAME[$principle]["large"]][JedisCopilotConstants::COMMENT], 
            true
        );
        if ($details) {
            return $details;
        }
        return "No information available. Ask Copilot.";
    }
} 
?>"