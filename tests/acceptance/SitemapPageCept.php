<?php

/**
 * @group frontend
 */

$I = new AcceptanceTester($scenario);
$I->wantTo('check Sitemap page');

$I->amOnPage('/sitemap');

// Ensure the sitemap template is being used
$I->seeElement('.page-sitemap');

// Ensure all types of sitemap entries are there

// Act
$I->see('Consectetur adipiscing elit', '.col-md-7 > a');

// Explore
$I->see('Energy', '.col-md-7 > a');
$I->see('#Coal', '.tag-item');
$I->see('Nature', '.col-md-7 > a');
$I->see('#Forests', '.tag-item');

// About
$I->see('Community Policy', '.col-md-5 > a');

// Articles
$I->see('Press Release', '.col-md-5 > a');
$I->see('Publication', '.col-md-5 > a');
$I->see('Story', '.col-md-5 > a');
