<?php

namespace App\Services;

class OrderCalculator
{
    /**
     * @param  array<int, array{qty:float|string, unit_price:float|string, discount:float|string, tax_rate:float|string, is_tax_inclusive?:bool}>  $lines
     * @param  string  $discountType  none|percent|amount
     * @param  float|string  $discountValue
     * @return array{subtotal:float, discount_amount:float, tax_total:float, grand_total:float}
     */
    public function calculate(array $lines, string $discountType = 'none', $discountValue = 0): array
    {
        $subtotal = 0.0;
        $lineTotals = [];
        $lineTaxAmounts = [];

        foreach ($lines as $line) {
            $qty = (float) ($line['qty'] ?? 0);
            $price = (float) ($line['unit_price'] ?? 0);
            $discount = (float) ($line['discount'] ?? 0);
            $taxRate = (float) ($line['tax_rate'] ?? 0);
            $isTaxInclusive = ! empty($line['is_tax_inclusive']);

            $lineTotal = max(0.0, $qty * $price - $discount);
            $lineTotals[] = $lineTotal;

            if ($isTaxInclusive && $taxRate > 0) {
                // Price already includes tax — extract base and tax portions
                $baseAmount = $lineTotal / (1 + $taxRate / 100);
                $lineTax = $lineTotal - $baseAmount;
                $lineTaxAmounts[] = $lineTax;
                $subtotal += $baseAmount;
            } else {
                // Tax-exclusive: subtotal includes full line total, tax calculated on top
                $lineTaxAmounts[] = null; // placeholder, calculated later with discount
                $subtotal += $lineTotal;
            }
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
            if ($lineTaxAmounts[$i] !== null) {
                // Tax-inclusive line — tax already calculated, apply discount proportionally
                $taxTotal += $lineTaxAmounts[$i] * (1 - $discountFraction);
            } else {
                // Tax-exclusive line — calculate tax after discount
                $lineTaxable = $lineTotals[$i] * (1 - $discountFraction);
                $taxRate = (float) ($line['tax_rate'] ?? 0);
                $taxTotal += $lineTaxable * ($taxRate / 100);
            }
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
