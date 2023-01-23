<?php

namespace P4\MasterTheme\Post;

use IntlBreakIterator;
use DOMDocument;
use DOMXpath;

/**
 * This class calculates a reading time from a post content.
 */
class ReadingTimeCalculator
{
    /**
     * Default Word per minute reading speed.
     *
     */
    public const DEFAULT_WPM = 265;

    /**
     * Default time spent on an image, in seconds.
     * With progression from max to min.
     * Based on https://blog.medium.com/read-time-and-you-bc2048ab620c#c072.
     *
     */
    public const IMAGE_TIME = [
        'max' => 12,
        'min' => 3,
    ];

    /**
     * @var string Language locale.
     */
    private string $locale;

    /**
     * @var int Words per minute reading speed.
     */
    private int $wpm;

    /**
     * @var \IntlBreakIterator Word iterator.
     */
    private \IntlBreakIterator $breaker;

    /**
     * @var array Counting options.
     */
    private array $options = [
        'count_images' => true,
        'count_videos' => false,
    ];

    /**
     * Construct a new calculator instance.
     *
     * @param string $locale Locale.
     * @param int    $wpm    Word per minute reading speed.
     */
    public function __construct(string $locale, int $wpm = self::DEFAULT_WPM)
    {
        $this->locale = $locale;
        $this->wpm = $wpm > 0 ? $wpm : self::DEFAULT_WPM;
        $this->breaker = IntlBreakIterator::createWordInstance($this->locale);
    }

    /**
     * Calculate reading time in seconds.
     *
     * Text reading time based on Word per minute speed
     * Image reading time based on
     *   max_time for the first image, max_time - 1 for the second, etc. with min_time for the tail.
     *   Cf. https://en.wikipedia.org/wiki/Arithmetic_progression#Sum
     *
     * @param string $post_content Content.
     *
     * @return int Reading time in seconds.
     */
    public function get_time(string $post_content): int
    {
        $time = 0;
        $content = $this->extract_readable_content($post_content);

        // Calculate text reading time.
        $word_count = $this->get_word_count($content['text'] ?? '');
        $time += ( $word_count / $this->wpm ) * 60;

        // Calculate images watching time.
        if ($this->options['count_images'] && ! empty($content['images'])) {
            $img_count = count($content['images']);
            $real_min = max(self::IMAGE_TIME['min'], self::IMAGE_TIME['max'] - $img_count + 1);
            $overflow = max(0, $img_count - ( self::IMAGE_TIME['max'] - self::IMAGE_TIME['min'] + 1 ));

            $time += ( ( $img_count - $overflow ) * ( self::IMAGE_TIME['max'] + $real_min ) / 2 );
            $time += self::IMAGE_TIME['min'] * $overflow;
        }

        // @todo: Calculate videos watching time.

        return $time;
    }

    /**
     * Get the word count.
     *
     * @param string $text_content The post content.
     *
     * @return int The word count.
     */
    public function get_word_count(string $text_content): int
    {
        $word_count = 0;
        $this->breaker->setText($text_content);
        while ($this->breaker->next() !== IntlBreakIterator::DONE) {
            if (IntlBreakIterator::WORD_NONE === $this->breaker->getRuleStatus()) {
                continue;
            }
            ++$word_count;
        }
        return $word_count;
    }

    /**
     * Return formatted reading time.
     *
     * @param string $post_content Content.
     *
     * @return int Reading time in seconds.
     */
    public function get_formatted_time(string $post_content): string
    {
        $time = $this->get_time($post_content);

        // translators: %d = Number of minutes for reading time.
        return sprintf(__('%d min read', 'planet4-master-theme'), round($time / 60));
    }

    /**
     * @param string $content The post content.
     *
     * @return array Sanitized post content.
     */
    public function extract_readable_content(string $content): array
    {
        return [
            'text' => wp_strip_all_tags($content),
            'images' => $this->extract_images($content),
            'videos' => $this->extract_videos($content),
        ];
    }

    /**
     * Extract images from content.
     *
     * @param string $content The post content.
     *
     * @return array
     */
    private function extract_images(string $content): array
    {
        if (! $this->options['count_images']) {
            return [];
        }

        $doc = new DOMDocument();
        $doc->loadHTML($content, \LIBXML_NOERROR);
        $xpath = new DOMXpath($doc);
        $imgs = $xpath->query('//img');

        return iterator_to_array($imgs);
    }

    /**
     * Extract videos from content.
     *
     * @param string $content The post content.
     *
     * @return array
     */
    private function extract_videos(string $content): array
    {
        if (! $this->options['count_videos']) {
            return [];
        }

        $video_blocks = [ 'core/embed' ];

        $blocks = has_blocks($content) ? parse_blocks($content) : null;
        if (empty($blocks)) {
            return [];
        }

        $videos = array_filter(
            $blocks,
            fn ($b) => in_array($b['blockName'], $video_blocks, true)
        );

        return array_column($videos, 'attrs');
    }
}
