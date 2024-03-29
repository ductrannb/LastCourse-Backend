<?php

namespace App\Services;

use App\Notifications\CreateBillNotification;
use App\Notifications\UpdateStatusBillNotification;
use App\Repositories\BillRepository;
use App\Repositories\CarrierRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BillService
{
    private $bill_repository;
    private $carrier_repository;
    private $product_service;
    private $bill_detail_service;
    private $cart_service;

    public function __construct()
    {
        $this->bill_repository = new BillRepository();
        $this->carrier_repository = new CarrierRepository();
        $this->product_service = new ProductService();
        $this->bill_detail_service = new BillDetailService();
        $this->cart_service = new CartService();
    }

    public function getFilterBill($bills, $search_string)
    {
        $search_string = strtolower($search_string);
        $filtered_bills = $bills->filter(function ($bill) use ($search_string) {
            if (
                str_contains(strtolower($bill->receiver), $search_string)
                || str_contains(strtolower($bill->phone), $search_string)
                || str_contains(strtolower($bill->address), $search_string)
                || str_contains(strtolower($bill->code), $search_string)
                || $bill->products->count() > 0
            ) {
                return true;
            }
            return false;
        })->values();
        return $filtered_bills;
    }

    public function find($id)
    {
        return $this->bill_repository->find($id);
    }

    public function updateStatus($id, $status)
    {
        $bill = $this->bill_repository->updateStatus($id, $status);
        $bill->user->notify(new UpdateStatusBillNotification($bill));
        $bill->shop->notify(new UpdateStatusBillNotification($bill, true));
        return $bill;
    }

    public function updateOrCreate(array $data, $id = null)
    {
        $carts = $this->cart_service->findCarts($data["cart_ids"]);
        $shop = $carts->first()->product->shop;
        $products = $carts->map(function ($cart) {
            return [
                "id" => $cart->product_id,
                "variant_id" => $cart->product_variant_id,
                "quantity" => $cart->quantity,
                "price" => $this->getPrice($cart->product, $cart->variant, $cart->quantity),
            ];
        });
        $data = array_merge($data, [
            "shop_id" => $shop->id,
            "carrier_id" => $shop->carrier_id,
            "user_id" => auth()->id(),
            "shipping_fee" => self::getShippingFee($shop->carrier_id, $products->pluck("id")),
        ]);
        foreach ($carts as $cart) {
            $cart->delete();
        }
        Arr::forget($data, "cart_ids");
        $data["code"] = Str::upper(Str::random(16));
        $bill = $this->bill_repository->create($data);
        $details = $this->bill_detail_service->createDetails($bill->id, $products);
        foreach ($details as $detail) {
            if ($variant = $detail->variant) {
                $variant->quantity -= $detail->quantity;
                $variant->save();
            }
            $product = $detail->product;
            $product->inventory -= $detail->quantity;
            $product->sold += $detail->quantity;
            $product->save();
        }
        $total = collect($details)->map(function ($detail) {
            return $detail->price * $detail->quantity;
        })->toArray();

        $bill->update(["total" => array_sum($total) + $bill->shipping_fee]);

        auth()->user()->update([
            "last_receiver" => $data["receiver"],
            "last_address" => $data["address"],
            "last_phone" => $data["phone"],
        ]);

        $bill->user->notify(new CreateBillNotification($bill));
        $bill->shop->notify(new CreateBillNotification($bill, true));
        return $bill;
    }

    public static function getShippingFee($carrier_id, $product_ids)
    {
        $billsv = new BillService();
        $carrier = $billsv->carrier_repository->find($carrier_id);
        $total = 0;
        foreach ($product_ids as $product_id) {
            if ($product = $billsv->product_service->find($product_id)) {
                $total += $product->getShippingFee();
            }
        }

        // gia ship co ban * tong ship cac san pham * he so random
        return intval(round($carrier->price * (1 + $total)));
    }

    private function getPrice($product, $variant, $quantity)
    {
        $price = $product->promotional_price ?? $product->price;
        if ($variant != null) {
            $price = $variant->price;
        }
        if ($product->is_buy_more_discount) {
            $discount_ranges = $product->discountRanges;
            foreach ($discount_ranges as $discount) {
                if ($quantity >= $discount->min && $quantity < $discount->max) {
                    $price -= $discount->amount;
                    break;
                }
            }
        }
        return $price;
    }
}
