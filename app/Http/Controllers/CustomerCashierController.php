<?php

namespace App\Http\Controllers;

use App\Models\CustomerCashierToken;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerCashierController extends Controller
{
    public function index($tokenStr)
    {
        $token = CustomerCashierToken::with('user')->where('token', $tokenStr)->where('is_active', true)->firstOrFail();
        
        $categories = Category::where('store_id', $token->store_id)->orderBy('name')->get();
        $query = Product::where('store_id', $token->store_id)->where('active', true);
        
        if (request('category')) {
            $query->where('category_id', request('category'));
        }
        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }
        
        $products = $query->orderBy('name')->get();
        $cart = session()->get('customer_cart_'.$token->token, []);

        $activeTransactions = [];
        $customerId = request()->cookie('smart_umkm_customer_id');
        
        if ($customerId) {
            $activeTransactions = \App\Models\Transaction::where('customer_id', $customerId)
                ->where('store_id', $token->store_id)
                ->whereNotIn('status', ['completed', 'rejected'])
                ->get();
        } else {
            // Fallback to session tracking if cookie not available yet
            $recentInvoices = session()->get('customer_recent_invoices_'.$token->token, []);
            if (!empty($recentInvoices)) {
                $activeTransactions = \App\Models\Transaction::whereIn('invoice', $recentInvoices)
                    ->whereNotIn('status', ['completed', 'rejected'])
                    ->get();
            }
        }

        return view('customer.kasir.index', compact('token', 'products', 'categories', 'cart', 'activeTransactions'));
    }

    public function addToCart(Request $request, $tokenStr)
    {
        $token = CustomerCashierToken::where('token', $tokenStr)->where('is_active', true)->firstOrFail();
        $product = Product::findOrFail($request->product_id);
        
        $cart = session()->get('customer_cart_'.$token->token, []);
        
        if(isset($cart[$product->id])) {
            $cart[$product->id]['quantity']++;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sell_price,
                'quantity' => 1,
                'stock' => $product->stock
            ];
        }
        
        session()->put('customer_cart_'.$token->token, $cart);
        return back()->with('success', 'Produk ditambahkan.');
    }

    public function updateCart(Request $request, $tokenStr, $productId)
    {
        $token = CustomerCashierToken::where('token', $tokenStr)->where('is_active', true)->firstOrFail();
        $cart = session()->get('customer_cart_'.$token->token, []);
        
        if(isset($cart[$productId])) {
            $action = $request->action;
            if($action === 'increase') {
                $cart[$productId]['quantity']++;
            } elseif($action === 'decrease' && $cart[$productId]['quantity'] > 1) {
                $cart[$productId]['quantity']--;
            }
            session()->put('customer_cart_'.$token->token, $cart);
        }
        return back();
    }

    public function removeCart($tokenStr, $productId)
    {
        $token = CustomerCashierToken::where('token', $tokenStr)->where('is_active', true)->firstOrFail();
        $cart = session()->get('customer_cart_'.$token->token, []);
        
        if(isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('customer_cart_'.$token->token, $cart);
        }
        return back();
    }

    public function checkout(Request $request, $tokenStr)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,qris,transfer',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => in_array($request->payment_method, ['qris', 'transfer']) ? 'required|string' : 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $token = CustomerCashierToken::where('token', $tokenStr)->where('is_active', true)->firstOrFail();
        $cart = session()->get('customer_cart_'.$token->token, []);
        
        if(count($cart) === 0) return back()->with('error', 'Keranjang kosong.');

        $total = 0;
        $itemsCount = 0;
        
        // Validate stock
        foreach($cart as $item) {
            $product = Product::find($item['id']);
            if(!$product || $product->stock < $item['quantity']) {
                return back()->with('error', 'Stok tidak mencukupi untuk '.$item['name']);
            }
            $total += $product->sell_price * $item['quantity'];
            $itemsCount += $item['quantity'];
        }

        // Store or update customer
        $customer = \App\Models\Customer::updateOrCreate(
            ['phone' => $request->phone, 'store_id' => $token->store_id],
            ['name' => $request->name, 'address' => $request->address, 'user_id' => $token->user_id]
        );

        $invoice = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        
        $status = in_array($request->payment_method, ['qris', 'transfer']) ? 'awaiting_payment' : 'pending';

        $transaction = Transaction::create([
            'store_id' => $token->store_id, // Belongs to the store
            'user_id' => $token->user_id,
            'customer_id' => $customer->id,
            'invoice' => $invoice,
            'total' => $total,
            'paid' => 0,
            'change' => 0,
            'items_count' => $itemsCount,
            'status' => $status,
            'notes' => $request->notes,
            'payment_method' => $request->payment_method
        ]);

        foreach($cart as $item) {
            $product = Product::find($item['id']);
            $subtotal = $product->sell_price * $item['quantity'];
            
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->sell_price,
                'subtotal' => $subtotal
            ]);
            
            // Reduce stock
            $product->decrement('stock', $item['quantity']);
        }

        // Clear cart
        session()->forget('customer_cart_'.$token->token);

        // Store invoice in session to track pending transactions (fallback)
        $recentInvoices = session()->get('customer_recent_invoices_'.$token->token, []);
        if(!in_array($invoice, $recentInvoices)) {
            $recentInvoices[] = $invoice;
            session()->put('customer_recent_invoices_'.$token->token, $recentInvoices);
        }

        // Set persistent cookie for 30 days to track customer identity reliably
        \Illuminate\Support\Facades\Cookie::queue('smart_umkm_customer_id', $customer->id, 60 * 24 * 30);

        return redirect()->route('customer-kasir.success', ['token' => $token->token, 'invoice' => $invoice]);
    }

    public function success($tokenStr, $invoice)
    {
        $token = CustomerCashierToken::where('token', $tokenStr)->where('is_active', true)->firstOrFail();
        $transaction = Transaction::where('invoice', $invoice)->where('store_id', $token->store_id)->firstOrFail();
        $store = \App\Models\Store::findOrFail($token->store_id);

        return view('customer.kasir.success', compact('token', 'transaction', 'store'));
    }

    public function confirmPayment(Request $request, $tokenStr, $invoice)
    {
        $request->validate([
            'payment_proof' => 'nullable|image|max:2048'
        ]);

        $token = CustomerCashierToken::where('token', $tokenStr)->where('is_active', true)->firstOrFail();
        $transaction = Transaction::where('invoice', $invoice)->where('store_id', $token->store_id)->firstOrFail();
        
        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payment_proofs', 'public');
            $transaction->payment_proof = $path;
        }

        $transaction->status = 'payment_review';
        $transaction->save();

        return redirect()->back()->with('success', 'Pembayaran berhasil dikonfirmasi. Menunggu pengecekan toko.');
    }
}
