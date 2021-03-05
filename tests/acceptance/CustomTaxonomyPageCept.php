<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check Custom Taxonomy page');

$I->amOnPage('/story');

$I->see('Story', 'h1');

$I->click('Lilian Reyes');

$I->amOnPage('/author/lreyes');

$I->see('Lilian Reyes', 'h1');

// Create a new post and override the author
$slug = $I->generateRandomSlug();

$postID = $I->havePostInDatabase([
	'post_name'    => $slug,
	'post_status'  => 'publish',
	'post_content' => 'This is a test post',
	'post_type'    => 'post',
	'tax_input'   => [
		'p4-page-type' => [ 'story' ],
		'category'     => [ 'people' ]
	],
	'meta_input'  => [
		'p4_author_override' => 'FooBarAuthor'
	]
]);

// Navigate to the newly created post
$I->amOnPage('/story/' . $postID);

$I->see('FooBarAuthor', 'address');
