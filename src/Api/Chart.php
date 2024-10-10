<?php

declare(strict_types=1);

namespace P4\MasterTheme\Api;

use WP_REST_Server;

/**
 * Chart block API
 */
class Chart
{
    /**
     * Register endpoint to read settings.
     *
     * @example GET /wp-json/planet4/v1/chart/data/
     */
    public static function register_endpoint(): void
    {
        /**
         * Endpoint local data used by the Chart block
         */
        register_rest_route(
            'planet4/v1',
            'chart/data',
            [
                [
                    'permission_callback' => static function () {
                        return true;
                    },
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function ($request) {
                        $params = $request->get_params();

                        $dataType = $params['dataType'];
                        $chartType = $params['chartType'];

                        // Force to return empty
                        $response = [];

                        // Dummy data
                        if ($dataType === 'json') {
                            if ($chartType === 'bar') {
                                $response = [
                                    ['Country' => "United States", 'Value' => 12394],
                                    ['Country' => "Russia", 'Value' => 6148],
                                    ['Country' => "Germany (FRG)", 'Value' => 1653],
                                    ['Country' => "France", 'Value' => 2162],
                                    ['Country' => "United Kingdom", 'Value' => 1214],
                                    ['Country' => "China", 'Value' => 1131],
                                    ['Country' => "Spain", 'Value' => 814],
                                    ['Country' => "Netherlands", 'Value' => 1167],
                                    ['Country' => "Italy", 'Value' => 1263],
                                    ['Country' => "Israel", 'Value' => 12394],
                                ];
                            }

                            if ($chartType === 'line') {
                                $response = [
                                    ["Date" => "2001-01-01", "population" => 208689],
                                    ["Date" => "2002-01-01", "population" => 286984],
                                    ["Date" => "2003-01-01", "population" => 304300],
                                    ["Date" => "2004-01-01", "population" => 383796],
                                    ["Date" => "2005-01-01", "population" => 467630],
                                    ["Date" => "2006-01-01", "population" => 527636],
                                    ["Date" => "2007-01-01", "population" => 627242],
                                    ["Date" => "2008-01-01", "population" => 159181],
                                    ["Date" => "2009-01-01", "population" => 199044],
                                    ["Date" => "2010-01-01", "population" => 194670],
                                    ["Date" => "2011-01-01", "population" => 190724],
                                    ["Date" => "2012-01-01", "population" => 165444],
                                    ["Date" => "2013-01-01", "population" => 130320],
                                    ["Date" => "2014-01-01", "population" => 146020],
                                    ["Date" => "2015-01-01", "population" => 184261],
                                    ["Date" => "2016-01-01", "population" => 208634],
                                    ["Date" => "2017-01-01", "population" => 202824],
                                    ["Date" => "2018-01-01", "population" => 198279],
                                    ["Date" => "2019-01-01", "population" => 180075],
                                    ["Date" => "2020-01-01", "population" => 148755],
                                    ["Date" => "2021-01-01", "population" => 142915],
                                    ["Date" => "2022-01-01", "population" => 162429],
                                    ["Date" => "2024-01-01", "population" => 165680],
                                    ["Date" => "2024-01-01", "population" => 188254],
                                ];
                            }
                        }

                        return rest_ensure_response($response);
                    },
                ],
            ]
        );
    }
}
