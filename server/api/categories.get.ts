export default eventHandler(async (event) => {
  // Categories are edited frequently in admin; avoid serving stale images.
  setHeader(event, 'Cache-Control', 'no-store');
  return await laravelFetch('/api/categories');
});
