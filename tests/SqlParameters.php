<?php

/**
 * P4 Test Case Class
 *
 * @package P4MT
 */

/**
 * Class SqlParameters.
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class SqlParameters extends \PHPUnit\Framework\TestCase
{
    /**
     * Ensure that in whichever order the sql is constructed, it will add the params in the right place.
     */
    public function testOrder(): void
    {
        $params = new \P4\MasterTheme\SqlParameters();

        $part_b = 'part B with param 1: ' . $params->string('param 1');
        $part_a = 'part A with param 2: ' . $params->int(2);
        $part_c = 'part C with param 3: ' . $params->identifier('param 3');

        $this->assertEquals(
            'part A with param 2: %2$d,part B with param 1: \'%1$s\',part C with param 3: `%3$s`',
            implode(',', [ $part_a, $part_b, $part_c ])
        );

        $this->assertEquals(
            [ 'param 1', 2, 'param 3' ],
            $params->get_values()
        );
    }
}
