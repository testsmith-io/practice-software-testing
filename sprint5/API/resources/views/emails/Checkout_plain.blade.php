Hello {{ $name }},

Thanks for your order.

@foreach ($items as $item)
{{ $item['quantity'] }} x {{ $item['product']['name'] }}{{ ($item['discount_percentage']) ? ' -'.$item['discount_percentage'].'%' : '' }} {{ ($item['is_rental'] === 1) ? " (For rent)": "\t\t  " }}       $ {{ ($item['discount_percentage']) ? number_format($item['discounted_price'],2) : number_format($item['product']['price'],2) }}      $ {{ ($item['discount_percentage']) ? number_format($item['quantity'] * $item['discounted_price'],2) : number_format($item['quantity'] *  $item['product']['price'],2) }}{{PHP_EOL}}
@endforeach

@if($additional_discount_percentage)
Subtotal: $ {{ number_format($subtotal,2) }}
Discount ({{$additional_discount_percentage}}%): - $ {{ number_format($additional_discount_amount,2) }}
@endif
Total: $ {{ number_format($total,2) }}

You can review your invoice in your account's "Invoices" section by clicking "My account" on our shop.

Best regards,
Team Practice Software Testing
