<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Package;
use App\Models\PhotoType;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Model;

class AutomaticDiscountService
{
    public function evaluate(Model $doc): array
    {
        $r = $doc instanceof Order
            ? ['lines' => 'orderPhotoTypes', 'fk' => 'order_package_id']
            : ['lines' => 'quotePhotoTypes', 'fk' => 'quote_package_id'];

        $lines = $doc->{$r['lines']}()
            ->whereNull($r['fk'])
            ->get(['photo_type_id', 'quantity']);

        if ($lines->isEmpty()) {
            return ['eligible' => false, 'reason' => null];
        }

        $grouped = [];
        foreach ($lines as $line) {
            $id = (int) $line->photo_type_id;
            $grouped[$id] = ($grouped[$id] ?? 0) + (int) $line->quantity;
        }

        $types = PhotoType::withTrashed()
            ->whereIn('id', array_keys($grouped))
            ->get()
            ->keyBy('id');

        $matchedA = null;
        $completeTypes = 0;

        foreach ($grouped as $typeId => $totalQty) {
            $type = $types->get($typeId);
            if (!$type) {
                continue;
            }

            $base = max(1, (int) ($type->base_quantity ?? 1));

            if ($matchedA === null && $totalQty >= 2 * $base) {
                $matchedA = $type;
            }
            if ($totalQty >= $base) {
                $completeTypes++;
            }
        }

        if ($matchedA !== null) {
            return [
                'eligible' => true,
                'reason' => '2+ órdenes completas de "' . $matchedA->name . '"',
            ];
        }

        if ($completeTypes >= 3) {
            return [
                'eligible' => true,
                'reason' => $completeTypes . ' tipos de fotografía con cantidad completa',
            ];
        }

        return ['eligible' => false, 'reason' => null];
    }

    public function evaluatePackage(Package $package): array
    {
        $photoTypes = $package->relationLoaded('photoTypes')
            ? $package->photoTypes
            : $package->photoTypes()->get();

        if ($photoTypes->isEmpty()) {
            return ['eligible' => false, 'reason' => null];
        }

        $grouped = [];
        foreach ($photoTypes as $pt) {
            $id = (int) $pt->id;
            $qty = (int) ($pt->pivot->quantity ?? 0);
            $grouped[$id] = ($grouped[$id] ?? 0) + $qty;
        }

        $matchedA = null;
        $completeTypes = 0;

        foreach ($grouped as $typeId => $totalQty) {
            $type = $photoTypes->first(fn ($t) => (int) $t->id === $typeId);
            if (!$type) {
                continue;
            }

            $base = max(1, (int) ($type->base_quantity ?? 1));

            if ($matchedA === null && $totalQty >= 2 * $base) {
                $matchedA = $type;
            }
            if ($totalQty >= $base) {
                $completeTypes++;
            }
        }

        if ($matchedA !== null) {
            return [
                'eligible' => true,
                'reason' => '2+ órdenes completas de "' . $matchedA->name . '"',
            ];
        }

        if ($completeTypes >= 3) {
            return [
                'eligible' => true,
                'reason' => $completeTypes . ' tipos de fotografía con cantidad completa',
            ];
        }

        return ['eligible' => false, 'reason' => null];
    }
}