<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Generate a PDF invoice from the provided data.
     *
     * @param  array{
     *     invoice_number: string,
     *     purchase_order?: string,
     *     note_to_customer?: string,
     *     bill_to_address: string,
     *     technician_phone?: string,
     *     technician_email?: string,
     *     line_items: array<int, array{
     *         sku?: string,
     *         stock?: string,
     *         vin?: string,
     *         year_make_model?: string,
     *         quantity?: int,
     *         price: float
     *     }>,
     *     logo_base64?: string
     * }  $data
     */
    public function generate(array $data): string
    {
        $lineItems = $this->normalizeLineItems($data['line_items'] ?? []);
        $subtotal = $this->calculateSubtotal($lineItems);
        $total = $subtotal;

        $viewData = [
            'invoice_number' => $data['invoice_number'],
            'purchase_order' => $data['purchase_order'] ?? '',
            'note_to_customer' => $data['note_to_customer'] ?? '',
            'bill_to_address' => $data['bill_to_address'],
            'technician_phone' => $data['technician_phone'] ?? '',
            'technician_email' => $data['technician_email'] ?? '',
            'line_items' => $lineItems,
            'logo_base64' => $data['logo_base64'] ?? null,
            'generated_date' => Carbon::now()->format('m/d/Y'),
            'due_date' => Carbon::now()->addDays(30)->format('m/d/Y'),
            'subtotal' => $subtotal,
            'total' => $total,
        ];

        $pdf = Pdf::loadView('invoicePdf', $viewData);

        return $pdf->output();
    }

    /**
     * Normalize line items to ensure all required fields have defaults.
     *
     * @param  array<int, array<string, mixed>>  $lineItems
     * @return array<int, array{sku: string, stock: string, vin: string, year_make_model: string, quantity: int, price: float}>
     */
    private function normalizeLineItems(array $lineItems): array
    {
        return array_map(function (array $item): array {
            return [
                'sku' => $item['sku'] ?? '',
                'stock' => $item['stock'] ?? '',
                'vin' => $item['vin'] ?? '',
                'year_make_model' => $item['year_make_model'] ?? '',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'price' => (float) ($item['price'] ?? 0),
            ];
        }, $lineItems);
    }

    /**
     * Calculate the subtotal from line items.
     *
     * @param  array<int, array{quantity: int, price: float}>  $lineItems
     */
    private function calculateSubtotal(array $lineItems): float
    {
        return array_reduce($lineItems, function (float $carry, array $item): float {
            return $carry + ($item['quantity'] * $item['price']);
        }, 0.0);
    }
}
