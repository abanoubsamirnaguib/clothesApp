export default cachedEventHandler(
  async event => {
    const { slug, sku } = getQuery(event) as { slug?: string; sku?: string };
    return await laravelFetch(`/api/products/${slug || sku}`);
  },
  {
    maxAge: 60 * 5,
    swr: true,
    getKey: event => event.req.url!,
  }
);
