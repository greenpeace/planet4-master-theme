<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('check Explore page');

$I->amOnPage('/explore');

$I->see('Justice for people and planet', 'h1');

$I->scrollTo('.split-two-column.block-wide');

$I->see('Energy', 'a');
$I->see('#renewables', '.split-two-column-item-tag');
