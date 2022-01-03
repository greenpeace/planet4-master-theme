<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check home page');

$I->amOnPage('/');

// Ensure the country dropdown opens
try {
	$I->click('.country-dropdown-toggle');
	$I->seeElement('.country-list.open');
} catch (\Exception $e) {
	// Try new country selector
	$I->scrollTo('.country-selector-toggle');
	$I->click('.country-selector-toggle');
	$I->seeElement('.countries-list');
}

