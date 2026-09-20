<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Category, Order, OrderItem, Payment, Product, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Payment\QPayService;

class ShopController extends Controller
{
    public function categories() { return ['data' => Category::where('status', true)->withCount('products')->get()]; }
    public function products(Request $request) { $query = Product::with('category')->where('status', true); if ($request->filled('search')) $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%')->orWhere('sku', 'like', '%'.$request->string('search').'%')); if ($request->filled('category')) $query->whereHas('category', fn ($q) => $q->where('slug', $request->category)); match ($request->input('sort')) { 'price_asc' => $query->orderBy('sale_price')->orderBy('price'), 'price_desc' => $query->orderByDesc('sale_price')->orderByDesc('price'), default => $query->latest(), }; return ['data' => $query->paginate(24)]; }
    public function product(string $slug) { $product = Product::with('category')->where('slug', $slug)->where('status', true)->firstOrFail(); return ['data' => $product]; }
    public function adminCategories(Request $request) { abort_unless($request->user()?->isAdmin(), 403); return ['data' => Category::withCount('products')->latest()->get()]; }
    public function saveCategory(Request $request, ?Category $category = null) { abort_unless($request->user()?->isAdmin(), 403); $data = $request->validate(['name' => 'required|string|max:120', 'image' => 'nullable|string|max:500', 'status' => 'boolean']); $data['slug'] = Str::slug($data['name']); if (!$category || $category->slug !== $data['slug']) $data['slug'] .= '-'.Str::lower(Str::random(4)); return ['data' => $category ? tap($category)->update($data) : Category::create($data)]; }
    public function deleteCategory(Request $request, Category $category) { abort_unless($request->user()?->isAdmin(), 403); abort_if($category->products()->exists(), 422, 'Бүтээгдэхүүнтэй ангиллыг устгах боломжгүй.'); $category->delete(); return ['message' => 'Ангилал устгагдлаа.']; }
    public function order(Request $request)
    {
        $data = $request->validate(['customer_name' => 'required|string|max:120', 'phone' => 'required|string|max:30', 'email' => 'nullable|email', 'district' => 'required|string|max:80', 'khoroo' => 'required|string|max:80', 'address' => 'required|string|max:500', 'note' => 'nullable|string|max:1000', 'payment_method' => 'required|in:qpay,bank_transfer,cash', 'items' => 'required|array|min:1', 'items.*.product_id' => 'required|integer|exists:products,id', 'items.*.quantity' => 'required|integer|min:1|max:100']);
        $order = DB::transaction(function () use ($data, $request) {
            $subtotal = 0; $lines = [];
            foreach ($data['items'] as $item) { $product = Product::lockForUpdate()->where('status', true)->findOrFail($item['product_id']); if ($product->stock < $item['quantity']) abort(422, 'Үлдэгдэл хүрэлцэхгүй байна.'); $unit = (float) ($product->sale_price ?? $product->price); $line = $unit * $item['quantity']; $subtotal += $line; $lines[] = [$product, $item['quantity'], $unit, $line]; }
            $delivery = (float) (DB::table('settings')->where('key', 'delivery_fee')->value('value') ?? 5000); $order = Order::create([...$data, 'user_id' => $request->user()?->id, 'order_number' => 'ORD-'.now()->format('Ymd').'-'.str_pad((string) ((Order::max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT), 'subtotal' => $subtotal, 'delivery_fee' => $delivery, 'total' => $subtotal + $delivery, 'status' => 'NEW', 'payment_status' => 'pending']);
            foreach ($lines as [$product, $quantity, $unit, $line]) { $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $quantity, 'unit_price' => $unit, 'subtotal' => $line]); $product->decrement('stock', $quantity); }
            $order->payments()->create(['method' => $data['payment_method'], 'amount' => $order->total, 'status' => 'pending']); return $order->load('items', 'payments');
        });
        return response()->json(['data' => $order, 'message' => 'Захиалга амжилттай үүслээ.'], 201);
    }
    public function showOrder(string $number) { return ['data' => Order::with('items', 'payments')->where('order_number', $number)->firstOrFail()]; }
    public function adminProducts(Request $request) { abort_unless($request->user()?->isAdmin(), 403, 'Эрх хүрэхгүй байна.'); return ['data' => Product::with('category')->latest()->paginate(30)]; }
    public function saveProduct(Request $request, ?Product $product = null) { abort_unless($request->user()?->isAdmin(), 403); $data = $request->validate(['category_id' => 'required|exists:categories,id', 'name' => 'required|string|max:200', 'sku' => 'required|string|max:80', 'description' => 'nullable|string', 'price' => 'required|numeric|min:0', 'sale_price' => 'nullable|numeric|min:0|lte:price', 'stock' => 'required|integer|min:0', 'status' => 'boolean']); $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5)); return ['data' => $product ? tap($product)->update($data) : Product::create($data)]; }
    public function deleteProduct(Request $request, Product $product) { abort_unless($request->user()?->isAdmin(), 403); $product->delete(); return ['message' => 'Бүтээгдэхүүн устгагдлаа.']; }
    public function adminOrders(Request $request) { abort_unless($request->user()?->isAdmin(), 403); return ['data' => Order::with('payments')->latest()->paginate(30)]; }
    public function updateOrder(Request $request, Order $order) { abort_unless($request->user()?->isAdmin(), 403); $data = $request->validate(['status' => 'required|in:NEW,CONFIRMED,PROCESSING,SHIPPED,DELIVERED,CANCELLED', 'payment_status' => 'nullable|in:pending,paid,failed,cancelled']); $order->update($data); return ['data' => $order->fresh()]; }
    public function confirmPayment(Request $request, Payment $payment) { abort_unless($request->user()?->isAdmin(), 403); $payment->update(['status' => 'paid', 'paid_at' => now()]); $payment->order->update(['payment_status' => 'paid']); return ['data' => $payment->fresh()]; }
    public function createQpay(Request $request, QPayService $qpay) { $data = $request->validate(['order_number' => 'required|exists:orders,order_number']); $order = Order::where('order_number', $data['order_number'])->firstOrFail(); return ['data' => $qpay->createInvoice($order)]; }
    public function checkQpay(Request $request, QPayService $qpay) { $data = $request->validate(['invoice_id' => 'required|string']); return ['data' => $qpay->checkPayment($data['invoice_id'])]; }
    public function uploadProof(Request $request, Payment $payment) { $data = $request->validate(['proof_image' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120']); $payment->update(['proof_image' => $data['proof_image']->store('payment-proofs', 'public')]); return ['message' => 'Баримт амжилттай илгээгдлээ.', 'data' => $payment->fresh()]; }
}