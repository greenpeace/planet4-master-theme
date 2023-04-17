
async function nonce(context) {
  const response = await context.request.get(
    './wp-admin/admin-ajax.php?action=rest-nonce',
    {failOnStatusCode: true,}
  );

  const adminNonce = await response.text();
  await response.dispose();
  return adminNonce;
}

export { nonce };
