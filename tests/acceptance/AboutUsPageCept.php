<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check About Us page');

$I->amOnPage('/about-us-2');

$I->see('Who we are', 'h1');
