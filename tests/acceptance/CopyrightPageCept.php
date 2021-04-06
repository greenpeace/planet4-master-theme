<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check Copyright page');

$I->amOnPage('/copyright');

$I->see('Copyright', 'h1');
