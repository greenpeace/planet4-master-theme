<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check footer');

$I->amOnPage('/');

function seeSharedElements($I) {
	$I->seeElement('.site-footer__social-media');
	$I->seeLink('Facebook', 'https://www.facebook.com/greenpeace.international');
	$I->seeLink('Twitter', 'https://twitter.com/greenpeace');
	$I->seeLink('YouTube', 'https://www.youtube.com/greenpeace');
	$I->seeLink('Instagram', 'https://www.instagram.com/greenpeace/');

	// Just for testing
	$aLinkText = $I->grabMultiple('a');
	$aLinks = $I->grabMultiple('a', 'href');
	print_r($aLinkText);
  print_r($aLinks);
	//
};

function seeCopyrightElements($I) {
	// the copyright notice appears on the page
	$I->seeInSource($I->getP4Option('copyright_line1'));
	$I->seeInSource($I->getP4Option('copyright_line2'));
};

$I->performOn('.site-footer', function ($I) {
	$I->seeElement('.site-footer__footer-links');
	seeSharedElements($I);

	// Only rendered through the default Footer
	$I->seeLink('News', 'https://www-dev.greenpeace.org/defaultcontent/?s=&orderby=relevant&f%5Bctype%5D%5BPost%5D=3');
	$I->seeLink('About Us', 'https://www.planet4.test/about-us-2/');
	$I->seeLink('Jobs', 'https://www.linkedin.com/jobs/greenpeace-jobs/');
	$I->seeLink('Press Center', 'https://www.planet4.test/press-center/');
	$I->seeLink('Sitemap', 'https://www.planet4.test/sitemap/');
	$I->seeLink('Privacy and Cookies', 'https://www.planet4.test/privacy-and-cookies/');
	$I->seeLink('Community Policy', 'https://www.planet4.test/community-policy/');
	$I->seeLink('Copyright', 'https://www.planet4.test/copyright/');
	$I->seeLink('Search the Archive', 'https://www.greenpeace.org/international/en/System-templates/Search-results/?adv=true');
});

seeCopyrightElements($I);

// Navigate to a Campaign page and check the Minimal Footer looks like
$I->amOnPage('/campaign/oceans-template-demo');

$I->performOn('.site-footer--minimal', function ($I) {
	# Ensure to don't render footer links
	$I->dontSeeElement('.site-footer__footer-links');
	seeSharedElements($I);
});

seeCopyrightElements($I);
