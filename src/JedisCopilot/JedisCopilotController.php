<?php

namespace P4\MasterTheme\JedisCopilot;

class JedisCopilotController
{
    private $openai_gateway;

    public function __construct($openai_gateway)
    {
        $this->openai_gateway = $openai_gateway;

        add_action('admin_init', [$this, 'communicate_with_chat_gpt']);
    }

    function communicate_with_chat_gpt() {
        if (!isset($_POST['post_id'])) {
            return;
        }
        if (!isset($_POST[JedisCopilotConstants::CALL_OPENAI_API_ACTION])) {
            return;
        }
        if (!$_POST[JedisCopilotConstants::CALL_OPENAI_API_ACTION] == JedisCopilotConstants::EXECUTE_OPENAI_API_ACTION) {
            return;
        }

        $post = get_post($_POST['post_id']);
    
        if (!$post) {
            return;
        }

        $blocks = parse_blocks($post->post_content);
        $text_content = self::get_text_content($blocks);

        $chat_gpt_propmt = get_option(JedisCopilotConstants::OPTIONS["prompt"], '');
        $chat_gpt_question = get_option(JedisCopilotConstants::OPTIONS["question"], '');

        $chat_answer = $this->openai_gateway->call($chat_gpt_propmt, $chat_gpt_question, $text_content);
        $chat_answer_content = $chat_answer["choices"][0]["message"]["content"];

        if (!$chat_answer_content) {
            return;
        }

        $chat_answer_content = self::clean_json($chat_answer_content);
        $chat_answer_content = json_decode($chat_answer_content, true);

        foreach (JedisCopilotConstants::PRINCIPLE_NAME as $principle) {
            $principle_large = $principle['large']; // e.g., "Justice"
            $principle_small = $principle['small']; // e.g., "justice"
    
            // Check and update score
            if (!empty($chat_answer_content[$principle_small]["score"])) {
                update_post_meta(
                    $_POST['post_id'], 
                    JedisCopilotConstants::PRINCIPLES_META[$principle_large][JedisCopilotConstants::SCORE], 
                    $chat_answer_content[$principle_small]["score"]
                );
            }
    
            // Check and update comment
            if (!empty($chat_answer_content[$principle_small]["comment"])) {
                update_post_meta(
                    $_POST['post_id'],
                    JedisCopilotConstants::PRINCIPLES_META[$principle_large][JedisCopilotConstants::COMMENT], 
                    $chat_answer_content[$principle_small]["comment"]
                );
            }
        }
    }

    private function get_text_content($blocks)
    {
        $text_content = "";

        $print_callback = function ($key, $value) {
            if (
                $key === "description" ||
                $key === "head" ||
                $key === "heading" ||
                $key === "title"
            ) {
                return $value;
            }
            if ($key === "innerHTML" && trim($value) !== "") {
                return strip_tags($value);
            }
            return ""; // Return an empty string for unhandled keys
        };

        $text_content .= self::iterate_array($blocks, $print_callback);

        return self::clean_text_format($text_content);
    }
    
    private function clean_text_format($text)
    {
        $formated_text = str_replace("\n", "", $text);
        $formated_text = str_replace("  ", "", $formated_text);
        $formated_text = strip_tags($formated_text);
        $formated_text = trim($formated_text);

        return $formated_text;
    }

    private function iterate_array($array, $callback)
    {
        $result = "";
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // If the value is an array, recursively call the function
                $result .= self::iterate_array($value, $callback);
            } else {
                // If it's not an array, apply the callback function to the value
                $result .= $callback($key, $value);
            }
        }
        return $result;
    }

    private function clean_json($json)
    {
        $cleaned_json = trim($json, "` \t\n\r\0\x0B");    
        $cleaned_json = preg_replace('/[^\x20-\x7E]/', '', $cleaned_json); // Remove non-printable characters
        $cleaned_json = preg_replace('/,\s*(\}|\])/', '$1', $cleaned_json); // Remove trailing commas
        $cleaned_json = str_replace('\"', '"', $cleaned_json); // Clean escaped quotes
        $cleaned_json = str_replace('json', '', $cleaned_json); // Remove 'json' string if found
        $cleaned_json = preg_replace("/'(.*?)'/", '"$1"', $cleaned_json);

        return $cleaned_json;
    }
}
