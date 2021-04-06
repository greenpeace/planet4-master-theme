<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check home page');

$I->amOnPage('/');

// Ensure the country dropdown opens
$I->click('.country-dropdown-toggle');

$I->seeElement('.country-list.open');

