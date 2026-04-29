export default cachedEventHandler(
  async event => {
    const query = getQuery(event);

    return await laravelFetch('/api/products', { query });
  },
  {
    maxAge: 60,
    swr: true,
    getKey: event => event.req.url!,
  }
);
