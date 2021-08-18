<?php
/**
 * Unit tests for shortcake shortocode blocks to gutenberg blocks conversion.
 *
 * @package P4BKS
 */

use P4GBKS\Command\Shortcode_To_Gutenberg;

require_once __DIR__ . '/../p4-unittestcase.php';

/**
 * Class P4_ShortcodeConverterTest
 *
 * @package Planet4_Plugin_Blocks
 */
class P4_ShortcodeConverterTest extends P4_UnitTestCase {

	/**
	 * This method sets up the test.
	 */
	public function setUp() {
		parent::setUp();
		$this->converter = new Shortcode_To_Gutenberg();
		$this->converter->init();
	}

	/**
	 * Test that the block retrieves all the available Posts with 'press-release' as p4 page type.
	 *
	 * @param string $shortcode  Shortcake shortcode.
	 * @param string $expected   Expected converted to gutenberg block.
	 *
	 * @dataProvider timeline_shortcodes_provider
	 * @dataProvider take_action_boxout_shortcodes_provider
	 */
	public function test_shortcodes_conversion( $shortcode, $expected ) {

		$converted = do_shortcode( $shortcode );

		$blocks = parse_blocks( $converted );
		$this->assertEquals( $expected, $converted );
		$this->assertCount( 1, $blocks );
	}

	// phpcs:disable
	/**
	 * Planet4 blocks shortocodes provider.
	 *
	 * @return array
	 */
	public function enblock_shortcodes_provider(): array {
		return [
			// 1-5
			[
				'[shortcake_enblock en_page_id="27713" enform_goal="Petition Signup" en_form_style="full-width" description="enform block description" button_text="Call to Action" en_form_id="22537" /]',

				'<!-- wp:planet4-blocks/enform {"en_page_id":27713,"enform_goal":"Petition Signup","en_form_style":"full-width","description":"enform block description","button_text":"Call to Action","en_form_id":22537} /-->',
			],

			[

				'[shortcake_enblock en_page_id="20001" enform_goal="Petition Signup" en_form_style="full-width" description="enform block description" content_title_size="h1" button_text="Call to Action" en_form_id="22715" /]',

				'<!-- wp:planet4-blocks/enform {"en_page_id":20001,"enform_goal":"Petition Signup","en_form_style":"full-width","description":"enform block description","button_text":"Call to Action","en_form_id":22715} /-->',
			],

			[
				'[shortcake_enblock en_page_id="25032" enform_goal="Petition Signup" en_form_style="side-style" title="this the form title" description="this is form description" content_title="This is a content title" content_title_size="h1" content_description="This is the content description: " button_text="Join us" en_form_id="681" /]',

				'<!-- wp:planet4-blocks/enform {"en_page_id":25032,"enform_goal":"Petition Signup","en_form_style":"side-style","title":"this the form title","description":"this is form description","content_title":"This is a content title","content_description":"This is the content description: ","button_text":"Join us","en_form_id":681} /-->',
			],

			[
				'[shortcake_enblock en_page_id="29490" enform_goal="Other" en_form_style="full-width" title="Title" description="Description" button_text="Sign up" thankyou_title="Thank you" thankyou_subtitle="Thanx again" en_form_id="8224" /]',

				'<!-- wp:planet4-blocks/enform {"en_page_id":29490,"enform_goal":"Other","en_form_style":"full-width","title":"Title","description":"Description","button_text":"Sign up","thankyou_title":"Thank you","thankyou_subtitle":"Thanx again","en_form_id":8224} /-->',
			],

			[
				'[shortcake_enblock en_page_id="44940" enform_goal="Petition Signup" en_form_style="side-style" background="2797" title="阻止破壞北極" description="今天立即加入全球行動！" content_title="阻止破壞北極 今天立即加入全球行動！	" content_description="全賴有您，守護北極的力量日益強大。我們團結一起，讓守護北極的呼聲，揚得更遠！" button_text="立即聯署" thankyou_title="多謝您的聯署" en_form_id="2783" /]',

				'<!-- wp:planet4-blocks/enform {"en_page_id":44940,"enform_goal":"Petition Signup","en_form_style":"side-style","background":2797,"title":"\u963b\u6b62\u7834\u58de\u5317\u6975","description":"\u4eca\u5929\u7acb\u5373\u52a0\u5165\u5168\u7403\u884c\u52d5\uff01","content_title":"\u963b\u6b62\u7834\u58de\u5317\u6975 \u4eca\u5929\u7acb\u5373\u52a0\u5165\u5168\u7403\u884c\u52d5\uff01\t","content_description":"\u5168\u8cf4\u6709\u60a8\uff0c\u5b88\u8b77\u5317\u6975\u7684\u529b\u91cf\u65e5\u76ca\u5f37\u5927\u3002\u6211\u5011\u5718\u7d50\u4e00\u8d77\uff0c\u8b93\u5b88\u8b77\u5317\u6975\u7684\u547c\u8072\uff0c\u63da\u5f97\u66f4\u9060\uff01","button_text":"\u7acb\u5373\u806f\u7f72","thankyou_title":"\u591a\u8b1d\u60a8\u7684\u806f\u7f72","en_form_id":2783} /-->',
			],
		];
	}

	/**
	 * Planet4 blocks shortocodes provider.
	 *
	 * @return array
	 */
	public function take_action_boxout_shortcodes_provider(): array {
		return [
			// 1-5

			'take action boxout with title and language' =>
				[
					'[shortcake_take_action_boxout custom_title="Stand against plastic pollution" custom_excerpt="Tell the world’s biggest plastic polluters to invest in reusable ways to deliver their products." custom_link="https://act.greenpeace.org/page/49013/petition/1" custom_link_text="Take Action" custom_link_new_tab="false" tag_ids="67" background_image="21162" /]',
					'<!-- wp:planet4-blocks/take-action-boxout {"custom_title":"Stand against plastic pollution","custom_excerpt":"Tell the world\u2019s biggest plastic polluters to invest in reusable ways to deliver their products.","custom_link":"https://act.greenpeace.org/page/49013/petition/1","custom_link_text":"Take Action","custom_link_new_tab":false,"tag_ids":[67],"background_image":21162} /-->',
				],


			'take action boxout with title and language' =>
				[
					'[shortcake_take_action_boxout take_action_page="32" /]',
					'<!-- wp:planet4-blocks/take-action-boxout {"take_action_page":32} /-->',
				],
			'take action boxout with title and language' =>
				[
					'[shortcake_take_action_boxout take_action_page="32" custom_link_new_tab="false" /]',
					'<!-- wp:planet4-blocks/take-action-boxout {"take_action_page":32} /-->',
				],
			'take action boxout with title and language' =>
				[
					'[shortcake_take_action_boxout custom_title="Ciao mamma" custom_excerpt="Ciao mamma I am a ballon and I floart " custom_link="https://jira.greenpeace.org/browse/PLANET-4024" custom_link_text="FLOA T ME! " custom_link_new_tab="true" tag_ids="89,67" background_image="24159" /]',
					'<!-- wp:planet4-blocks/take-action-boxout {"custom_title":"Ciao mamma","custom_excerpt":"Ciao mamma I am a ballon and I floart ","custom_link":"https://jira.greenpeace.org/browse/PLANET-4024","custom_link_text":"FLOA T ME! ","custom_link_new_tab":true,"tag_ids":[89,67],"background_image":24159} /-->',
				],
			'take action boxout with title and language' =>
				[
					'[shortcake_take_action_boxout custom_title="Cusrtom stuf " custom_link="https://www.google.com" custom_link_text="I AM A CUSTOM LINK" custom_link_new_tab="true" background_image="24020" /]',
					'<!-- wp:planet4-blocks/take-action-boxout {"custom_title":"Cusrtom stuf ","custom_link":"https://www.google.com","custom_link_text":"I AM A CUSTOM LINK","custom_link_new_tab":true,"background_image":24020} /-->',
				],
		];
	}

	/**
	 * Planet4 blocks shortocodes provider.
	 *
	 * @return array
	 */
	public function timeline_shortcodes_provider(): array {
		return [
			// 1-5

			'timeline with title and language' =>
				[
					'[shortcake_timeline timeline_title="Timeline block!" language="en" timenav_position="bottom" start_at_end="true" /]',

					'<!-- wp:planet4-blocks/timeline {"timeline_title":"Timeline block!","language":"en","timenav_position":"bottom","start_at_end":true} /-->',
				],

			'timeline with url' =>
				[
					'[shortcake_timeline timeline_title="Timeline Title" description="Pea horseradish azuki bean lettuce avocado asparagus okra. Kohlrabi radish okra azuki bean corn fava bean mustard tigernut jícama green bean celtuce collard greens avocado quandong" google_sheets_url="https://docs.google.com/spreadsheets/d/1tYlLd_Fx0T_7ZEaf2y9dLfRnr5HzEOW_s0wELp5-j4s/edit#gid=0" language="en" timenav_position="bottom" start_at_end="false" /]',

					'<!-- wp:planet4-blocks/timeline {"timeline_title":"Timeline Title","description":"Pea horseradish azuki bean lettuce avocado asparagus okra. Kohlrabi radish okra azuki bean corn fava bean mustard tigernut j\u00edcama green bean celtuce collard greens avocado quandong","google_sheets_url":"https://docs.google.com/spreadsheets/d/1tYlLd_Fx0T_7ZEaf2y9dLfRnr5HzEOW_s0wELp5-j4s/edit#gid=0","language":"en","timenav_position":"bottom","start_at_end":false} /-->',
				],

			'timeline with url position' =>
				[
					'[shortcake_timeline google_sheets_url="https://docs.google.com/spreadsheets/d/1tYlLd_Fx0T_7ZEaf2y9dLfRnr5HzEOW_s0wELp5-j4s/edit#gid=0"  timeline_title="Timeline TItle" description="Pea horseradish azuki bean lettuce avocado asparagus okra. Kohlrabi radish okra azuki bean corn fava bean mustard tigernut jícama green bean celtuce collard greens avocado quandong" language="en" timenav_position="bottom" /]',

					'<!-- wp:planet4-blocks/timeline {"google_sheets_url":"https://docs.google.com/spreadsheets/d/1tYlLd_Fx0T_7ZEaf2y9dLfRnr5HzEOW_s0wELp5-j4s/edit#gid=0","timeline_title":"Timeline TItle","description":"Pea horseradish azuki bean lettuce avocado asparagus okra. Kohlrabi radish okra azuki bean corn fava bean mustard tigernut j\u00edcama green bean celtuce collard greens avocado quandong","language":"en","timenav_position":"bottom"} /-->',
				],

			'timeline block test, default' =>
				[
					'[shortcake_timeline timeline_title="Timeline Title" description="Pea horseradish azuki bean lettuce avocado asparagus okra. Kohlrabi radish okra azuki bean corn fava bean mustard tigernut jícama green bean celtuce collard greens avocado quandong" google_sheets_url="https://docs.google.com/spreadsheets/d/1tYlLd_Fx0T_7ZEaf2y9dLfRnr5HzEOW_s0wELp5-j4s/edit#gid=0" language="en" timenav_position="bottom" start_at_end="false" /]',

					'<!-- wp:planet4-blocks/timeline {"timeline_title":"Timeline Title","description":"Pea horseradish azuki bean lettuce avocado asparagus okra. Kohlrabi radish okra azuki bean corn fava bean mustard tigernut j\u00edcama green bean celtuce collard greens avocado quandong","google_sheets_url":"https://docs.google.com/spreadsheets/d/1tYlLd_Fx0T_7ZEaf2y9dLfRnr5HzEOW_s0wELp5-j4s/edit#gid=0","language":"en","timenav_position":"bottom","start_at_end":false} /-->',
				],

			'timeline block test, start at end' =>
				[
					'[shortcake_timeline timeline_title="Timeline Title" description="Pea horseradish azuki bean lettuce avocado asparagus okra. Kohlrabi radish okra azuki bean corn fava bean mustard tigernut jícama green bean celtuce collard greens avocado quandong" google_sheets_url="https://docs.google.com/spreadsheets/d/1tYlLd_Fx0T_7ZEaf2y9dLfRnr5HzEOW_s0wELp5-j4s/edit#gid=0" language="en" timenav_position="bottom" start_at_end="true" /]',

					'<!-- wp:planet4-blocks/timeline {"timeline_title":"Timeline Title","description":"Pea horseradish azuki bean lettuce avocado asparagus okra. Kohlrabi radish okra azuki bean corn fava bean mustard tigernut j\u00edcama green bean celtuce collard greens avocado quandong","google_sheets_url":"https://docs.google.com/spreadsheets/d/1tYlLd_Fx0T_7ZEaf2y9dLfRnr5HzEOW_s0wELp5-j4s/edit#gid=0","language":"en","timenav_position":"bottom","start_at_end":true} /-->',
				],

			'timeline block test, start at end, navigation at top' =>
				[
					'[shortcake_timeline timeline_title="Timeline Title" description="Pea horseradish azuki bean lettuce avocado asparagus okra. Kohlrabi radish okra azuki bean corn fava bean mustard tigernut jícama green bean celtuce collard greens avocado quandong" google_sheets_url="https://docs.google.com/spreadsheets/d/1tYlLd_Fx0T_7ZEaf2y9dLfRnr5HzEOW_s0wELp5-j4s/edit#gid=0" language="en" timenav_position="top" start_at_end="true" /]',

					'<!-- wp:planet4-blocks/timeline {"timeline_title":"Timeline Title","description":"Pea horseradish azuki bean lettuce avocado asparagus okra. Kohlrabi radish okra azuki bean corn fava bean mustard tigernut j\u00edcama green bean celtuce collard greens avocado quandong","google_sheets_url":"https://docs.google.com/spreadsheets/d/1tYlLd_Fx0T_7ZEaf2y9dLfRnr5HzEOW_s0wELp5-j4s/edit#gid=0","language":"en","timenav_position":"top","start_at_end":true} /-->',
				],
		];
	}
	// phpcs:enable
}




