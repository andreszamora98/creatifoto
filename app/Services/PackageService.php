<?php

namespace App\Services;

use App\Models\AdditionalService;
use App\Models\Package;
use App\Models\PhotoType;
use Illuminate\Support\Facades\DB;

class PackageService
{
    public function createPackage(array $data): Package
    {
        return DB::transaction(function () use ($data) {
            $listPrice = $this->resolveListPrice($data);
            $totalPrice = round((float) $data['total_price'], 2);

            $package = Package::create([
                'school_id' => $data['school_id'],
                'sequence' => $data['sequence'],
                'name' => trim($data['name']),
                'total_price' => $totalPrice,
                'list_price' => $listPrice,
                'discount_amount' => $this->resolveDiscountAmount($listPrice, $totalPrice),
                'women_features' => $this->buildFeatures($data['women_features_text'] ?? null),
                'men_features' => $this->buildFeatures($data['men_features_text'] ?? null),
                'package_notes' => $this->normalizeNotes($data['package_notes'] ?? null),
            ]);

            $this->syncPhotoTypes($package, $data['photo_types'] ?? []);
            $this->syncServices($package, $data['additional_services'] ?? []);

            return $package->fresh(['school:id,name,sequence', 'photoTypes', 'additionalServices']);
        });
    }

    public function updatePackage(Package $package, array $data): Package
    {
        return DB::transaction(function () use ($package, $data) {
            $listPrice = $this->resolveListPrice($data);
            $totalPrice = round((float) $data['total_price'], 2);

            $package->update([
                'name' => trim($data['name']),
                'total_price' => $totalPrice,
                'list_price' => $listPrice,
                'discount_amount' => $this->resolveDiscountAmount($listPrice, $totalPrice),
                'women_features' => $this->buildFeatures($data['women_features_text'] ?? null),
                'men_features' => $this->buildFeatures($data['men_features_text'] ?? null),
                'package_notes' => $this->normalizeNotes($data['package_notes'] ?? null),
            ]);

            $this->syncPhotoTypes($package, $data['photo_types'] ?? []);
            $this->syncServices($package, $data['additional_services'] ?? []);

            return $package->fresh(['school:id,name,sequence', 'photoTypes', 'additionalServices']);
        });
    }

    public function deletePackage(Package $package): void
    {
        $package->delete();
    }

    protected function resolveListPrice(array $data): ?float
    {
        $raw = $data['list_price'] ?? null;

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

    protected function normalizeBackground(?string $value): string
    {
        $trimmed = $value !== null ? trim($value) : '';

        return $trimmed === '' ? 'blanco' : $trimmed;
    }
}