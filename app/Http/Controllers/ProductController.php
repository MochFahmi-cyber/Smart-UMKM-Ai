<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\CashFlow;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')->where('store_id', auth()->user()->store_id)
            ->when($request->q, function ($query, $q) {
                $query->where(function($qBuilder) use ($q) {
                    $qBuilder->where('name', 'like', "%{$q}%")
                             ->orWhere('sku', 'like', "%{$q}%");
                });
            })
            ->when($request->category, function($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->status, function($query, $status) {
                $query->where('active', $status === 'active' ? 1 : 0);
            })
            ->orderBy($request->sort === 'newest' ? 'created_at' : 'name', $request->sort === 'newest' ? 'desc' : 'asc')
            ->paginate(10)
            ->withQueryString();

        $categories = \App\Models\Category::where('store_id', auth()->user()->store_id)->orderBy('name')->get();
        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('store_id', auth()->user()->store_id)->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        $product = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['store_id'] = auth()->user()->store_id;
            $data['user_id'] = auth()->id();
            
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }
            
            $product = Product::create($data);

            $this->recordStockPurchase($product, $product->stock, (float) $product->purchase_price, 'Stok awal');

            return $product;
        });

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('store_id', auth()->user()->store_id)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        DB::transaction(function () use ($request, $product) {
            $previousStock = $product->stock;
            $data = $request->validated();

            $product->update($data);

            $addedStock = $product->stock - $previousStock;
            $this->recordStockPurchase($product, $addedStock, (float) $data['purchase_price'], 'Penambahan stok');
        });

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    private function recordStockPurchase(Product $product, int $quantity, float $purchasePrice, string $prefix): void
    {
        if ($quantity <= 0 || $purchasePrice <= 0) {
            return;
        }

        CashFlow::create([
            'store_id' => auth()->user()->store_id,
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'type' => CashFlow::STOCK_PURCHASE,
            'amount' => $quantity * $purchasePrice,
            'description' => "{$prefix}: {$product->name} ({$quantity} item)",
        ]);
    }
}
