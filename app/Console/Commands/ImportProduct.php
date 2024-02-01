<?php

namespace App\Console\Commands;

use App\DTO\Exceptions\AbstractValidationException;
use App\Importer\Exception\ProductImporterException;
use App\Importer\ProductImporterInterface;
use App\Models\Repository\CategoryRepository;
use App\Models\Repository\ProductRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportProduct extends Command
{
    protected $signature = 'product:import
                            {source : The source of the products to import}';
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
            return $this->logAndFail(
                e: $e,
                message: $e->getMessage(),
                exitCode: self::INVALID
            );
        } catch (AbstractValidationException $e) {
            return $this->logAndFail(
                $e,
                'An error occurred, process has been stopped',
                [
                    'header' => [$e->getIdentifierName(), 'errors'],
                    'rows' => $e->getDetails(),
                ]
            );
        } catch (ProductImporterException|\JsonException $e) {
            return $this->logAndFail(
                e: $e,
                message: 'An error occurred with a product, process has been stopped',
                details: $e->getMessage(),
            );
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

    private function logAndFail(
        \Exception $e,
        string $message,
        array $table = [],
        ?string $details = null,
        int $exitCode = self::FAILURE
    ): int {
        $this->log($e);
        $this->error($message);

        if (!empty($table)) {
            $this->table($table['header'], $table['rows']);
        }

        if (!empty($details)) {
            $this->warn(sprintf('Details : %s', $details));
        }

        return $exitCode;
    }
}
