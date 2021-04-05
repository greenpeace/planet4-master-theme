<?php
use \Codeception\Util\Locator;

$I = new AcceptanceTester($scenario);

$I->wantTo('create and check take action boxout block');

$slug = $I->generateRandomSlug();

$I->havePageInDatabase([
  'post_name'    => $slug,
  'post_status'  => 'publish',
  'post_content' => $I->generateGutenberg('wp:planet4-blocks/take-action-boxout', [
    'take_action_page' => 28
  ])."Take action boxout block page"
]);

// Navigate to the newly created page
$I->amOnPage('/' . $slug);

// Check the Take Action Boxout block
$I->see('#Climate', '.cover-card .cover-card-tag');
