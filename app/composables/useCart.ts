import { push } from 'notivue';

const CART_STORAGE_KEY = 'cart';

export const useCart = () => {
  const cart = useState<CartItem[]>('cart', () => []);
  const addToCartButtonStatus = ref<AddBtnStatus>('add');

  const persistCart = () => {
    if (!import.meta.client) return;
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart.value));
  };

  const setCart = (items: CartItem[]) => {
    cart.value = items;
    persistCart();
  };

  const findItem = (productId: number, size?: string) => {
    return cart.value.find(item => item.product_id === productId && item.size === size);
  };

  const handleAddToCart = async (product: Product, size?: string) => {
    addToCartButtonStatus.value = 'loading';

    try {
      const price = Number(product.sale_price || product.price || 0);
      const key = `${product.id}:${size || 'default'}`;
      const incoming: CartItem = {
        key,
        product_id: product.id,
        name: product.name,
        slug: product.slug,
        sku: product.sku,
        image: product.featured_image || product.image?.sourceUrl,
        size,
        color: product.color,
        style: product.style,
        unitPrice: price,
        regularPrice: product.regularPrice,
        salePrice: product.salePrice,
        stockQuantity: product.stock_quantity,
        quantity: 1,
      };
      const itemIndex = cart.value.findIndex(item => item.key === key);

      if (itemIndex >= 0) {
        const next = [...cart.value];
        const existing = next[itemIndex];
        if (!existing) return;
        if (existing.quantity >= existing.stockQuantity) {
          push.error('Insufficient stock');
          return;
        }
        next[itemIndex] = { ...existing, quantity: existing.quantity + 1 };
        setCart(next);
      } else {
        setCart([...cart.value, incoming]);
      }

      addToCartButtonStatus.value = 'added';
      setTimeout(() => {
        addToCartButtonStatus.value = 'add';
      }, 2000);
    } catch {
      addToCartButtonStatus.value = 'add';
      push.error('Insufficient stock');
    }
  };

  const changeQuantity = (key: string, quantity: number) => {
    const next =
      quantity <= 0
        ? cart.value.filter(item => item.key !== key)
        : cart.value.map(item => (item.key === key ? { ...item, quantity } : item));

    setCart(next);
  };

  const increment = (productId: number, size?: string) => {
    const item = findItem(productId, size);

    if (!item) {
      return;
    }

    if (typeof item.stockQuantity === 'number' && item.quantity >= item.stockQuantity) {
      push.error('Insufficient stock');
      return;
    }

    changeQuantity(item.key, item.quantity + 1);
  };

  const decrement = (productId: number, size?: string) => {
    const item = findItem(productId, size);
    if (!item) return;
    changeQuantity(item.key, item.quantity - 1);
  };

  onMounted(() => {
    if (!import.meta.client) return;
    const raw = localStorage.getItem(CART_STORAGE_KEY);
    if (!raw) return;

    try {
      const parsed = JSON.parse(raw) as CartItem[];
      setCart(Array.isArray(parsed) ? parsed : []);
    } catch {
      setCart([]);
    }
  });

  return {
    cart,
    addToCartButtonStatus,
    handleAddToCart,
    increment,
    decrement,
  };
};
