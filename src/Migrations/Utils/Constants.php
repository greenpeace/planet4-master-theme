<?php

namespace P4\MasterTheme\Migrations\Utils;

/**
 * Set of constant to be used by the migration scripts.
 */
class Constants
{
    private const PREFIX_P4_BLOCKS = 'planet4-blocks';
    private const PREFIX_CORE_BLOCKS = 'core';

    public const BLOCK_MEDIA_VIDEO = self::PREFIX_P4_BLOCKS . '/media-video';

    public const BLOCK_EMBED = self::PREFIX_CORE_BLOCKS . '/embed';
    public const BLOCK_AUDIO = self::PREFIX_CORE_BLOCKS . '/audio';
    public const BLOCK_VIDEO = self::PREFIX_CORE_BLOCKS . '/video';
    public const BLOCK_GROUP = self::PREFIX_CORE_BLOCKS . '/group';
    public const BLOCK_HEADING = self::PREFIX_CORE_BLOCKS . '/heading';
    public const BLOCK_PARAGRAPH = self::PREFIX_CORE_BLOCKS . '/paragraph';

    public const POST_TYPES_PAGE = 'page';
    public const POST_TYPES_POST = 'post';
    public const POST_TYPES_ACTION = 'action';
    public const POST_TYPES_CAMPAIGN = 'campaign';

    public const ALL_POST_TYPES = [
        self::POST_TYPES_PAGE,
        self::POST_TYPES_POST,
        self::POST_TYPES_ACTION,
        self::POST_TYPES_CAMPAIGN,
    ];
}
