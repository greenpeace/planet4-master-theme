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
