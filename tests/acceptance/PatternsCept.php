<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('test patterns');

$I->loginAsAdminCached();

$I->amOnPage('/wp-admin/post-new.php?post_type=page');

// Close Welcome modal
$I->waitForElement('//button[contains(@aria-label, "Close dialog")]');
$I->click('//button[contains(@aria-label, "Close dialog")]');

// We need to chek these class names since we've overriden its layout
// https://github.com/greenpeace/planet4-master-theme/blob/master/assets/src/scss/editorStyle.scss
$I->wantTo('check pattern modal styles');
$I->waitForElement('.block-editor-block-patterns-list__item');
$I->seeElement('.block-editor-block-patterns-list__item');
$I->seeElement('.block-editor-block-patterns-list__item-title');
