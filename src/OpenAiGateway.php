<?php

namespace P4\MasterTheme;

use P4\MasterTheme\JedisCopilot\JedisCopilotConstants;

/**
 * @see description().
 */
class OpenAiGateway
{
    public const API_URL = 'https://api.openai.com/v1/chat/completions';

    public const API_MODEL = 'gpt-3.5-turbo';

    public function call($prompt, $question, $extra_data = false)
    {
        $api_key = get_option(JedisCopilotConstants::OPTIONS["key"], '');

        $headers = [];
        $data = [];
        $data["messages"] = [];
        
        $headers[0] = "Content-Type: application/json";
        $headers[1] = "Authorization: Bearer $api_key";
        
        $data["model"] = self::API_MODEL;

        $data["messages"][0]["role"] = "system";
        $data["messages"][0]["content"] = $prompt;

        $data["messages"][1]["role"] = "user";
        $data["messages"][1]["content"] = $question;

        if ($extra_data) {
            $data["messages"][2]["role"] = "user";
            $data["messages"][2]["content"] = $extra_data;
        }
        
        $ch = curl_init(self::API_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return 'Error: ' . curl_error($ch);
        } else {
            $result = json_decode($response, true);
            return $result;
        }
        
        curl_close($ch);
    }
}
