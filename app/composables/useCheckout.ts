const defaultUserDetails = (): CheckoutUserDetails => ({
  name: '',
  phone: '',
  address: '',
  notes: '',
  discountCode: '',
});

export const useCheckout = () => {
  const { cart } = useCart();
  const order = useState<CheckoutOrder | null>('order', () => null);
  const userDetails = useState<CheckoutUserDetails>('userDetails', defaultUserDetails);
  const checkoutStatus = ref<CheckoutStatus>('order');

  const clearCart = () => {
    cart.value = [];
    if (!import.meta.client) return;
    localStorage.setItem('cart', JSON.stringify(cart.value));
  };

  const handleCheckout = async () => {
    if (checkoutStatus.value !== 'order') return;
    checkoutStatus.value = 'processing';

    try {
      const response = await $fetch<CheckoutResponse>('/api/orders', {
        method: 'POST',
        body: {
          customer: {
            name: userDetails.value.name,
            phone: userDetails.value.phone,
            address: userDetails.value.address,
            notes: userDetails.value.notes,
          },
          discount_code: userDetails.value.discountCode || null,
          items: cart.value.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            selected_size: item.size,
          })),
        },
      });

      clearCart();
      order.value = {
        order_number: response.order_number,
        total: Number(response.order?.total || 0),
        whatsapp_url: response.whatsapp_url,
      };

      if (import.meta.client) {
        window.open(response.whatsapp_url, '_blank', 'noopener,noreferrer');
      }

      await navigateTo({ path: '/order-confirmation', query: { order: response.order_number } });
    } finally {
      checkoutStatus.value = 'order';
    }
  };

  return {
    order,
    userDetails,
    checkoutStatus,
    handleCheckout,
  };
};
