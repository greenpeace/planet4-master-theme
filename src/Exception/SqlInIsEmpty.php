<?php

namespace P4\MasterTheme\Exception;

use Exception;

/**
 * An empty array was given to be used in a SQL IN query, but that doesn't work.
 */
class SqlInIsEmpty extends Exception
{
}
