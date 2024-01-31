<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Contains EnformQuestionsListTable class declaration.
 */

namespace P4\MasterTheme\Controllers;

if (! class_exists('\WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use P4\MasterTheme\Controllers\EnsapiController as Ensapi;

/**
 * Class EnformQuestionsListTable.
 * Creates a list table for available en questions overriding WordPress List Table.
 *
 * @see \WP_List_Table
 * @link https://developer.wordpress.org/reference/classes/wp_list_table/
 */
class EnformQuestionsListTable extends \WP_List_Table
{
    /**
     * Store errors from en api.
     */
    private string $error;

    /**
     * Type of questions. Either 'GEN' or 'OPT'.
     */
    private string $type;

    /**
     * Enform_Questions_List_Table constructor.
     *
     * @param string $type Type of questions that should be displayed. Either 'GEN' or 'OPT'.
     */
    public function __construct(string $type = 'GEN')
    {
        parent::__construct(
            [
                'ajax' => false,
            ]
        );
        $this->error = '';
        $this->type = $type;
    }

    /**
     * Implements parent's abstract function.
     * Prepares the list of items for displaying.
     *
     * @see \WP_List_Table::prepare_items
     */
    public function prepare_items(): void
    {
        $supporter_questions = [];
        $main_settings = get_option('p4en_main_settings');

        if (isset($main_settings['p4en_private_api'])) {
            $ens_private_token = $main_settings['p4en_private_api'];
            $ens_api = new Ensapi($ens_private_token);
            $supporter_questions = $ens_api->get_supporter_questions();

            if (is_array($supporter_questions)) {
                $supporter_questions = array_filter($supporter_questions, [$this, 'check_type']);
            } else {
                $this->error = $supporter_questions . ' : ' .
                    __('Could not fetch results from engaging networks', 'planet4-engagingnetworks-backend');
            }
        }

        $columns = $this->get_columns();

        $hidden = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];
        $this->items = is_array($supporter_questions) ? $supporter_questions : [];
    }

    /**
     * Used to filter items array based on question type.
     *
     * @param array $item Item in array.
     */
    private function check_type(array $item): bool
    {
        return $this->type === $item['type'];
    }

    /**
     * Implements parent's abstract function.
     * Get a list of columns. The format is:
     * 'internal-name' => 'Title'
     *
     * @return array Columns array.
     */
    public function get_columns(): array
    {
        $columns = [
            'id' => __('Id', 'planet4-engagingnetworks-backend'),
            'name' => __('Name', 'planet4-engagingnetworks-backend'),
            'type' => __('Type', 'planet4-engagingnetworks-backend'),
            'actions' => __('Actions', 'planet4-engagingnetworks-backend'),
        ];

        return $columns;
    }

    /**
     * Generates content for a column that does not have each own function defined.
     *
     * @param object $item array Column data.
     * @param string $column_name Column Name.
     *
     * @return string Content for column.
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint -- compatible with parent class
    protected function column_default($item, $column_name): string
    {
        switch ($column_name) {
            case 'id':
            case 'name':
            case 'type':
                return $item[$column_name];
        }
        return '';
    }

    /**
     * Generates content for the actions column.
     *
     * @param array $item Column data.
     *
     * @return string Content for actions column.
     */
    public function column_actions(array $item): string
    {
        $data_attributes = [
            'id' => $item['id'],
            'name' => $item['name'],
            'type' => $item['type'],
        ];

        $attributes_string = '';
        foreach ($data_attributes as $attr => $value) {
            $attributes_string .= " data-$attr=\"" . esc_attr($value) . '"';
        }

        return '<button class="add-en-field" ' . $attributes_string . '>' .
            __('Add', 'planet4-engagingnetworks-backend') . '</button>';
    }

    /**
     * Overrides parent function to disable nonce generation, bulk actions and pagination.
     * Used to display errors (if any) that come from en api.
     *
     * @param string $which Navigation position.
     *
     * @see \WP_List_Table::display_tablenav
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint -- compatible with parent class
    protected function display_tablenav($which): void
    {
        if (empty($this->error) || 'top' !== $which) {
            return;
        }
        echo '<div><p>' . esc_html($this->error) . '</p></div>';
    }
}
