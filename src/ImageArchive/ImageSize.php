<?php

namespace P4\MasterTheme\ImageArchive;

/**
 * Entity for sizes of an image.
 */
class ImageSize implements \JsonSerializable
{
    /**
     * @var string The media url.
     */
    private $url;

    /**
     * @var int The width in pixels.
     */
    private $width;

    /**
     * @var int The heigth in pixels.
     */
    private $height;

    /**
     * Extract all available sizes from the media API data for an image.
     *
     * @param array $data The data of an image.
     *
     * @return self[] All available sizes from the data.
     */
    public static function all_from_api_response($data): array
    {
        $keys = [
            'Path_TR7',
            'Path_TR1_COMP_SMALL',
            'Path_TR1',
            'Path_TR4',
            'Path_TR1_COMP',
            'Path_TR2',
            'Path_TR3',
        ];

        return array_reduce(
            $keys,
            static function ($carry, $key) use ($data) {
                if (! empty($data[ $key ]['URI'])) {
                    $size = new self();

                    $size->url = $data[ $key ]['URI'];
                    $size->width = (int) $data[ $key ]['Width'];
                    $size->height = (int) $data[ $key ]['Height'];

                    $carry[] = $size;
                }

                return $carry;
            },
            []
        );
    }

    /**
     * @inheritDoc
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'url' => $this->url,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    /**
     * Accessor for url.
     *
     * @return string The url.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Accessor for width.
     *
     * @return int The width.
     */
    public function get_width()
    {
        return $this->width;
    }
}
