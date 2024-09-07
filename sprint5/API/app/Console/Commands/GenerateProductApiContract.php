<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Standalone\MockService\MockServerConfig;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Process\Process;

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
//        $pactDir = base_path('pacts');
//
//        if (!file_exists($pactDir)) {
//            mkdir($pactDir, 0777, true); // Creates the directory with necessary permissions
//            $this->info("Pact directory created at: {$pactDir}");
//        }
//
//        $process = new Process([
//            'pact-mock-service', 'start', '--host', 'localhost', '--port', '7203',
//            '--pact-dir', $pactDir, '--log', './storage/logs/pact.log',
//            '--consumer', 'AnyConsumer', '--provider', 'ProductAPI'
//        ]);
//
//        $process->start();
//        $process->waitUntil(function ($type, $buffer) {
//            return strpos($buffer, 'INFO') !== false; // Wait until the mock server is ready
//        });
//
//        if (!$process->isRunning()) {
//            $this->error("Failed to start pact-mock-service: " . $process->getErrorOutput());
//            return CommandAlias::FAILURE;
//        }

        sleep(3);

        // Configure the mock server
        $config = new MockServerConfig();
        $config->setHost('localhost')
            ->setPort(7200)
            ->setConsumer('AnyConsumer')
            ->setProvider('ProductAPI');

        // Create an InteractionBuilder for defining interactions
        $builder = new InteractionBuilder($config);

        // Create a ConsumerRequest object
        $consumerRequest = new ConsumerRequest();
        $consumerRequest
            ->setMethod('GET')
            ->setPath('/api/products')
            ->addHeader('Accept', 'application/json');

        // Create a ProviderResponse object
        $providerResponse = new ProviderResponse();
        $providerResponse
            ->setStatus(200)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                [
                    'id' => 1,
                    'name' => 'Product A',
                    'price' => 99.99,
                    'in_stock' => true
                ],
                [
                    'id' => 2,
                    'name' => 'Product B',
                    'price' => 49.99,
                    'in_stock' => false
                ]
            ]);

        // Define the interaction using the ConsumerRequest and ProviderResponse objects
        $builder->uponReceiving('A request for all products')
            ->with($consumerRequest)
            ->willRespondWith($providerResponse);

        // Write the Pact file to disk

        $builder->writePact();
        $builder->finalize();

        sleep(3);

        $this->info('Contract generated successfully and written to disk.');

//        $process->stop();

        return CommandAlias::SUCCESS;
    }
}
