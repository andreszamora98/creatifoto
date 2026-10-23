<?php

namespace App\Services;

use App\Models\AdditionalService;
use App\Models\Package;
use App\Models\PhotoType;
use Illuminate\Support\Facades\DB;

class PackageService
{
    public function __construct(protected AutomaticDiscountService $autoDiscount)
    {
    }

    public function createPackage(array $data): Package
    {
        return DB::transaction(function () use ($data) {
            $listPrice = $this->resolveListPrice($data);
            $discountSource = $data['discount_source'] ?? null;

            $package = Package::create([
                'school_id' => $data['school_id'],
                'sequence' => $data['sequence'],
                'name' => trim($data['name']),
                'list_price' => $listPrice,
                'total_price' => $listPrice ?? 0,
                'discount_amount' => 0,
                'discount_type' => null,
                'discount_value' => null,
                'discount_source' => $discountSource ?? 'none',
                'discount_reason' => null,
                'auto_discount_reason' => null,
                'women_features' => $this->buildFeatures($data['women_features_text'] ?? null),
                'men_features' => $this->buildFeatures($data['men_features_text'] ?? null),
                'package_notes' => $this->normalizeNotes($data['package_notes'] ?? null),
            ]);

            $this->syncPhotoTypes($package, $data['photo_types'] ?? []);
            $this->syncServices($package, $data['additional_services'] ?? []);

            if ($discountSource === null) {
                $totalPrice = round((float) ($data['total_price'] ?? $listPrice ?? 0), 2);
                $package->forceFill([
                    'total_price' => $totalPrice,
                    'discount_amount' => $this->resolveDiscountAmount($listPrice, $totalPrice),
                    'discount_source' => ($listPrice !== null && $listPrice > $totalPrice) ? 'manual' : 'none',
                ])->save();
            } else {
                $package->load('photoTypes');
                $this->applyDiscount($package, [
                    'source' => $discountSource,
                    'type' => $data['discount_type'] ?? null,
                    'value' => $data['discount_value'] ?? null,
                    'reason' => $data['discount_reason'] ?? null,
                ]);
            }

            return $package->fresh(['school:id,name,sequence', 'photoTypes', 'additionalServices']);
        });
    }

    public function updatePackage(Package $package, array $data): Package
    {
        return DB::transaction(function () use ($package, $data) {
            $listPrice = $this->resolveListPrice($data);
            $discountSource = $data['discount_source'] ?? null;

            $package->update([
                'name' => trim($data['name']),
                'list_price' => $listPrice,
                'women_features' => $this->buildFeatures($data['women_features_text'] ?? null),
                'men_features' => $this->buildFeatures($data['men_features_text'] ?? null),
                'package_notes' => $this->normalizeNotes($data['package_notes'] ?? null),
            ]);

            $this->syncPhotoTypes($package, $data['photo_types'] ?? []);
            $this->syncServices($package, $data['additional_services'] ?? []);

            if ($discountSource === null) {
                $totalPrice = round((float) ($data['total_price'] ?? $listPrice ?? 0), 2);
                $package->forceFill([
                    'total_price' => $totalPrice,
                    'discount_amount' => $this->resolveDiscountAmount($listPrice, $totalPrice),
                    'discount_source' => ($listPrice !== null && $listPrice > $totalPrice) ? 'manual' : 'none',
                ])->save();
            } else {
                $package->load('photoTypes');
                $this->applyDiscount($package, [
                    'source' => $discountSource,
                    'type' => $data['discount_type'] ?? null,
                    'value' => $data['discount_value'] ?? null,
                    'reason' => $data['discount_reason'] ?? null,
                ]);
            }

            return $package->fresh(['school:id,name,sequence', 'photoTypes', 'additionalServices']);
        });
    }

    public function deletePackage(Package $package): void
    {
        $package->delete();
    }

    protected function applyDiscount(Package $package, array $config): void
    {
        $listPrice = (float) ($package->list_price ?? 0);
        $source = $config['source'] ?? 'none';
        $type = $config['type'] ?? null;
        $value = $config['value'] ?? null;
        $reason = $config['reason'] ?? null;

        if (in_array($type, ['amount', 'fixed'], true)) {
            $type = 'fixed';
        }
        if ($type === 'percent' && $value !== null) {
            $value = max(0.0, min(100.0, (float) $value));
        }
        if ($type === 'fixed' && $value !== null) {
            $value = max(0.0, (float) $value);
        }

        $discountAmount = 0.0;
        $autoReason = null;

        if ($source === 'auto') {
            $eval = $this->autoDiscount->evaluatePackage($package);
            if ($eval['eligible']) {
                $type = 'percent';
                $value = (float) config('creatifoto.auto_discount_percent', 10);
                $discountAmount = round(min($listPrice, $listPrice * $value / 100), 2);
                $autoReason = $eval['reason'];
            } else {
                $type = null;
                $value = null;
                $discountAmount = 0;
            }
        } elseif ($source === 'manual') {
            if ($type === 'percent' && $value !== null) {
                $discountAmount = round(min($listPrice, $listPrice * $value / 100), 2);
            } elseif ($type === 'fixed' && $value !== null) {
                $discountAmount = round(min($value, $listPrice), 2);
            }
        } else {
            $source = 'none';
            $type = null;
            $value = null;
            $discountAmount = 0;
            $reason = null;
        }

        $package->forceFill([
            'total_price' => max(0, round($listPrice - $discountAmount, 2)),
            'discount_amount' => $discountAmount,
            'discount_type' => $type,
            'discount_value' => $value,
            'discount_source' => $source,
            'discount_reason' => $source === 'manual' ? $reason : null,
            'auto_discount_reason' => $autoReason,
        ])->save();
    }

    protected function resolveListPrice(array $data): ?float
    {
        $raw = $data['list_price'] ?? null;
        if ($raw === null || $raw === '') {
            $raw = $data['total_price'] ?? null;
        }
        if ($raw === null || $raw === '') {
            return null;
        }
        return round((float) $raw, 2);
    }

    protected function resolveDiscountAmount(?float $listPrice, float $totalPrice): float
    {
        if ($listPrice === null || $listPrice <= $totalPrice) {
            return 0.0;
        }
        return round($listPrice - $totalPrice, 2);
    }

    protected function buildFeatures(?string $text): ?array
    {
        $trimmed = trim((string) $text);
        return $trimmed === '' ? null : ['text' => $trimmed];
    }

    protected function normalizeNotes(?string $value): ?string
    {
        $trimmed = $value !== null ? trim($value) : '';
        return $trimmed === '' ? null : $trimmed;
    }

    protected function syncPhotoTypes(Package $package, array $photoTypes): void
    {
        $package->photoTypes()->detach();

        foreach ($photoTypes as $line) {
            $photoType = PhotoType::withTrashed()->find($line['photo_type_id'])
                ?? throw new \DomainException('Uno de los tipos de fotografía seleccionados ya no está disponible.');

            $package->photoTypes()->attach($line['photo_type_id'], [
                'quantity' => $line['quantity'],
                'format' => $line['format'] ?? 'color',
                'background' => $this->normalizeBackground($line['background'] ?? null),
                'paper_type_id' => $line['paper_type_id'] ?? null,
                'unit_price' => (float) ($photoType->base_price ?? 0),
            ]);
        }
    }

    protected function syncServices(Package $package, array $services): void
    {
        $syncData = [];

        foreach ($services as $line) {
            $svc = AdditionalService::withTrashed()->find($line['additional_service_id'])
                ?? throw new \DomainException('Uno de los servicios adicionales seleccionados ya no está disponible.');

            $syncData[$line['additional_service_id']] = [
                'quantity' => $line['quantity'] ?? 1,
                'unit_price' => (float) ($svc->default_price ?? 0),
            ];
        }

        $package->additionalServices()->sync($syncData);
    }

    protected function normalizeBackground(?string $value): ?string
    {
        $trimmed = $value !== null ? trim($value) : '';
        return $trimmed === '' ? null : $trimmed;
    }
}