<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check header');

$I->amOnPage('/act');

$I->seeElement('.page-header');
$I->seeElement('.page-header-title');
