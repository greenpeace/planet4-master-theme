<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check footer');

$I->amOnPage('/');

$I->performOn('.site-footer', function ($I) {
	$I->seeElement('.footer-social-media');
	$I->seeElement('.footer-links');
	$I->seeElement('.footer-links-secondary');

	$I->seeLink('Facebook', 'https://www.facebook.com/greenpeace.international');
	$I->seeLink('Twitter', 'https://twitter.com/greenpeace');
	$I->seeLink('YouTube', 'https://www.youtube.com/greenpeace');
	$I->seeLink('Instagram', 'https://www.instagram.com/greenpeace/');

	$I->seeLink('NEWS', 'https://www-dev.greenpeace.org/defaultcontent/?s=&orderby=relevant&f%5Bctype%5D%5BPost%5D=3');
	$I->seeLink('JOBS', 'https://www.linkedin.com/jobs/greenpeace-jobs/');
	$I->seeLink('PRESS CENTER', 'http://www.planet4.test/press-center/');
	$I->seeLink('SITEMAP', 'http://www.planet4.test/sitemap/');

	$I->seeLink('PRIVACY AND COOKIES', 'http://www.planet4.test/privacy-and-cookies/');
	$I->seeLink('COMMUNITY POLICY', 'http://www.planet4.test/community-policy/');
	$I->seeLink('COPYRIGHT', 'http://www.planet4.test/copyright/');
	$I->seeLink('SEARCH THE ARCHIVE', 'http://www.greenpeace.org/international/en/System-templates/Search-results/?adv=true');
});

// the copyright notice appears on the page
$I->seeInSource($I->getP4Option('copyright_line1'));
$I->seeInSource($I->getP4Option('copyright_line2'));
