<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('store_id', auth()->user()->store_id)->orderBy('name')->get();

        $query = Product::where('store_id', auth()->user()->store_id)->where('active', true);

        if (request('category')) {
            $query->where('category_id', request('category'));
        }

        $products = $query->orderBy('name')->limit(50)->get();
        $customers = Customer::where('store_id', auth()->user()->store_id)->orderBy('name')->get();
        $cart = Session::get('pos.cart', []);

        return view('pos.index', compact('products', 'categories', 'customers', 'cart'));
    }

    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $quantity = max(1, (int) $request->quantity);

        $cart = Session::get('pos.cart', []);
        $cartQuantity = $cart[$product->id]['quantity'] ?? 0;

        if ($product->stock < 1) {
            return back()->with('error', "Produk {$product->name} sedang habis.");
        }

        if (($cartQuantity + $quantity) > $product->stock) {
            return back()->with('error', "Stok {$product->name} hanya tersedia {$product->stock}.");
        }

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sell_price,
                'quantity' => $quantity,
                'subtotal' => $product->sell_price * $quantity,
            ];
        }

        $cart[$product->id]['subtotal'] = $cart[$product->id]['price'] * $cart[$product->id]['quantity'];

        Session::put('pos.cart', $cart);

        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function updateCart(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->quantity);
        $cart = Session::get('pos.cart', []);

        if (! isset($cart[$product->id])) {
            return back()->with('error', 'Produk tidak ditemukan di keranjang.');
        }

        if ($quantity > $product->stock) {
            return back()->with('error', "Stok {$product->name} hanya tersedia {$product->stock}.");
        }

        $cart[$product->id]['quantity'] = $quantity;
        $cart[$product->id]['subtotal'] = $cart[$product->id]['price'] * $quantity;

        Session::put('pos.cart', $cart);

        return back()->with('success', 'Jumlah produk di keranjang diperbarui.');
    }

    public function removeCart(Product $product)
    {
        $cart = Session::get('pos.cart', []);

        if (isset($cart[$product->id])) {
            unset($cart[$product->id]);
            Session::put('pos.cart', $cart);
        }

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }

    public function checkout(StoreTransactionRequest $request)
    {
        $cart = Session::get('pos.cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Keranjang kosong.');
        }

        $items = collect($cart);
        $total = $items->sum('subtotal');
        $paymentMethod = $request->payment_method;
        $paid = $paymentMethod === 'qris' ? $total : (float) $request->paid;
        $change = $paid - $total;

        $qrisImage = null;
        if ($paymentMethod === 'qris') {
            $qrisImage = 'images/qris.jpeg';

            if ($request->hasFile('qris_image')) {
                $directory = public_path('images/qris');
                File::ensureDirectoryExists($directory);
                $filename = 'qris_'.now()->format('YmdHis').'_'.Str::random(8).'.'.$request->file('qris_image')->extension();
                $request->file('qris_image')->move($directory, $filename);
                $qrisImage = 'images/qris/'.$filename;
            }
        }

        if ($paid < $total) {
            return back()->with('error', 'Jumlah uang pelanggan kurang.');
        }

        $transaction = null;

        try {
            DB::transaction(function () use ($request, $items, $total, $paid, $change, $paymentMethod, $qrisImage, &$transaction) {
                $products = [];

                foreach ($items as $item) {
                    $product = Product::lockForUpdate()->find($item['id']);

                    if (! $product || $product->stock < $item['quantity']) {
                        $name = $product?->name ?? $item['name'];
                        $availableStock = $product?->stock ?? 0;

                        throw ValidationException::withMessages([
                            'stock' => "Stok {$name} hanya tersedia {$availableStock}.",
                        ]);
                    }

                    $products[$item['id']] = $product;
                }

                $transaction = Transaction::create([
                    'store_id' => auth()->user()->store_id,
                    'user_id' => auth()->id(),
                    'customer_id' => $request->customer_id,
                    'payment_method' => $paymentMethod,
                    'qris_image' => $qrisImage,
                    'invoice' => 'INV'.now()->format('YmdHis').rand(100, 999),
                    'total' => $total,
                    'paid' => $paid,
                    'change' => $change,
                    'items_count' => $items->sum('quantity'),
                    'notes' => $request->notes,
                ]);

                foreach ($items as $item) {
                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    $product = $products[$item['id']];
                    $product->stock -= $item['quantity'];
                    $product->save();
                }
            });
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->errors()['stock'][0]);
        }

        Session::forget('pos.cart');
        
        if ($transaction) {
            Session::put('pos.last_transaction', $transaction->id);
            return redirect()->route('pos.receipt', $transaction)->with('success', 'Transaksi berhasil disimpan.');
        }

        return back()->with('error', 'Gagal menyimpan transaksi.');
    }

    public function receipt(Transaction $transaction)
    {
        return view('pos.receipt', compact('transaction'));
    }
}
