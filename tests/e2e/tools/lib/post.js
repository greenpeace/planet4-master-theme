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

export {publishPost, publishPostAndVisit};
