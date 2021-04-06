<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check there are no PHP warnings');

$I->amOnPage('/');

// check we have no php warnings on the page
$I->dontSeeInSource('<b>Warning</b>:');
