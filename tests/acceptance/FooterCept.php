<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check footer');

$I->amOnPage('/');

$I->performOn('.site-footer', function ($I) {
	$I->seeElement('.footer-social-media');
	$I->seeElement('.footer-links');
	$I->seeElement('.footer-links-secondary');
});

// the copyright notice appears on the page
$I->seeInSource($I->getP4Option('copyright_line1'));
$I->seeInSource($I->getP4Option('copyright_line2'));
