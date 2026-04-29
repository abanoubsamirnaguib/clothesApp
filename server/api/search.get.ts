export default cachedEventHandler(
  async event => {
    const { search = '' } = getQuery(event) as { search?: string };
    return await laravelFetch('/api/products', { query: { search } });
  },
  {
    maxAge: 60,
    swr: true,
    getKey: event => event.req.url!,
  }
);
