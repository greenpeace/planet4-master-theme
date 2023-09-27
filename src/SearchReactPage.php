<?php

namespace P4\MasterTheme;

/**
 * Abstract Class SearchReactPage
 */
class SearchReactPage
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        do_action('wp_head');
        do_action('wp_footer');

        $this->render_search_page();
    }

    public function render_search_page(): void
    {
        $file = get_template_directory_uri() . '/assets/build/search_react_page.js';
        echo '
            <script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function() {
                    var script = document.createElement("script");
                    script.src = "' . $file . '";
                    document.getElementsByTagName("head")[0].appendChild(script);
                });
            </script>
        ';
    }
}
