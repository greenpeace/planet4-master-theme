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
	$I->scrollTo('.country-selector-toggle-container');
	$I->waitForElementClickable('.country-selector-toggle', 10);
	$I->click('.country-selector-toggle');
	$I->seeElement('.countries-list');
}

