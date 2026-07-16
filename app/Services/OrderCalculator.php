<?php

namespace App\Services;

class OrderCalculator
{
    /**
     * @param  array<int, array{qty:float|string, unit_price:float|string, discount:float|string, tax_rate:float|string}>  $lines
     * @param  string  $discountType  none|percent|amount
     * @param  float|string  $discountValue
     * @return array{subtotal:float, discount_amount:float, tax_total:float, grand_total:float}
     */
    public function calculate(array $lines, string $discountType = 'none', $discountValue = 0): array
    {
        $subtotal = 0.0;
        $lineTotals = [];

        foreach ($lines as $line) {
            $qty = (float) ($line['qty'] ?? 0);
            $price = (float) ($line['unit_price'] ?? 0);
            $discount = (float) ($line['discount'] ?? 0);
            $lineTotal = max(0.0, $qty * $price - $discount);
            $lineTotals[] = $lineTotal;
            $subtotal += $lineTotal;
        }

        $discountAmount = 0.0;
        if ($discountType === 'percent') {
            $discountAmount = $subtotal * ((float) $discountValue / 100);
        } elseif ($discountType === 'amount') {
            $discountAmount = min((float) $discountValue, $subtotal);
        }
        $discountAmount = round($discountAmount, 2);

        $discountFraction = $subtotal > 0 ? ($discountAmount / $subtotal) : 0.0;

        $taxTotal = 0.0;
        foreach ($lines as $i => $line) {
            $lineTaxable = $lineTotals[$i] * (1 - $discountFraction);
            $taxRate = (float) ($line['tax_rate'] ?? 0);
            $taxTotal += $lineTaxable * ($taxRate / 100);
        }

        $grandTotal = ($subtotal - $discountAmount) + $taxTotal;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => $discountAmount,
            'tax_total' => round($taxTotal, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }
}
