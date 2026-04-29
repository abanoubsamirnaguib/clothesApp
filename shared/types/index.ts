export type Money = string | number;

export interface Category {
  id: number;
  name: string;
  slug: string;
  image?: string | null;
  products_count?: number;
}

export interface ProductImage {
  sourceUrl: string;
}

export interface Product {
  id: number;
  databaseId: number;
  sku: string;
  slug: string;
  name: string;
  description?: string | null;
  price: number;
  sale_price?: number | null;
  regularPrice: Money;
  salePrice?: Money | null;
  stock_quantity: number;
  color?: string | null;
  style?: string | null;
  sizes: string[];
  featured_image?: string | null;
  images: string[];
  image?: ProductImage;
  galleryImages?: { nodes: ProductImage[] };
  allPaStyle?: { nodes: Array<{ name?: string | null }> };
  category?: Category;
}

export interface CartItem {
  key: string;
  product_id: number;
  name: string;
  slug: string;
  sku: string;
  image?: string | null;
  size?: string;
  color?: string | null;
  style?: string | null;
  unitPrice: number;
  regularPrice: Money;
  salePrice?: Money | null;
  stockQuantity: number;
  quantity: number;
}

export type AddBtnStatus = 'add' | 'loading' | 'added';

export type RemoveBtnStatus = 'remove' | 'loading';

export type WishlistItem = Product;

export interface CheckoutUserDetails {
  name: string;
  phone: string;
  address: string;
  notes: string;
  discountCode: string;
}

export interface CheckoutOrder {
  order_number: string;
  total: number;
  whatsapp_url: string;
}

export interface CheckoutResponse {
  order_number: string;
  whatsapp_url: string;
  order: CheckoutOrder;
}

export type CheckoutStatus = 'order' | 'processing';
