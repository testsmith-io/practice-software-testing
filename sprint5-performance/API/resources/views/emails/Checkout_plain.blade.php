Hello {{ $name }},

Thanks for your order.

@foreach ($items as $item)
{{ $item['quantity'] }} x {{ $item['product']['name'] }}{{ isset($item['product']['co2_rating']) ? ' [CO2: ' . $item['product']['co2_rating'] . ']' : '' }}{{ ($item['product']['is_rental'] === 1) ? " (For rent)": "\t\t  " }}       $ {{ number_format($item['product']['price'],2) }}      $ {{ number_format($item['quantity'] * ($item['discounted_price'] ?? $item['product']['price']),2) }}{{PHP_EOL}}
@endforeach

@if ($additional_discount_percentage)
Subtotal: $ {{ number_format($subtotal,2) }}
Discount ({{ $additional_discount_percentage }}%): -$ {{ number_format($additional_discount_amount,2) }}
@endif
@if (isset($eco_discount_percentage) && $eco_discount_percentage > 0)
Eco-Friendly Discount ({{ $eco_discount_percentage }}%): -$ {{ number_format($eco_discount_amount,2) }}
@endif
Total: $ {{ number_format($total,2) }}

You can review your invoice in your account's "Invoices" section by clicking "My account" on our shop.

Best regards,
Team Practice Software Testing
