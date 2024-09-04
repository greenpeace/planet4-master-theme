<?php

namespace P4\MasterTheme;

use P4\MasterTheme\OpenAiGateway;
use P4\MasterTheme\JedisCopilot\JedisCopilotAdminPage;
use P4\MasterTheme\JedisCopilot\JedisCopilotReportPage;
use P4\MasterTheme\JedisCopilot\JedisCopilotMetabox;
use P4\MasterTheme\JedisCopilot\JedisCopilotController;

class JedisCopilot
{
    public function __construct()
    {
        $open_ai = new OpenAiGateway();

        new JedisCopilotReportPage();
        new JedisCopilotAdminPage();
        new JedisCopilotMetabox();
        new JedisCopilotController($open_ai);
    }
}
