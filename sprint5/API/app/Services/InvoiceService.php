<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Download;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentBankTransferDetails;
use App\Models\PaymentCashOnDeliveryDetails;
use App\Models\PaymentCreditCardDetails;
use App\Models\PaymentGiftCardDetails;
use App\Models\PaymentBnplDetails;
use App\Jobs\UpdateProductInventory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    protected $invoiceNumberGenerator;

    public function __construct(InvoiceNumberGenerator $invoiceNumberGenerator)
    {
        $this->invoiceNumberGenerator = $invoiceNumberGenerator;
    }

    public function getInvoices($isAdmin)
    {
        $query = Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details')->orderBy('invoice_date', 'DESC');

        if (!$isAdmin) {
            $query->where('user_id', Auth::user()->id);
        }

        return $query->paginate();
    }

    public function createInvoice(array $data, $cartId)
    {
        $data['user_id'] = Auth::user()->id;
        $data['invoice_date'] = now();
        $data['invoice_number'] = $this->invoiceNumberGenerator->generate([
            'table' => 'invoices',
            'field' => 'invoice_number',
            'length' => 14,
            'prefix' => 'INV-' . now()->year
        ]);

        $invoice = Invoice::create($data);
        $cart = Cart::with('cartItems', 'cartItems.product')->findOrFail($cartId);

        $subTotalPrice = 0;

        foreach ($cart->cartItems as $cartItem) {
            $quantity = $cartItem['quantity'];
            $unitPrice = $cartItem['product']->price;

            $discountedPrice = $cartItem->discount_percentage !== null
                ? round($cartItem->product->price * (1 - ($cartItem->discount_percentage / 100)), 2)
                : null;

            UpdateProductInventory::dispatch($cartItem['product']->id, $quantity);

            $invoice->invoicelines()->create([
                'product_id' => $cartItem['product']->id,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'discount_percentage' => $cartItem->discount_percentage,
                'discounted_price' => $discountedPrice
            ]);

            $subTotalPrice += $discountedPrice ? $quantity * $discountedPrice : $quantity * $unitPrice;
        }

        $discountAmount = $subTotalPrice * ($cart->additional_discount_percentage / 100);
        $totalPrice = $subTotalPrice - $discountAmount;

        $invoice->update([
            'subtotal' => $subTotalPrice,
            'total' => $totalPrice,
            'additional_discount_percentage' => $cart->additional_discount_percentage,
            'additional_discount_amount' => $discountAmount,
        ]);

        return $invoice;
    }

    public function handlePayment($invoiceId, $paymentMethod, array $details)
    {
        $payment = new Payment([
            'invoice_id' => $invoiceId,
            'payment_method' => $paymentMethod,
        ]);

        $paymentDetails = null;

        switch ($paymentMethod) {
            case 'bank-transfer':
                $paymentDetails = new PaymentBankTransferDetails($details);
                break;
            case 'cash-on-delivery':
                $paymentDetails = new PaymentCashOnDeliveryDetails();
                break;
            case 'credit-card':
                $paymentDetails = new PaymentCreditCardDetails($details);
                break;
            case 'buy-now-pay-later':
                $paymentDetails = new PaymentBnplDetails($details);
                break;
            case 'gift-card':
                $paymentDetails = new PaymentGiftCardDetails($details);
                break;
            default:
                throw new \InvalidArgumentException("Invalid payment method: {$paymentMethod}");
        }

        if ($paymentDetails) {
            $paymentDetails->save();
            $payment->payment_details_id = $paymentDetails->id;
            $payment->payment_details_type = get_class($paymentDetails);
        }

        $payment->save();
    }


    public function getInvoice($id, $isAdmin)
    {
        $query = Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details');

        if (!$isAdmin) {
            $query->where('user_id', Auth::user()->id);
        }

        return $query->findOrFail($id);
    }

    public function downloadPDF($invoiceNumber)
    {
        $filePath = "invoices/{$invoiceNumber}.pdf";
        if (Storage::exists($filePath)) {
            return Storage::download($filePath, "{$invoiceNumber}.pdf");
        }
        return null;
    }

    public function updateInvoiceStatus($id, array $data)
    {
        return Invoice::where('id', $id)->update($data);
    }

    public function searchInvoices($query, $isAdmin)
    {
        $baseQuery = Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details')
            ->where('invoice_number', 'like', "%$query%")
            ->orWhere('billing_address', 'like', "%$query%")
            ->orWhere('status', 'like', "%$query%")
            ->orderBy('invoice_date', 'DESC');

        if (!$isAdmin) {
            $baseQuery->where('user_id', Auth::user()->id);
        }

        return $baseQuery->paginate();
    }

    public function deleteInvoice($id)
    {
        return Invoice::find($id)->where('user_id', Auth::user()->id)->delete();
    }

    public function getPDFStatus($invoiceNumber)
    {
        $status = Download::where('name', $invoiceNumber)->first(['status']);
        return $status ? $status->toArray() : ['status' => 'NOT_INITIATED'];
    }

    public function updateInvoice($id, array $data)
    {
        return Invoice::where('id', $id)->where('user_id', Auth::user()->id)->update($data);
    }

    public function patchInvoice($id, array $data)
    {
        $userId = Auth::user()->id;
        return Invoice::where('id', $id)->where('user_id', $userId)->update($data);
    }
}
