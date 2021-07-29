
<?php
use \Codeception\Util\Locator;

$I = new AcceptanceTester($scenario);

$I->wantTo('create and check check split two column block');

$slug = $I->generateRandomSlug();

$I->havePageInDatabase([
	'post_name'    => $slug,
	'post_status'  => 'publish',
	'post_content' => $I->generateGutenberg('wp:planet4-blocks/split-two-columns', [
		'select_issue'    => 60,
		'title'           => 'Issue',
		'issue_link_text' => 'Read more',
		'select_tag'      => 6,
		'tag_description' => 'Campaign description'
	])
]);

// Navigate to the newly created page
$I->amOnPage('/' . $slug);

// Check the Split Two Column block
$I->scrollTo('.split-two-column');
$I->see('Issue', 'h2.split-two-column-item-title');
$I->see('Read more', 'a.split-two-column-item-link');
$I->see('#Climate', 'a.split-two-column-item-tag');
$I->see('Campaign description', 'div.item--right p.split-two-column-item-subtitle');
$I->see('Get Involved', 'a.split-two-column-item-button');
