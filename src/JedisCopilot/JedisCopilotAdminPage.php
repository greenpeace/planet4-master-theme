<?php

namespace P4\MasterTheme\JedisCopilot;

class JedisCopilotAdminPage
{
    public function __construct()
    {
        add_action("admin_menu", [$this, "create_admin_page"]);
    }

    function create_admin_page()
    {
        $title = JedisCopilotConstants::JEDIS . " Settings";

        add_submenu_page(
            JedisCopilotConstants::PAGE_NAME,
            $title,
            $title,
            "manage_options",
            JedisCopilotConstants::SUB_PAGE_NAME,
            [$this, "create_jedis_settings_page"],
            "dashicons-admin-generic"
        );
    }

    public function create_jedis_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $key = JedisCopilotConstants::OPTIONS["key"];
        $prompt = JedisCopilotConstants::OPTIONS["prompt"];
        $question = JedisCopilotConstants::OPTIONS["question"];
    
        if (isset($_POST['submit'])) {
            if (isset($_POST['jedis_nonce_field'])) {
                if (wp_verify_nonce($_POST['jedis_nonce_field'], 'jedis_nonce_action')) {
                    update_option($key, sanitize_text_field($_POST[$key]));
                    update_option($prompt, sanitize_textarea_field($_POST[$prompt]));
                    update_option($question, sanitize_textarea_field($_POST[$question]));

                    echo '<div id="message" class="updated notice is-dismissible"><p>Settings saved.</p></div>';
                }
            }
        }
        ?>
    
        <div class="wrap">
            <h1><?php echo JedisCopilotConstants::JEDIS . " Settings"; ?></h1>

            <form method="post" action="" style="max-width: 900px;">
                <?php wp_nonce_field('jedis_nonce_action', 'jedis_nonce_field'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="<?php echo $key; ?>">OpenAI Secret Key</label>
                        </th>
                        <td>
                            <input type="text" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo esc_attr(get_option($key, '')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo $prompt; ?>">ChatGPT Prompt</label>
                        </th>
                        <td>
                            <textarea name="<?php echo $prompt; ?>" id="<?php echo $prompt; ?>" rows="15" class="large-text"><?php echo esc_textarea(get_option('chat_gpt_prompt', '')); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo $question; ?>">ChatGPT Question</label>
                        </th>
                        <td>
                            <textarea name="<?php echo $question; ?>" id="<?php echo $question; ?>" rows="15" class="large-text"><?php echo esc_textarea(get_option('chat_gpt_question', '')); ?></textarea>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
} 
?>