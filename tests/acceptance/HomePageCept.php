<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check home page');

$I->amOnPage('/');

// Ensure the country dropdown opens
$I->scrollTo('.country-selector-toggle-container');
$I->waitForElementClickable('.country-selector-toggle', 10);
$I->click('.country-selector-toggle');
$I->seeElement('.countries-list');

