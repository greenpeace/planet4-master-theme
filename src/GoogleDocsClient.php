<?php

namespace P4\MasterTheme;

use Google\Client;
use Google\Service\Sheets;

/**
 * Handles calling the Google Docs API.
 */
final class GoogleDocsClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Create from JSON config.
     *
     * @param string $config The JSON config.
     *
     * @return self|null Client if auth exists.
     */
    public static function from_account_config(string $config): ?self
    {
        $client = new Client();
        $client->setApplicationName('Planet 4');
        $client->setScopes([ Sheets::SPREADSHEETS ]);
        $client->setAccessType('offline');

        try {
            $auth = ! $config
                ? '/app/source/sheets-service-account.json'
                : json_decode(
                    $config,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            $client->setAuthConfig($auth);
            $instance = new self();
            $instance->client = $client;

            return $instance;
        } catch (\Exception $e) {
            if (function_exists('\Sentry\captureException')) {
                \Sentry\captureException($exception);
            }
            return null;
        }
    }

    /**
     * Fetch a sheet from the API by its id.
     *
     * @param string $sheet_id The id of the sheet to fetch.
     *
     * @return Spreadsheet|null The sheet if found, otherwise null.
     */
    public function get_sheet(string $sheet_id): ?Spreadsheet
    {
        try {
            $sheets = new Sheets($this->client);
            // Currently, it only needs until F, but we can fetch a bit more to be sure.
            $range = 'A1:I';

            $rows = $sheets->spreadsheets_values->get($sheet_id, $range, [ 'majorDimension' => 'ROWS' ]);

            if (! $rows['values']) {
                return null;
            }

            return Spreadsheet::from_google_response($rows[0], array_slice($rows['values'], 1));
        } catch (\Exception $exception) {
            if (function_exists('\Sentry\captureException')) {
                \Sentry\captureException($exception);
            }
            return null;
        }
    }
}
