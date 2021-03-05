<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('check search works');

$I->amOnPage('/');

// Search for the term "climate"
$I->submitForm('#search_form', ['s' => 'climate']);

// We get some results ...
$I->see('for \'climate\'', 'h1');

// ... and at least one #Climate tag to show up
$I->see('#Climate', '.search-result-item-headline');
