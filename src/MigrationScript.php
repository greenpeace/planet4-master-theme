<?php

namespace P4\MasterTheme;

use Exception;

/**
 * Abstract class to enforce the signature of a migration function.
 * The "execute" function is abstract to keep implementations as simple as possible. These functions will be using
 * WordPress's global functions to interact with the database (either through the high level API or using raw SQL,
 * whatever fits the specific case best).
 */
abstract class MigrationScript
{
    /**
     * Get a unique identifier, achieved here by using the FQCN.
     *
     * @return string The unique identifier.
     */
    public static function get_id(): string
    {
        return static::class;
    }

    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     *
     */
    abstract protected static function execute(MigrationRecord $record): void;

    /**
     * Log the time and run the migration.
     *
     * @return MigrationRecord Data on the migration run.
     */
    public static function run(): MigrationRecord
    {
        $record = MigrationRecord::start(static::class);

        try {
            static::execute($record);
            $record->succeed();
        } catch (Exception $exception) {
            $message = 'Migration ' . $record->get_migration_id() . ' failed. Message: ' . $exception->getMessage();
            $record->fail($message);
        }

        $record->done();

        return $record;
    }

    /**
     * Check whether a block exists in page.
     *
     * @param array $block - A block data array.
     * @param string $blockName - The name of the block to check.
     */
    public static function check_is_valid_block(array $block, string $blockName): bool
    {
        // Check if the block is valid.
        if (!is_array($block)) {
            return false;
        }

        // Check if the block has a 'blockName' key.
        if (!isset($block['blockName'])) {
            return false;
        }

        // Check if the block is the desired block. If not, abort.
        return $block['blockName'] === $blockName;
    }
}
