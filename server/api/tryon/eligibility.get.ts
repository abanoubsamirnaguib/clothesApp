import { getOrCreateTryOnUserKey } from '../../utils/tryon';

export default defineEventHandler(async event => {
  setHeader(event, 'Cache-Control', 'no-store');

  const { product_id } = getQuery(event) as { product_id?: string };
  if (!product_id) {
    throw createError({ statusCode: 400, statusMessage: 'product_id is required' });
  }

  const userKey = getOrCreateTryOnUserKey(event);

  return await laravelFetch('/api/tryon/eligibility', {
    query: { product_id, user_key: userKey },
  });
});

