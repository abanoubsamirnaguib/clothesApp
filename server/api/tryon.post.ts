import { Client } from '@gradio/client';
import { getOrCreateTryOnUserKey } from '../utils/tryon';

type MultipartFile = {
  filename?: string;
  type?: string;
  data: Buffer | Uint8Array | ArrayBuffer;
  name?: string;
};

function toUint8Array(data: MultipartFile['data']): Uint8Array {
  if (data instanceof Uint8Array) return data;
  return new Uint8Array(data as ArrayBuffer);
}

async function urlToBlob(url: string): Promise<Blob> {
  const res = await fetch(url);
  if (!res.ok) throw createError({ statusCode: 400, statusMessage: 'Failed to fetch garment image' });
  const contentType = res.headers.get('content-type') || 'image/jpeg';
  const buf = await res.arrayBuffer();
  return new Blob([buf], { type: contentType });
}

export default defineEventHandler(async event => {
  setHeader(event, 'Cache-Control', 'no-store');

  const form = await readMultipartFormData(event);
  if (!form) throw createError({ statusCode: 400, statusMessage: 'multipart/form-data is required' });

  const productId = form.find(p => p.name === 'product_id')?.data?.toString?.();
  const person = form.find(p => p.name === 'person_image') as any as MultipartFile | undefined;

  if (!productId) throw createError({ statusCode: 400, statusMessage: 'product_id is required' });
  if (!person?.data) throw createError({ statusCode: 400, statusMessage: 'person_image is required' });

  const userKey = getOrCreateTryOnUserKey(event);

  // Best product image (MVP: featured_image or first image)
  const best = (await laravelFetch(`/api/tryon/products/${encodeURIComponent(productId)}/best-image`)) as any;
  const garmentUrl = best?.garment_image_url as string | undefined;
  if (!garmentUrl) throw createError({ statusCode: 400, statusMessage: 'Product has no images for try-on' });

  // Reserve attempt (enforces one-try-per-product-per-user)
  const reserve = (await laravelFetch('/api/tryon/reserve', {
    method: 'POST',
    body: {
      product_id: Number(productId),
      user_key: userKey,
      garment_image_url: garmentUrl,
    },
  })) as any;

  const attemptId = reserve?.attempt_id;
  if (!attemptId) throw createError({ statusCode: 500, statusMessage: 'Failed to reserve try-on attempt' });

  const cfg = useRuntimeConfig();
  const space = (cfg as any).huggingface?.space as string;
  const token = ((cfg as any).huggingface?.token as string) || undefined;

  try {
    const personBytes = toUint8Array(person.data);
    const base = personBytes.buffer as ArrayBuffer;
    const personArrayBuffer = base.slice(personBytes.byteOffset, personBytes.byteOffset + personBytes.byteLength);
    const personBlob = new Blob([personArrayBuffer], { type: person.type || 'image/jpeg' });
    const garmentBlob = await urlToBlob(garmentUrl);

    const app = await Client.connect(space, token ? ({ hf_token: token } as any) : undefined);

    const seed = Math.floor(Math.random() * 1_000_000_000);
    const result: any = await app.predict('/tryon', {
      dict: { background: personBlob, layers: [], composite: null },
      garm_img: garmentBlob,
      garment_des: '',
      is_checked: true,
      is_checked_crop: false,
      denoise_steps: 30,
      seed,
    });

    const output = result?.data?.[0] ?? result?.data ?? null;
    const resultUrl = typeof output === 'string' ? output : output?.url || null;

    await laravelFetch('/api/tryon/complete', {
      method: 'POST',
      body: {
        attempt_id: attemptId,
        user_key: userKey,
        status: 'completed',
        result_image_url: resultUrl,
      },
    });

    return { attempt_id: attemptId, result_image_url: resultUrl, garment_image_url: garmentUrl };
  } catch (err: any) {
    await laravelFetch('/api/tryon/complete', {
      method: 'POST',
      body: {
        attempt_id: attemptId,
        user_key: userKey,
        status: 'failed',
        error: String(err?.message || err),
      },
    });
    throw err;
  }
});

