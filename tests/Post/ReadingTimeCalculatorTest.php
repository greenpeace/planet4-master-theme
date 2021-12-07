<?php
/**
 * ReadingTimeCalculator Test Class
 *
 * @package P4MT
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use P4\MasterTheme\Post\ReadingTimeCalculator;

/**
 * Test cases for Post\ReadingTimeCalculator
 */
final class ReadingTimeCalculatorTest extends TestCase {

	/**
	 * Format: [
	 *  (string) locale,
	 *  (string) text,
	 *  (int) expected word count
	 * ]
	 *
	 * @return array
	 */
	public function wordCountProvider() {
		return [
			'Arabic word count'  => [
				'ar',
				'يداً بيد نحمي مقدرة كوكب الأرض في الحفاظ على استمرارية أشكال الحياة التي تؤويها. يداً بيد نتحدى سلطة الملوثين ونفوذهم المؤثر على صناع القرار. يداً بيد نواجه الذين يعملون جاهدين على إبقائنا في دوامة الممارسات الهدامة. يداً بيد نعمل على تحديد الأساليب التي تدير العالم بطريقة تهدد البيئة، ونسعى إلى تغييرها لتعمل بانسجام وتناغم معها.',
				55,
			],
			'Greek word count'   => [
				'el_GR',
				'Αν θες έναν καλύτερο κόσμο, ασφαλή, δίκαιο και ελπιδοφόρο, θα προσπαθήσουμε μαζί να τον δημιουργήσουμε. Αν έχεις ιδέες για το πώς θα το καταφέρουμε, θέλουμε να μάθουμε από εσένα. Ας κάνουμε μαζί όνειρα, σχέδια και πράξεις.',
				36,
			],
			'Hebrew word count'  => [
				'he_IL',
				'דמיינו לכם עולם בהם יערות משגשגים והים מלא חיים. מקום בו האנרגיה שלנו נקייה כמו מים היוצאים ממעיין. מקום בו לכולם יש ביטחון, כבוד ושמחת חיים. אנחנו לא יכולים לבנות את העתיד הזה לבד, אבל אנחנו יכולים לבנות אותו ביחד.',
				40,
			],
			'Latin word count'   => [
				'en_GB',
				'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent laoreet quis lectus quis faucibus. Proin vitae ligula quis odio tincidunt ultricies. Suspendisse potenti. Donec sit amet justo ac justo mattis convallis eget eu ex. Etiam non eros enim. Morbi tristique erat vitae. ',
				42,
			],
			'French word count'  => [
				'fr_FR',
				'Agissez à nos côtés',
				4,
			],
			'Russian word count' => [
				'ru_RU',
				'это независимая международная природоохранная некоммерческая организация.
				Мы работаем только на частные пожертвования граждан и не принимаем финансирования от коммерческих, государственных и политических организаций.',
				23,
			],
			'Thai word count'    => [
				'th_TH',
				'ยุติยุคเชื้อเพลิงฟอสซิล เปลี่ยนผ่านพลังงานสู่การใช้พลังงานหมุนเวียนที่สะอาด เพื่ออากาศสะอาดไร้มลพิษและชะลอวิกฤตสภาพภูมิอากาศ',
				26,
			],
			'Turkish word count' => [
				'tr_TR',
				'Güzel bir gelecek hayalin var. Narin dünyayı korumak istiyorsun. Yalnız değilsin. Biz yanındayız. Milyonlarca insan yanında. Harekete geçelim çünkü beraberken her zamankinden güçlüyüz.',
				23,
			],
			'Chinese word count' => [
				'zh',
				'2021年12月2日，北京——国际环保机构绿色和平今日发布《逐绿而行：中、日、韩科技企业气候行动研究报告》（下称报告）对东亚地区30家头部科技企业进行评级。报告发现，上榜科技企业整体仍然缺乏有力的气候行动。其中，全球知名消费电子产品制造商三星电子、小米尚未作出全球范围内的100%可再生能源及碳中和承诺，导致排名落后。对此，绿色和平呼吁东亚科技行业应在碳中和时代下充分发挥创新领导力，及早制定在2030年前实现包含供应链在内的100%可再生能源的目标，切实履行气候承诺',
				122,
			],
		];
	}

	/**
	 * @dataProvider wordCountProvider
	 *
	 * @param string $locale   The locale.
	 * @param string $text     The text.
	 * @param int    $expected The expected word count.
	 */
	public function testWordCount( $locale, $text, $expected ) {
		$rt = new ReadingTimeCalculator( $locale );

		$this->assertEquals( $expected, $rt->get_word_count( $text ) );
	}

	/**
	 * Format: [
	 *  (string) locale,
	 *  (int) reading speed in words per minute,
	 *  (string) content,
	 *  (int) expected reading time in seconds
	 * ]
	 *
	 * @return array
	 */
	public function contentProvider() {
		return [
			'Thai reading speed'       => [
				'th_TH',
				13,
				'ยุติยุคเชื้อเพลิงฟอสซิล เปลี่ยนผ่านพลังงานสู่การใช้พลังงานหมุนเวียนที่สะอาด เพื่ออากาศสะอาดไร้มลพิษและชะลอวิกฤตสภาพภูมิอากาศ',
				120,
			],
			'Latin reading speed'      => [
				'en_GB',
				84,
				'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent laoreet quis lectus quis faucibus. Proin vitae ligula quis odio tincidunt ultricies. Suspendisse potenti. Donec sit amet justo ac justo mattis convallis eget eu ex. Etiam non eros enim. Morbi tristique erat vitae. ',
				30,
			],
			'3 images watching speed'  => [
				'en_GB',
				0,
				'<img src="image1" /><img src="image2" /><img src="image3" />',
				33,
			],
			'20 images watching speed' => [
				'en_GB',
				0,
				'<img src="image1" /><img src="image2" /><img src="image3" /><img src="image4" /><img src="image5" />
				<img src="image1" /><img src="image2" /><img src="image3" /><img src="image4" /><img src="image5" />
				<img src="image1" /><img src="image2" /><img src="image3" /><img src="image4" /><img src="image5" />
				<img src="image1" /><img src="image2" /><img src="image3" /><img src="image4" /><img src="image5" />',
				105,
			],
		];
	}

	/**
	 * @dataProvider contentProvider
	 *
	 * @param string $locale   The locale.
	 * @param int    $wpm      The wpm.
	 * @param string $content  The content.
	 * @param int    $expected The expected reading time.
	 */
	public function testReadingTime( $locale, $wpm, $content, $expected ) {
		$rt = new ReadingTimeCalculator( $locale, $wpm );

		$this->assertEquals( $expected, $rt->get_time( $content ) );
	}
}
