<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $variablePath = base_path('../dataa/products/variable.csv');
        $variationPath = base_path('../dataa/products/variation.csv');

        if (! file_exists($variablePath)) {
            throw new RuntimeException("Product CSV not found at {$variablePath}");
        }

        if (! file_exists($variationPath)) {
            throw new RuntimeException("Variation CSV not found at {$variationPath}");
        }

        $variationGroups = $this->loadVariationsGroupedByParent($variationPath);

        $handle = fopen($variablePath, 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            if (! $data || empty($data['SKU'])) {
                continue;
            }

            $parentSku = (string) $data['SKU'];
            $categoryName = $this->parentCategoryName($data['Categories'] ?? '');
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName]
            );

            $images = $this->csvList($data['Images'] ?? '');
            $variationRows = $variationGroups[$parentSku] ?? [];
            $sizes = $this->sizesFromVariationsOrVariableRow($variationRows, (string) ($data['Attribute 2 value(s)'] ?? ''));
            $stock = $this->sumStockFromVariationsOrDefault($variationRows, 100);
            [$regularPrice, $salePrice] = $this->priceFromVariationsOrDefault($variationRows, 0.0, null);

            $product = Product::updateOrCreate(
                ['sku' => $parentSku],
                [
                    'category_id' => $category->id,
                    'name' => $data['Name'],
                    'slug' => $this->uniqueSlug($data['Name'], $parentSku),
                    'description' => $data['Description'] ?? null,
                    'price' => $regularPrice,
                    'sale_price' => $salePrice,
                    'stock_quantity' => $stock,
                    'status' => 'active',
                    'color' => $data['Attribute 1 value(s)'] ?? null,
                    'sizes' => $sizes,
                    'style' => $data['Attribute 3 value(s)'] ?? null,
                    'featured_image' => $images[0] ?? null,
                    'images' => $images,
                ]
            );

            foreach ($variationRows as $v) {
                ProductVariation::updateOrCreate(
                    ['sku' => $v['SKU']],
                    [
                        'product_id' => $product->id,
                        'name' => $v['Name'] ?: null,
                        'stock' => $v['Stock'],
                        'regular_price' => $v['Regular price'],
                        'sale_price' => $v['Sale price'] ?: null,
                        'attribute_name' => $v['Attribute 1 name'] ?: null,
                        'attribute_value' => $v['Attribute 1 value(s)'] ?: null,
                    ]
                );
            }
        }

        fclose($handle);
    }

    private function loadVariationsGroupedByParent(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        $groups = [];
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            if (! $data) {
                continue;
            }

            $parent = trim((string) ($data['Parent'] ?? ''));
            $sku = trim((string) ($data['SKU'] ?? ''));
            if ($parent === '' || $sku === '') {
                continue;
            }

            $groups[$parent][] = [
                'SKU' => $sku,
                'Name' => trim((string) ($data['Name'] ?? '')),
                'Stock' => (int) ($data['Stock'] ?? 0),
                'Sale price' => $this->toFloatOrNull($data['Sale price'] ?? null),
                'Regular price' => $this->toFloatOrNull($data['Regular price'] ?? null) ?? 0.0,
                'Attribute 1 name' => trim((string) ($data['Attribute 1 name'] ?? '')),
                'Attribute 1 value(s)' => trim((string) ($data['Attribute 1 value(s)'] ?? '')),
            ];
        }

        fclose($handle);

        return $groups;
    }

    private function sizesFromVariationsOrVariableRow(array $variationRows, string $rawFromVariableRow): array
    {
        $sizes = [];

        foreach ($variationRows as $v) {
            if (($v['Attribute 1 name'] ?? '') === 'Size' && ! empty($v['Attribute 1 value(s)'])) {
                $sizes[] = (string) $v['Attribute 1 value(s)'];
            }
        }

        $sizes = array_values(array_unique(array_filter(array_map('trim', $sizes))));

        return $sizes ?: $this->csvList($rawFromVariableRow);
    }

    private function sumStockFromVariationsOrDefault(array $variationRows, int $default): int
    {
        if (! $variationRows) {
            return $default;
        }

        return array_sum(array_map(fn ($v) => (int) ($v['Stock'] ?? 0), $variationRows));
    }

    private function priceFromVariationsOrDefault(array $variationRows, float $defaultRegular, ?float $defaultSale): array
    {
        if (! $variationRows) {
            return [$defaultRegular, $defaultSale];
        }

        $regularPrices = array_values(array_filter(array_map(fn ($v) => (float) ($v['Regular price'] ?? 0), $variationRows)));
        $salePrices = array_values(array_filter(array_map(fn ($v) => $v['Sale price'] !== null ? (float) $v['Sale price'] : null, $variationRows), fn ($v) => $v !== null && $v > 0));

        $regular = $regularPrices ? min($regularPrices) : $defaultRegular;
        $sale = $salePrices ? min($salePrices) : $defaultSale;

        return [$regular, $sale];
    }

    private function parentCategoryName(string $raw): string
    {
        $first = trim(explode(',', $raw)[0] ?? 'Uncategorized');

        return trim(explode('>', $first)[0] ?? 'Uncategorized') ?: 'Uncategorized';
    }

    private function csvList(string $raw): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    private function uniqueSlug(string $name, string $sku): string
    {
        return Str::slug($name).'-'.Str::slug($sku);
    }

    private function toFloatOrNull($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return (float) $value;
    }
}
