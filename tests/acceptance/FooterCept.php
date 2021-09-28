<?php
$I = new AcceptanceTester($scenario);

function seeSocialMediaLinks($I) {
	$I->wantTo('check social media links');
	$I->seeLink('Facebook', 'https://www.facebook.com/greenpeace.international');
	$I->seeLink('Twitter', 'https://twitter.com/greenpeace');
	$I->seeLink('YouTube', 'https://www.youtube.com/greenpeace');
	$I->seeLink('Instagram', 'https://www.instagram.com/greenpeace/');
}

function seePostPagesLinks($I) {
	$I->seeLink('News', 'https://www-dev.greenpeace.org/defaultcontent/?s=&orderby=relevant&f%5Bctype%5D%5BPost%5D=3');
	$I->seeLink('Jobs', 'https://www.linkedin.com/jobs/greenpeace-jobs/');
	$I->seeLink('Press Center', 'http://www.planet4.test/press-center/');
	$I->seeLink('Sitemap', 'http://www.planet4.test/sitemap/');
	$I->seeLink('Privacy and Cookies', 'http://www.planet4.test/privacy-and-cookies/');
	$I->seeLink('Community Policy', 'http://www.planet4.test/community-policy/');
	$I->seeLink('Copyright', 'http://www.planet4.test/copyright/');
	$I->seeLink('Search the Archive', 'http://www.greenpeace.org/international/en/System-templates/Search-results/?adv=true');
}

$I->wantTo('check the default footer');
$I->amOnPage('/act');
$I->waitForElement('.site-footer');
$I->scrollTo('.site-footer');
$I->dontSeeElement('.site-footer--minimal');
$I->seeElement('.footer-social-media');
seeSocialMediaLinks($I);
$I->seeElement('.site-footer .footer-menu');
seePostPagesLinks($I);

// Create a new post with a default footer for campaigns
$campaignSlug = $I->generateRandomSlug();

$I->havePostInDatabase([
	'post_name'    => $campaignSlug,
	'post_status'  => 'publish',
	'post_content' => 'This is a new campaign with a regular footer',
	'post_type'    => 'campaign',
	'meta_input'  => [
		'theme' => 'plastic-new',
		'campaign_footer_theme' => 'default',
		'campaign_logo' => 'greenpeace',
		'campaign_nav_color' => '#ff513c',
		'campaign_nav_type' => 'planet4',
	],
]);

$I->wantTo('check the footer for campaigns');
$I->amOnPage('/campaign/' . $campaignSlug);
$I->waitForElement('.site-footer');
$I->scrollTo('.site-footer');
$I->seeElement('.site-footer .footer-menu');
seePostPagesLinks($I);
$I->seeElement('.footer-social-media');
seeSocialMediaLinks($I);


// Create a new post with and change the regular footer to a minimal version
$campaignSlug2 = $I->generateRandomSlug();

$I->havePostInDatabase([
	'post_name'    => $campaignSlug2,
	'post_status'  => 'publish',
	'post_content' => 'This is another campaign with a minimal version',
	'post_type'    => 'campaign',
	'meta_input'  => [
		'theme' => 'climate-new',
		'campaign_footer_theme' => 'white',
		'campaign_logo' => 'greenpeace',
		'campaign_nav_type' => 'minimal',
		'footer_menu_color' => '#007eff',
	],
]);

$I->wantTo('check the footer for campaigns minimal version');
$I->amOnPage('/campaign/' . $campaignSlug2);
$I->waitForElement('.site-footer--minimal');
$I->scrollTo('.site-footer--minimal');
$I->dontSeeElement('.site-footer--minimal .footer-menu');
$I->seeElement('.site-footer--minimal .footer-social-media');
