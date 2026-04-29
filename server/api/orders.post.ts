export default defineEventHandler(async event => {
  const body = await readBody(event);

  return await laravelFetch('/api/orders', {
    method: 'POST',
    body,
  });
});
