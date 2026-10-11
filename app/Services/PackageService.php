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
            $package = Package::create([
                'school_id' => $data['school_id'],
                'sequence' => $data['sequence'],
                'name' => trim($data['name']),
                'total_price' => $data['total_price'],
                'women_features' => $this->buildFeatures($data['women_features_text'] ?? null),
                'men_features' => $this->buildFeatures($data['men_features_text'] ?? null),
            ]);

            $this->syncPhotoTypes($package, $data['photo_types'] ?? []);
            $this->syncServices($package, $data['additional_services'] ?? []);

            return $package->fresh(['school:id,name,sequence', 'photoTypes', 'additionalServices']);
        });
    }

    public function updatePackage(Package $package, array $data): Package
    {
        return DB::transaction(function () use ($package, $data) {
            $package->update([
                'name' => trim($data['name']),
                'total_price' => $data['total_price'],
                'women_features' => $this->buildFeatures($data['women_features_text'] ?? null),
                'men_features' => $this->buildFeatures($data['men_features_text'] ?? null),
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

    protected function buildFeatures(?string $text): ?array
    {
        $trimmed = trim((string) $text);
        return $trimmed === '' ? null : ['text' => $trimmed];
    }

    protected function syncPhotoTypes(Package $package, array $photoTypes): void
    {
        $package->photoTypes()->detach();

        foreach ($photoTypes as $line) {
            $photoType = PhotoType::find($line['photo_type_id']);
            $package->photoTypes()->attach($line['photo_type_id'], [
                'quantity' => $line['quantity'],
                'format' => $line['format'] ?? 'color',
                'paper_type_id' => $line['paper_type_id'] ?? null,
                'unit_price' => (float) ($photoType?->base_price ?? 0),
            ]);
        }
    }

    protected function syncServices(Package $package, array $services): void
    {
        $syncData = [];
        foreach ($services as $line) {
            $svc = AdditionalService::find($line['additional_service_id']);
            $syncData[$line['additional_service_id']] = [
                'quantity' => $line['quantity'] ?? 1,
                'unit_price' => (float) ($svc?->default_price ?? 0),
            ];
        }
        $package->additionalServices()->sync($syncData);
    }
}