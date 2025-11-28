<?php

namespace P4\MasterTheme\Blocks;

class Register
{
    /**
     * Requires a file `block.json`
     * in assets/(src|build)/blocks/{blockDirName}/
     */
    public static function registerFromAssets(
        string $blockDirName,
        array $properties = []
    ): void {
        register_block_type(
            get_template_directory() . '/assets/build/blocks/' . $blockDirName,
            $properties
        );
    }
}
