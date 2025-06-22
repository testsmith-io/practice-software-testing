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

        // Define contract for all products
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
                        'id' => '01J7WPNDG3FYHQK9PVVMSHZE74',
                        'name' => 'Combination Pliers',
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris viverra felis nec pellentesque feugiat.',
                        'price' => 14.15,
                        'is_location_offer' => false,
                        'is_rental' => false,
                        'in_stock' => true,
                        'product_image' => [
                            'id' => '01J7WPNDFYEJA197785WQDXBFT',
                            'by_name' => 'Helinton Fantin',
                            'by_url' => 'https://unsplash.com/@fantin',
                            'source_name' => 'Unsplash',
                            'source_url' => 'https://unsplash.com/photos/W8BNwvOvW4M',
                            'file_name' => 'pliers01.avif',
                            'title' => 'Combination pliers'
                        ],
                        'category' => [
                            'id' => '01J7WPNDFV9R4DPXFANQ2GZ1MB',
                            'name' => 'Pliers',
                            'slug' => 'pliers',
                            'parent_id' => '01J7WPNDFRR91AASPVXMCASW41'
                        ],
                        'brand' => [
                            'id' => '01J7WPNDFK1F9KXNVPSVQP6EHY',
                            'name' => 'ForgeFlex Tools',
                            'slug' => 'forgeflex-tools'
                        ]
                    ],
                    [
                        'id' => '01J7WPNDG424DVVWMC492D29CW',
                        'name' => 'Pliers',
                        'description' => 'Nunc vulputate, orci at congue faucibus, enim neque sodales nulla, nec imperdiet augue odio vel nibh.',
                        'price' => 12.01,
                        'is_location_offer' => false,
                        'is_rental' => false,
                        'in_stock' => true,
                        'product_image' => [
                            'id' => '01J7WPNDFYEJA197785WQDXBFV',
                            'by_name' => 'Everyday basics',
                            'by_url' => 'https://unsplash.com/@zanardi',
                            'source_name' => 'Unsplash',
                            'source_url' => 'https://unsplash.com/photos/I8eTuMmxIfo',
                            'file_name' => 'pliers02.avif',
                            'title' => 'Pliers'
                        ],
                        'category' => [
                            'id' => '01J7WPNDFV9R4DPXFANQ2GZ1MB',
                            'name' => 'Pliers',
                            'slug' => 'pliers',
                            'parent_id' => '01J7WPNDFRR91AASPVXMCASW41'
                        ],
                        'brand' => [
                            'id' => '01J7WPNDFK1F9KXNVPSVQP6EHY',
                            'name' => 'ForgeFlex Tools',
                            'slug' => 'forgeflex-tools'
                        ]
                    ]
                ],
                'from' => 1,
                'last_page' => 5,
                'per_page' => 9,
                'to' => 9,
                'total' => 45
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
                        'id' => '01J7WPNDHX679T12D37VFHPXX9',
                        'name' => 'Excavator',
                        'description' => 'Aliquam tempus consequat ligula rutrum consequat. Pellentesque quis felis bibendum nunc facilisis vehicula et sed nunc.',
                        'price' => 136.5,
                        'is_location_offer' => true,
                        'is_rental' => true,
                        'in_stock' => false,
                        'product_image' => [
                            'id' => '01J7WPNDFYEJA197785WQDXBGN',
                            'by_name' => 'John Kakuk',
                            'by_url' => 'https://unsplash.com/@mgnfy',
                            'source_name' => 'Unsplash',
                            'source_url' => 'https://unsplash.com/photos/HvvPceHYLOg',
                            'file_name' => 'excavator01.avif',
                            'title' => 'Excavator'
                        ],
                        'category' => [
                            'id' => '01J7WPNDFV9R4DPXFANQ2GZ1MH',
                            'name' => 'Drill',
                            'slug' => 'drill',
                            'parent_id' => '01J7WPNDFRR91AASPVXMCASW42'
                        ],
                        'brand' => [
                            'id' => '01J7WPNDFK1F9KXNVPSVQP6EHY',
                            'name' => 'ForgeFlex Tools',
                            'slug' => 'forgeflex-tools'
                        ]
                    ],
                    [
                        'id' => '01J7WPNDHY8CXJMQQWMNC55TBM',
                        'name' => 'Bulldozer',
                        'description' => 'Maecenas varius suscipit rutrum. Quisque vel egestas mi. Cras gravida vitae ipsum non placerat. Nulla facilisi.',
                        'price' => 147.5,
                        'is_location_offer' => true,
                        'is_rental' => true,
                        'in_stock' => false,
                        'product_image' => [
                            'id' => '01J7WPNDFYEJA197785WQDXBGP',
                            'by_name' => 'Zac Edmonds',
                            'by_url' => 'https://unsplash.com/@zacedmo',
                            'source_name' => 'Unsplash',
                            'source_url' => 'https://unsplash.com/photos/N1LBcqLP9ec',
                            'file_name' => 'bulldozer01.avif',
                            'title' => 'Bulldozer'
                        ],
                        'category' => [
                            'id' => '01J7WPNDFV9R4DPXFANQ2GZ1MH',
                            'name' => 'Drill',
                            'slug' => 'drill',
                            'parent_id' => '01J7WPNDFRR91AASPVXMCASW42'
                        ],
                        'brand' => [
                            'id' => '01J7WPNDFK1F9KXNVPSVQP6EHY',
                            'name' => 'ForgeFlex Tools',
                            'slug' => 'forgeflex-tools'
                        ]
                    ],
                    [
                        'id' => '01J7WPNDHZQ8VZM0ZTFNQXTMAN',
                        'name' => 'Crane',
                        'description' => 'Donec lacus nulla, posuere id nibh convallis, lobortis sollicitudin sem.',
                        'price' => 153.5,
                        'is_location_offer' => true,
                        'is_rental' => true,
                        'in_stock' => false,
                        'product_image' => [
                            'id' => '01J7WPNDFYEJA197785WQDXBGM',
                            'by_name' => 'Ade Adebowale',
                            'by_url' => 'https://unsplash.com/@adebowax',
                            'source_name' => 'Unsplash',
                            'source_url' => 'https://unsplash.com/photos/DKr6BEdI2sE',
                            'file_name' => 'crane01.avif',
                            'title' => 'Crane'
                        ],
                        'category' => [
                            'id' => '01J7WPNDFV9R4DPXFANQ2GZ1MH',
                            'name' => 'Drill',
                            'slug' => 'drill',
                            'parent_id' => '01J7WPNDFRR91AASPVXMCASW42'
                        ],
                        'brand' => [
                            'id' => '01J7WPNDFK1F9KXNVPSVQP6EHZ',
                            'name' => 'MightyCraft Hardware',
                            'slug' => 'mightycraft-hardware'
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
