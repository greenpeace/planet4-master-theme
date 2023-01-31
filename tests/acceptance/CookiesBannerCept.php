<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('check cookie banner renders');

$cookieText = $I->getP4Option('cookies_field');

$I->amOnPage('/');

// We have the banner!
$I->see($cookieText, '#set-cookie');

// accept the cookies
$I->click('Accept all cookies');

// and it's gone
$I->waitForElementNotVisible('#set-cookie', 5);
