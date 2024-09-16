<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Standalone\MockService\MockServerConfig;
use Symfony\Component\Console\Command\Command as CommandAlias;

class GenerateProductApiContract extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contract:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the Pact contract for the ProductAPI provider.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Configure the mock server
        $config = new MockServerConfig();
        $config->setHost('localhost')
            ->setPort(7203)
            ->setConsumer('AnyConsumer')
            ->setProvider('ProductAPI');

        // Create an InteractionBuilder for defining interactions
        $builder = new InteractionBuilder($config);

        // Define contract for all products (no query params)
        $consumerRequest = new ConsumerRequest();
        $consumerRequest
            ->setMethod('GET')
            ->setPath('/products')
            ->setQuery('');

        $providerResponse = new ProviderResponse();
        $providerResponse
            ->setStatus(200)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'current_page' => 1,
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Combination Pliers',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris viverra felis nec pellentesque feugiat.',
                        'stock' => 11,
                        'price' => 14.15,
                        'is_location_offer' => 0,
                        'is_rental' => 0,
                        'brand_id' => 1,
                        'category_id' => 7,
                        'product_image_id' => 1,
                        'product_image' => [
                            'id' => 1,
                            'by_name' => 'Helinton Fantin',
                            'by_url' => 'https://unsplash.com/@fantin',
                            'source_name' => 'Unsplash',
                            'source_url' => 'https://unsplash.com/photos/W8BNwvOvW4M',
                            'file_name' => 'pliers01.jpeg',
                            'title' => 'Combination pliers'
                        ],
                        'category' => [
                            'id' => 7,
                            'parent_id' => 1,
                            'name' => 'Pliers',
                            'slug' => 'pliers'
                        ],
                        'brand' => [
                            'id' => 1,
                            'name' => 'Brand name 1',
                            'slug' => 'brand-name-1'
                        ]
                    ],
                    [
                        'id' => 2,
                        'name' => 'Pliers',
                        'description' => 'Nunc vulputate, orci at congue faucibus, enim neque sodales nulla, nec imperdiet augue odio vel nibh.',
                        'stock' => 11,
                        'price' => 12.01,
                        'is_location_offer' => 0,
                        'is_rental' => 0,
                        'brand_id' => 1,
                        'category_id' => 7,
                        'product_image_id' => 2,
                        'product_image' => [
                            'id' => 2,
                            'by_name' => 'Everyday basics',
                            'by_url' => 'https://unsplash.com/@zanardi',
                            'source_name' => 'Unsplash',
                            'source_url' => 'https://unsplash.com/photos/I8eTuMmxIfo',
                            'file_name' => 'pliers02.jpeg',
                            'title' => 'Pliers'
                        ],
                        'category' => [
                            'id' => 7,
                            'parent_id' => 1,
                            'name' => 'Pliers',
                            'slug' => 'pliers'
                        ],
                        'brand' => [
                            'id' => 1,
                            'name' => 'Brand name 1',
                            'slug' => 'brand-name-1'
                        ]
                    ]
                ],
                'from' => 1,
                'last_page' => 3,
                'per_page' => 9,
                'to' => 9,
                'total' => 26
            ]);

        $builder->uponReceiving('A request for all products')
            ->with($consumerRequest)
            ->willRespondWith($providerResponse);

        // Define contract for product rentals (is_rental=true)
        $rentalRequest = new ConsumerRequest();
        $rentalRequest
            ->setMethod('GET')
            ->setPath('/products')
            ->setQuery('is_rental=true');

        $rentalResponse = new ProviderResponse();
        $rentalResponse
            ->setStatus(200)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'current_page' => 1,
                'data' => [
                    [
                        'id' => 27,
                        'name' => 'Excavator',
                        'description' => 'Aliquam tempus consequat ligula rutrum consequat. Pellentesque quis felis bibendum nunc facilisis vehicula et sed nunc.',
                        'stock' => null,
                        'price' => 136.5,
                        'is_location_offer' => 1,
                        'is_rental' => 1,
                        'brand_id' => 1,
                        'category_id' => 12,
                        'product_image_id' => 28,
                        'product_image' => [
                            'id' => 28,
                            'by_name' => 'John Kakuk',
                            'by_url' => 'https://unsplash.com/@mgnfy',
                            'source_name' => 'Unsplash',
                            'source_url' => 'https://unsplash.com/photos/HvvPceHYLOg',
                            'file_name' => 'excavator01.jpg',
                            'title' => 'Excavator'
                        ],
                        'category' => [
                            'id' => 12,
                            'parent_id' => null,
                            'name' => 'Other',
                            'slug' => 'other'
                        ],
                        'brand' => [
                            'id' => 1,
                            'name' => 'Brand name 1',
                            'slug' => 'brand-name-1'
                        ]
                    ],
                    [
                        'id' => 28,
                        'name' => 'Bulldozer',
                        'description' => 'Maecenas varius suscipit rutrum. Quisque vel egestas mi. Cras gravida vitae ipsum non placerat. Nulla facilisi.',
                        'stock' => null,
                        'price' => 147.5,
                        'is_location_offer' => 1,
                        'is_rental' => 1,
                        'brand_id' => 1,
                        'category_id' => 12,
                        'product_image_id' => 29,
                        'product_image' => [
                            'id' => 29,
                            'by_name' => 'Zac Edmonds',
                            'by_url' => 'https://unsplash.com/@zacedmo',
                            'source_name' => 'Unsplash',
                            'source_url' => 'https://unsplash.com/photos/N1LBcqLP9ec',
                            'file_name' => 'bulldozer01.jpg',
                            'title' => 'Bulldozer'
                        ],
                        'category' => [
                            'id' => 12,
                            'parent_id' => null,
                            'name' => 'Other',
                            'slug' => 'other'
                        ],
                        'brand' => [
                            'id' => 1,
                            'name' => 'Brand name 1',
                            'slug' => 'brand-name-1'
                        ]
                    ],
                    [
                        'id' => 29,
                        'name' => 'Crane',
                        'description' => 'Donec lacus nulla, posuere id nibh convallis, lobortis sollicitudin sem. Nulla facilisi.',
                        'stock' => null,
                        'price' => 153.5,
                        'is_location_offer' => 1,
                        'is_rental' => 1,
                        'brand_id' => 1,
                        'category_id' => 12,
                        'product_image_id' => 27,
                        'product_image' => [
                            'id' => 27,
                            'by_name' => 'Ade Adebowale',
                            'by_url' => 'https://unsplash.com/@adebowax',
                            'source_name' => 'Unsplash',
                            'source_url' => 'https://unsplash.com/photos/DKr6BEdI2sE',
                            'file_name' => 'crane01.jpg',
                            'title' => 'Crane'
                        ],
                        'category' => [
                            'id' => 12,
                            'parent_id' => null,
                            'name' => 'Other',
                            'slug' => 'other'
                        ],
                        'brand' => [
                            'id' => 1,
                            'name' => 'Brand name 1',
                            'slug' => 'brand-name-1'
                        ]
                    ]
                ],
                'from' => 1,
                'last_page' => 1,
                'per_page' => 9,
                'to' => 3,
                'total' => 3
            ]);

        $builder->uponReceiving('A request for rental products')
            ->with($rentalRequest)
            ->willRespondWith($rentalResponse);

        // Perform the consumer's actual request
        $client = new Client();
        $client->get('http://localhost:7203/products');
        $client->get('http://localhost:7203/products?is_rental=true');

        $builder->writePact();
        $builder->finalize();

        $this->info('Contract generated successfully and written to disk.');

        return CommandAlias::SUCCESS;
    }
}
