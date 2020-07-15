<?php
/**
 * MasterThemeTest Class
 *
 * @package P4MT
 */

declare(strict_types=1);

use P4\MasterTheme\MasterSite;
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
		/** @var MasterSite $p4 */
		$p4 = $this->getMockBuilder( MasterSite::class )
			->disableOriginalConstructor()
			->setMethodsExcept( [ 'make_content_images_lazyload' ] )
			->getMock();

		if ( null === $expected ) {
			$expected = $content;
		}

		$this->assertEquals( $expected, $p4->make_content_images_lazyload( $content ) );
	}

	/**
	 * Test cases for cloudflare
	 */
	public function cloudflareImageProvider(): array {
		return [
			[
				[ 'path/to/my/image.jpg', 'path/to/my/image.jpg 1200w,path/to/my/image1.jpg 300w', '' ],
				'/cdn-cgi/image/format=auto,width=1200/path/to/my/image.jpg',
			],
			[
				[ 'http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE.jpg 1200w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-300x210.jpg 300w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-1024x715.jpg 1024w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-768x536.jpg 768w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-487x340.jpg 487w', '', '' ],
				'/cdn-cgi/image/format=auto,fit=contain,width=1200/http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE.jpg 1200w, /cdn-cgi/image/format=auto,fit=contain,width=300/http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-300x210.jpg 300w, /cdn-cgi/image/format=auto,fit=contain,width=1024/http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-1024x715.jpg 1024w, /cdn-cgi/image/format=auto,fit=contain,width=768/http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-768x536.jpg 768w, /cdn-cgi/image/format=auto,fit=contain,width=487/http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-487x340.jpg 487w',
			],
			[
				[ 'http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE.jpg', 'http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE.jpg 1200w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-300x210.jpg 300w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-1024x715.jpg 1024w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-768x536.jpg 768w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-487x340.jpg 487w', 'optionA=valA' ],
				'/cdn-cgi/image/format=auto,optionA=valA,width=1200/http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE.jpg',
			],
			[
				[ 'http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE.jpg 1200w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-300x210.jpg 300w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-1024x715.jpg 1024w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-768x536.jpg 768w, http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-487x340.jpg 487w', '', 'optionA=valA' ],
				'/cdn-cgi/image/format=auto,optionA=valA,fit=contain,width=1200/http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE.jpg 1200w, /cdn-cgi/image/format=auto,optionA=valA,fit=contain,width=300/http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-300x210.jpg 300w, /cdn-cgi/image/format=auto,optionA=valA,fit=contain,width=1024/http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-1024x715.jpg 1024w, /cdn-cgi/image/format=auto,optionA=valA,fit=contain,width=768/http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-768x536.jpg 768w, /cdn-cgi/image/format=auto,optionA=valA,fit=contain,width=487/http://www.planet4.test/wp-content/uploads/2020/06/GP1STWPE-487x340.jpg 487w',
			],
		];
	}

	/**
	 * @dataProvider cloudflareImageProvider
	 *
	 * @param array  $params   Parameters for img_to_cloudflare().
	 * @param string $expected Resulting URL expected.
	 */
	public function testImgToCloudflare( array $params, string $expected ): void {
		/** @var MasterSite $p4 */
		$p4 = $this->getMockBuilder( MasterSite::class )
		->disableOriginalConstructor()
		->setMethodsExcept( [ 'img_to_cloudflare' ] )
		->getMock();

		$this->assertEquals( $expected, $p4->img_to_cloudflare( ...$params ) );
	}
}
