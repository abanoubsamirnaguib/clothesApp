<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = Admin::query()->where('email', $credentials['email'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
        }

        $request->session()->put('admin_id', $admin->id);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('admin_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        return view('admin.dashboard', [
            'ordersToday' => Order::query()->whereDate('created_at', today())->count(),
            'pendingOrders' => Order::query()->where('status', 'pending')->count(),
            'totalProducts' => Product::query()->count(),
            'revenue' => Order::query()->where('status', '!=', 'cancelled')->sum('total'),
            'latestOrders' => Order::query()->latest()->limit(8)->get(),
        ]);
    }

    public function products()
    {
        return view('admin.products.index', [
            'products' => Product::query()->with('category')->latest()->paginate(20),
        ]);
    }

    public function createProduct()
    {
        return view('admin.products.form', [
            'product' => new Product(['status' => 'active', 'stock_quantity' => 100]),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function storeProduct(Request $request)
    {
        Product::create($this->productData($request));

        return redirect()->route('admin.products')->with('success', 'Product created.');
    }

    public function editProduct(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $product->update($this->productData($request, $product));

        return redirect()->route('admin.products')->with('success', 'Product updated.');
    }

    public function deleteProduct(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Product deleted.');
    }

    public function categories()
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->withCount('products')->orderBy('name')->get(),
            'categoryToEdit' => null,
        ]);
    }

    public function editCategory(Category $category)
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->withCount('products')->orderBy('name')->get(),
            'categoryToEdit' => $category,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:2048'],
        ]);
        $data['slug'] = Str::slug($data['name']);
        if (Category::query()->where('slug', $data['slug'])->exists()) {
            return back()
                ->withErrors(['name' => 'A category with this name already exists.'])
                ->withInput();
        }

        Category::create($data);

        return back()->with('success', 'Category saved.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:2048'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        if (Category::query()
            ->where('slug', $data['slug'])
            ->whereKeyNot($category->getKey())
            ->exists()
        ) {
            return back()
                ->withErrors(['name' => 'A category with this name already exists.'])
                ->withInput();
        }

        $category->update($data);

        return redirect()->route('admin.categories')->with('success', 'Category updated.');
    }

    public function deleteCategory(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    public function orders(Request $request)
    {
        $orders = Order::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function showOrder(Order $order)
    {
        return view('admin.orders.show', [
            'order' => $order->load('items'),
            'statuses' => Order::STATUSES,
        ]);
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $order->update($data);

        return back()->with('success', 'Order updated.');
    }

    public function discounts()
    {
        return view('admin.discounts.index', [
            'discounts' => Discount::query()->latest()->get(),
        ]);
    }

    public function storeDiscount(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable'],
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['min_order_amount'] = $data['min_order_amount'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        Discount::updateOrCreate(['code' => $data['code']], $data);

        return back()->with('success', 'Discount saved.');
    }

    public function deleteDiscount(Discount $discount)
    {
        $discount->delete();

        return back()->with('success', 'Discount deleted.');
    }

    public function settings()
    {
        return view('admin.settings.index', [
            'settings' => Setting::query()->pluck('value', 'key'),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'whatsapp_number' => ['required', 'string', 'max:255'],
            'store_name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Settings updated.');
    }

    private function productData(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($product)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'color' => ['nullable', 'string', 'max:255'],
            'style' => ['nullable', 'string', 'max:255'],
            'sizes' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:2048'],
            'featured_image_file' => ['nullable', 'image', 'max:4096'],
            'images' => ['nullable', 'string'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:4096'],
        ]);

        $data['slug'] = Str::slug($data['name'].'-'.$data['sku']);
        $data['sizes'] = $this->linesOrCsv($data['sizes'] ?? '');
        $images = $this->linesOrCsv($data['images'] ?? '');

        if ($request->hasFile('featured_image_file')) {
            $data['featured_image'] = Storage::url($request->file('featured_image_file')->store('products', 'public'));
        }

        foreach ($request->file('gallery_images', []) as $image) {
            $images[] = Storage::url($image->store('products', 'public'));
        }

        $data['images'] = $images;
        if (empty($data['featured_image']) && $images) {
            $data['featured_image'] = $images[0];
        }

        unset($data['featured_image_file'], $data['gallery_images']);
        $data['sale_price'] = $data['sale_price'] ?: null;

        return $data;
    }

    private function linesOrCsv(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $value))));
    }
}
