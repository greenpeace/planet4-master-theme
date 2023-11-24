async function publishPost({page, editor}) {
  await editor.publishPost();

  const urlString = await page
    .getByRole('region', {name: 'Editor publish'})
    .getByRole('textbox', {name: 'address'})
    .inputValue();

  return urlString;
}

async function publishPostAndVisit({page, editor}) {
  const urlString = await publishPost({page, editor});

  await page.goto(urlString);
}

async function updatePost({page}) {
  const updateButton = await page.locator('.edit-post-header__settings').getByRole('button', {name: 'Update'});
  await updateButton.click();

  return page.waitForSelector('.components-snackbar');
}

export {publishPost, publishPostAndVisit, updatePost};
