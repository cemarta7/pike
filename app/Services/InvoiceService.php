<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceService
{
    public function __construct(
        private InvoiceSettingsService $settings,
    ) {}

    /**
     * Generate a PDF invoice from the provided data.
     *
     * @param  array{
     *     invoice_number: string,
     *     from_address?: string,
     *     bill_to_address: string,
     *     ship_to_address?: string,
     *     purchase_order?: string,
     *     payment_terms?: string,
     *     notes?: string,
     *     terms?: string,
     *     line_items: array<int, array{
     *         description?: string,
     *         quantity?: int,
     *         rate: float
     *     }>,
     *     tax_percent?: float,
     *     discount?: float,
     *     shipping?: float,
     *     amount_paid?: float,
     *     logo_base64?: string
     * }  $data
     */
    public function generate(array $data): string
    {
        $defaults = $this->settings->all();

        $lineItems = $this->normalizeLineItems($data['line_items']);
        $subtotal = $this->calculateSubtotal($lineItems);

        $taxPercent = (float) ($data['tax_percent'] ?? $defaults['tax_percent'] ?? 0);
        $taxAmount = $subtotal * ($taxPercent / 100);
        $discount = (float) ($data['discount'] ?? 0);
        $shipping = (float) ($data['shipping'] ?? 0);
        $amountPaid = (float) ($data['amount_paid'] ?? 0);

        $total = $subtotal + $taxAmount - $discount + $shipping;
        $balanceDue = $total - $amountPaid;

        // Resolve logo: use provided base64, or fetch from settings
        $logoBase64 = $data['logo_base64'] ?? null;
        if (empty($logoBase64)) {
            $logoBase64 = $this->settings->getLogoBase64();
        }

        $viewData = [
            'invoice_number' => $data['invoice_number'],
            'from_address' => $this->normalizeNewlines($data['from_address'] ?? $defaults['from_address'] ?? ''),
            'bill_to_address' => $this->normalizeNewlines($data['bill_to_address']),
            'ship_to_address' => $this->normalizeNewlines($data['ship_to_address'] ?? ''),
            'purchase_order' => $data['purchase_order'] ?? '',
            'payment_terms' => $data['payment_terms'] ?? $defaults['payment_terms'] ?? '',
            'notes' => $this->normalizeNewlines($data['notes'] ?? $defaults['notes'] ?? ''),
            'terms' => $this->normalizeNewlines($data['terms'] ?? $defaults['terms'] ?? ''),
            'line_items' => $lineItems,
            'logo_base64' => $logoBase64,
            'generated_date' => Carbon::now()->format('M d, Y'),
            'due_date' => Carbon::now()->addDays(30)->format('M d, Y'),
            'subtotal' => $subtotal,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
            'amount_paid' => $amountPaid,
            'balance_due' => $balanceDue,
        ];

        $pdf = Pdf::loadView('invoicePdf', $viewData);

        return $pdf->output();
    }

    /**
     * Normalize line items to ensure all required fields have defaults.
     *
     * @param  array<int, array<string, mixed>>  $lineItems
     * @return array<int, array{description: string, quantity: int, rate: float}>
     */
    private function normalizeLineItems(array $lineItems): array
    {
        return array_map(function (array $item): array {
            return [
                'description' => $item['description'] ?? '',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'rate' => (float) ($item['rate'] ?? 0),
            ];
        }, $lineItems);
    }

    /**
     * Calculate the subtotal from line items.
     *
     * @param  array<int, array{quantity: int, rate: float}>  $lineItems
     */
    private function calculateSubtotal(array $lineItems): float
    {
        return array_reduce($lineItems, function (float $carry, array $item): float {
            return $carry + ($item['quantity'] * $item['rate']);
        }, 0.0);
    }

    /**
     * Convert literal \n strings to actual newlines.
     */
    private function normalizeNewlines(string $text): string
    {
        return str_replace('\n', "\n", $text);
    }
}
