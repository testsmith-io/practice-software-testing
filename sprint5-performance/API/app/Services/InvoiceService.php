<?php

namespace App\Services;

use App\Jobs\UpdateProductInventory;
use App\Models\Cart;
use App\Models\Download;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentBankTransferDetails;
use App\Models\PaymentBnplDetails;
use App\Models\PaymentCashOnDeliveryDetails;
use App\Models\PaymentCreditCardDetails;
use App\Models\PaymentGiftCardDetails;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class InvoiceService
{
    protected $invoiceNumberGenerator;

    public function __construct(InvoiceNumberGenerator $invoiceNumberGenerator)
    {
        $this->invoiceNumberGenerator = $invoiceNumberGenerator;
    }

    public function getInvoices($isAdmin)
    {
        Log::info('Fetching invoices', ['is_admin' => $isAdmin]);

        $query = Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details')->orderBy('invoice_date', 'DESC');

        if (!$isAdmin) {
            $query->where('user_id', Auth::user()->id);
        }

        return $query->paginate();
    }

    public function createInvoice(array $data, $cartId)
    {
        Log::info('Creating invoice', ['cart_id' => $cartId]);

        $data['user_id'] = Auth::user()->id;
        $data['invoice_date'] = now();
        $data['invoice_number'] = $this->invoiceNumberGenerator->generate([
            'table' => 'invoices',
            'field' => 'invoice_number',
            'length' => 14,
            'prefix' => 'INV-' . now()->year
        ]);

        $invoice = Invoice::create($data);
        Log::debug('Invoice created', ['invoice_id' => $invoice->id]);

        $cart = Cart::with('cartItems', 'cartItems.product')->findOrFail($cartId);

        $subTotalPrice = 0;

        foreach ($cart->cartItems as $cartItem) {
            $quantity = $cartItem['quantity'];
            $unitPrice = $cartItem['product']->price;

            $discountedPrice = $cartItem->discount_percentage !== null
                ? round($cartItem->product->price * (1 - ($cartItem->discount_percentage / 100)), 2)
                : null;

            UpdateProductInventory::dispatch($cartItem['product']->id, $quantity);
            Log::debug('Dispatched inventory update', ['product_id' => $cartItem['product']->id, 'quantity' => $quantity]);

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

        Log::info('Invoice finalized', ['invoice_id' => $invoice->id, 'total' => $totalPrice]);

        return $invoice;
    }

    public function handlePayment($invoiceId, $paymentMethod, array $details)
    {
        Log::info('Handling payment', ['invoice_id' => $invoiceId, 'method' => $paymentMethod]);

        $payment = new Payment([
            'invoice_id' => $invoiceId,
            'payment_method' => $paymentMethod,
        ]);

        $paymentDetails = null;

        try {
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
                    throw new InvalidArgumentException("Invalid payment method: {$paymentMethod}");
            }

            if ($paymentDetails) {
                $paymentDetails->save();
                $payment->payment_details_id = $paymentDetails->id;
                $payment->payment_details_type = get_class($paymentDetails);
                Log::debug('Payment details saved', ['type' => $payment->payment_details_type]);
            }

            $payment->save();
            Log::info('Payment saved', ['invoice_id' => $invoiceId, 'payment_id' => $payment->id]);
        } catch (Exception $e) {
            Log::error('Payment handling failed', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getInvoice($id, $isAdmin)
    {
        Log::info('Fetching invoice', ['invoice_id' => $id, 'is_admin' => $isAdmin]);

        $query = Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details');

        if (!$isAdmin) {
            $query->where('user_id', Auth::user()->id);
        }

        return $query->findOrFail($id);
    }

    public function downloadPDF($invoiceNumber)
    {
        Log::info('Attempting PDF download', ['invoice_number' => $invoiceNumber]);

        $filePath = "invoices/{$invoiceNumber}.pdf";

        if (Storage::exists($filePath)) {
            Log::debug('PDF found, downloading', ['file' => $filePath]);
            return Storage::download($filePath, "{$invoiceNumber}.pdf");
        }

        Log::warning('PDF not found', ['file' => $filePath]);
        return null;
    }

    public function updateInvoiceStatus($id, array $data)
    {
        Log::info('Updating invoice status', ['invoice_id' => $id, 'data' => $data]);
        return Invoice::where('id', $id)->update($data);
    }

    public function searchInvoices($query, $isAdmin)
    {
        Log::info('Searching invoices', ['query' => $query, 'is_admin' => $isAdmin]);

        $baseQuery = Invoice::with('invoicelines', 'invoicelines.product', 'payment', 'payment.payment_details')
            ->where('invoice_number', 'like', "%$query%")
            ->orWhere('billing_street', 'like', "%$query%")
            ->orWhere('status', 'like', "%$query%")
            ->orderBy('invoice_date', 'DESC');

        if (!$isAdmin) {
            $baseQuery->where('user_id', Auth::user()->id);
        }

        return $baseQuery->paginate();
    }

    public function deleteInvoice($id)
    {
        Log::info('Deleting invoice', ['invoice_id' => $id]);
        return Invoice::find($id)->where('user_id', Auth::user()->id)->delete();
    }

    public function getPDFStatus($invoiceNumber)
    {
        $status = Download::where('name', $invoiceNumber)->first(['status']);
        $logStatus = $status ? $status->status : 'NOT_INITIATED';
        Log::debug('PDF generation status', ['invoice_number' => $invoiceNumber, 'status' => $logStatus]);
        return $status ? $status->toArray() : ['status' => 'NOT_INITIATED'];
    }

    public function updateInvoice($id, array $data)
    {
        Log::info('Updating invoice', ['invoice_id' => $id]);
        return Invoice::where('id', $id)->where('user_id', Auth::user()->id)->update($data);
    }

    public function patchInvoice($id, array $data)
    {
        $userId = Auth::user()->id;
        Log::info('Patching invoice', ['invoice_id' => $id, 'user_id' => $userId]);
        return Invoice::where('id', $id)->where('user_id', $userId)->update($data);
    }
}
