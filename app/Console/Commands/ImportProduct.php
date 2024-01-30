<?php

namespace App\Console\Commands;

use App\DTO\Exceptions\CategoryValidationException;
use App\DTO\Exceptions\ProductValidationException;
use App\Importer\Exception\ProductImporterException;
use App\Importer\ProductImporterInterface;
use App\Models\Repository\CategoryRepository;
use App\Models\Repository\ProductRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportProduct extends Command
{
    protected $signature = 'product:import
                            {source? : The source of the products to import}';
    protected $description = 'Import products from a specific source';
    private ?ProductImporterInterface $source;

    /**
     * @param array<int, ProductImporterInterface> $sources
     */
    public function __construct(
        private readonly array $sources,
        private readonly ProductRepository $productRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Process start');
        try {
            $this->setSource();
            $this->info('Start importing categories...');
            $this->categoryRepository->createFromCollection($this->source->getCategories());
            $this->info('Start importing products...');
            $this->productRepository->createFromCollection($this->source->getProducts());
        } catch (\InvalidArgumentException $e) {
            $this->log($e);
            $this->error($e->getMessage());

            return self::FAILURE;
        } catch (ProductValidationException $e) {
            $this->log($e);
            $this->error('An error occurred with a product, process has been stopped');
            $this->table(['Product id', 'errors'], $e->getDetails());

            return self::FAILURE;
        } catch (CategoryValidationException $e) {
            $this->log($e);
            $this->error('An error occurred with a category, process has been stopped');
            $this->table(['Category name', 'errors'], $e->getDetails());

            return self::FAILURE;
        } catch (\JsonException $e) {
            $this->log($e);
            $this->error('An error occurred with a product, process has been stopped');
            $this->warn(sprintf('Details : %s', $e->getMessage()));

            return self::FAILURE;
        } catch (ProductImporterException $e) {
            $this->log($e);
            $this->error($e->getMessage());
            $this->warn(sprintf('Details : %s', json_encode($e->getDetails())));

            return self::FAILURE;
        }

        $this->info('Process terminated with success');

        return self::SUCCESS;
    }

    private function setSource(): void
    {
        $sourceName = $this->argument('source');

        if (!isset($this->sources[$sourceName])) {
            throw new \InvalidArgumentException(sprintf(
                'The source must be in the following : %s',
                implode(', ', array_keys($this->sources))
            ));
        }

        $this->source = $this->sources[$sourceName];
    }

    private function log(\Exception $e): void
    {
        Log::error($e->getMessage(), [
            'context' => 'IMPORT',
            'domain' => 'PRODUCT',
            'exception' => $e,
        ]);
    }
}
