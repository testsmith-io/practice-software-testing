Hello {{ $name }},

Thanks for your order.

@foreach ($items as $item)
{{ $item['quantity'] }} x {{ $item['name'] }}{{ ($item['is_rental'] === 1) ? " (For rent)": "\t\t  " }}       $ {{ number_format($item['price'],2) }}      $ {{ number_format($item['total'],2) }}{{PHP_EOL}}
@endforeach

Total: $ {{ number_format($item['total'],2) }}

You can review your invoice in your account's "Invoices" section by clicking "My account" on our shop.

Best regards,
Team Practice Software Testing
