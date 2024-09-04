<?php

namespace P4\MasterTheme\JedisCopilot;

class JedisCopilotConstants
{
    public const JEDIS = "JEDIS Copilot";
    public const PAGE_NAME = "jedis-copilot";
    public const SUB_PAGE_NAME = "jedis-copilot-settings";

    public const SCORE = "score";
    public const COMMENT = "comment";
    public const ALL_POST_TYPES = ["post", "page", "P4_action", "campaign"];
    
    public const CALL_OPENAI_API_ACTION = 'call_openai';
    public const EXECUTE_OPENAI_API_ACTION = 'execute_openai';

    public const OPTIONS = [
        "key" => "openai_key",
        "prompt" => "chat_gpt_prompt",
        "question" => "chat_gpt_question",
    ];

    public const PRINCIPLE_NAME = [
        "J" => [
            "large" => "Justice",
            "small" => "justice"
        ],
        "E" => [
            "large" => "Equity",
            "small" => "equity"
        ],
        "D" => [
            "large" => "Diversity",
            "small" => "diversity"
        ],
        "I" => [
            "large" => "Inclusion",
            "small" => "inclusion"
        ],
        "S" => [
            "large" => "Safety",
            "small" => "safety"
        ],
    ];

    public const PRINCIPLES_META = [
        self::PRINCIPLE_NAME["J"]["large"] => [
            self::SCORE => "_" . self::PRINCIPLE_NAME["J"]["small"] . "_" . self::SCORE,
            self::COMMENT => "_" . self::PRINCIPLE_NAME["J"]["small"] . "_" . self::COMMENT,
        ],
        self::PRINCIPLE_NAME["E"]["large"] => [
            self::SCORE => "_" . self::PRINCIPLE_NAME["E"]["small"] . "_" . self::SCORE,
            self::COMMENT => "_" . self::PRINCIPLE_NAME["E"]["small"] . "_" . self::COMMENT,
        ],
        self::PRINCIPLE_NAME["D"]["large"] => [
            self::SCORE => "_" . self::PRINCIPLE_NAME["D"]["small"] . "_" . self::SCORE,
            self::COMMENT => "_" . self::PRINCIPLE_NAME["D"]["small"] . "_" . self::COMMENT,
        ],
        self::PRINCIPLE_NAME["I"]["large"] => [
            self::SCORE => "_" . self::PRINCIPLE_NAME["I"]["small"] . "_" . self::SCORE,
            self::COMMENT => "_" . self::PRINCIPLE_NAME["I"]["small"] . "_" . self::COMMENT,
        ],
        self::PRINCIPLE_NAME["S"]["large"] => [
            self::SCORE => "_" . self::PRINCIPLE_NAME["S"]["small"] . "_" . self::SCORE,
            self::COMMENT => "_" . self::PRINCIPLE_NAME["S"]["small"] . "_" . self::COMMENT,
        ],
    ];

    public const DEFAULT_CHAT_TEXT = [
        'prompt' => "
            You are a specialist on Greenpeace's JEDIS principles (Justice, Equity, Diversity, Inclusion, and Safety), which guide Greenpeace in fostering a more just, equitable, diverse, inclusive, and safe environment in its advocacy.
            Here are the key details of each principle:
            Justice: Ensuring fairness by addressing the intersectionality of environmental and social issues, particularly for marginalized communities most impacted by environmental harm.
            Equity: Acknowledging and addressing systemic disadvantages by providing tailored resources and opportunities to historically marginalized groups.
            Diversity: Embracing a wide range of identities, perspectives, and experiences to enhance Greenpeace’s effectiveness through innovative solutions.
            Inclusion: Creating a culture where all voices are heard and valued, with active participation from underrepresented groups.
            Safety: Promoting physical, emotional, and psychological safety for staff, volunteers, and the communities Greenpeace works with, ensuring a respectful and secure environment.
        ",
        'question' => '
            1. Imagine you are a Greenpeace web editor with in-depth knowledge and experience of the JEDIS principles.
            2. Review the text provided at the end of this message.
            3. Assess whether the content aligns with the JEDIS principles and explain how each principle is applied or not.
            4. Provide your evaluation in JSON format, scoring each principle as "good", "needs improvement", or "bad", along with suggestions for improvement.
            5. Use this format for the JSON response:
                {
                "justice": { "score": "", "comment": "" },
                "equity": { "score": "", "comment": "" },
                "diversity": { "score": "", "comment": "" },
                "inclusion": { "score": "", "comment": "" },
                "safety": { "score": "", "comment": "" }
                }
            ',
    ];
}
