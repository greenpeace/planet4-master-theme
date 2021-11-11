<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check act page');

$I->amOnPage('/act');

$I->seeElement('.covers-block');
$I->seeElement('.cover-card');

$I->see('#Consumption', '.cover-card-tag');
$I->see('#renewables', '.cover-card-tag');
$I->see('#Climate', '.cover-card-tag');
