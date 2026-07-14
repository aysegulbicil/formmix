<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

class SalesDocumentCalculator
{
    public function calculateLine(float $quantity, float $unitPrice, float $discountPercent, float $taxRate): array
    {
        if ($quantity <= 0 || $unitPrice <= 0) throw new InvalidArgumentException('Miktar ve birim fiyat sıfırdan büyük olmalıdır.');
        if ($discountPercent < 0 || $discountPercent > 100 || $taxRate < 0 || $taxRate > 100) throw new InvalidArgumentException('İndirim ve vergi oranı 0 ile 100 arasında olmalıdır.');
        $gross = $this->money($quantity * $unitPrice);
        $discount = $this->money($gross * $discountPercent / 100);
        $net = $this->money($gross - $discount);
        $tax = $this->money($net * $taxRate / 100);
        return ['gross_amount'=>$gross,'discount_amount'=>$discount,'net_amount'=>$net,'tax_amount'=>$tax,'line_total'=>$this->money($net+$tax)];
    }

    public function calculateDocument(array $lines): array
    {
        if ($lines === []) throw new InvalidArgumentException('Belgede en az bir ürün bulunmalıdır.');
        $subtotal=$discount=$tax=$grand=0.0;
        foreach ($lines as $line) { $subtotal += (float)$line['gross_amount']; $discount += (float)$line['discount_amount']; $tax += (float)$line['tax_amount']; $grand += (float)$line['line_total']; }
        return ['subtotal'=>$this->money($subtotal),'discount_total'=>$this->money($discount),'tax_total'=>$this->money($tax),'grand_total'=>$this->money($grand)];
    }

    private function money(float $value): float { return round($value + 0.00000001, 2, PHP_ROUND_HALF_UP); }
}
