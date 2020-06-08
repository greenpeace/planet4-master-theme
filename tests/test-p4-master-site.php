<?php
/**
 * MasterThemeTest Class
 *
 * @package P4MT
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Class P4MasterSite
 */
class P4MasterSite extends TestCase {

	/**
	 * Test cases provider
	 */
	public function contentImageProvider(): array {
		return [
			'basic test'          => [ '<img src="img-url" />', '<img class="lazyload" data-src="img-url" />' ],
			'all attrs'           => [
				'<img src="img-url" srcset="img-srcset" sizes="img-srcsizes" />',
				'<img class="lazyload" data-src="img-url" data-srcset="img-srcset" data-sizes="img-srcsizes" />',
			],
			'll present 1'        => [ '<img src="img-url" class="lazyload" />', null ],
			'll present 2'        => [ '<img src="img-url" class="foo lazyload" />', null ],
			'll present 3'        => [ '<img src="img-url" class="lazyload bar" />', null ],
			'll present 4'        => [ '<img src="img-url" class="foo lazyload bar" />', null ],
			'data-src present'    => [ '<img src="img-url" data-src="src" />', null ],
			'data-srcset present' => [ '<img src="img-url" data-srcset="srcset" />', null ],
			'data-sizes'          => [ '<img src="img-url" data-sizes="sizes" />', null ],
		];
	}

	/**
	 * @dataProvider contentImageProvider
	 *
	 * If $expected is null, it will be replaced by $content,
	 * to check if nothing has changed
	 *
	 * @param string  $content Html content given.
	 * @param ?string $expected Html content expected.
	 */
	public function testMakeContentImageLazyLoad( string $content, ?string $expected ): void {
		/** @var P4_Master_Site $p4 */
		$p4 = $this->getMockBuilder( P4_Master_Site::class )
			->disableOriginalConstructor()
			->setMethodsExcept( [ 'make_content_images_lazyload' ] )
			->getMock();

		if ( null === $expected ) {
			$expected = $content;
		}

		$this->assertEquals( $expected, $p4->make_content_images_lazyload( $content ) );
	}
}
