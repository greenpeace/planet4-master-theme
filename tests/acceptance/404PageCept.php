<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('check the 404 page looks nice');

$html = $I->getP4Option('404_page_text');
$backgroundImage = $I->getP4Option('404_page_bg_image');

$I->amOnPage('/thispagereallywillnotexist');

$I->seeInSource($html);
$I->seeElement('input', ['placeholder' => 'Search']);

$I->seeElement('img', ['src' => $backgroundImage]);
