<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Support\Facades\DB;

class PackageService
{
    public function createPackage(array $data): Package
    {
        return DB::transaction(function () use ($data) {
            $package = Package::create([
                'school_id' => $data['school_id'],
                'sequence' => $data['sequence'],
                'name' => $data['name'] ?? null,
                'total_price' => $data['total_price'],
                'women_features' => $this->buildFeatures($data['women_features_text'] ?? null),
                'men_features' => $this->buildFeatures($data['men_features_text'] ?? null),
            ]);

            $this->syncPhotoTypes($package, $data['photo_types'] ?? []);

            return $package->fresh(['school:id,name,sequence', 'photoTypes']);
        });
    }

    public function updatePackage(Package $package, array $data): Package
    {
        return DB::transaction(function () use ($package, $data) {
            $package->update([
                'name' => $data['name'] ?? null,
                'total_price' => $data['total_price'],
                'women_features' => $this->buildFeatures($data['women_features_text'] ?? null),
                'men_features' => $this->buildFeatures($data['men_features_text'] ?? null),
            ]);

            $this->syncPhotoTypes($package, $data['photo_types'] ?? []);

            return $package->fresh(['school:id,name,sequence', 'photoTypes']);
        });
    }

    public function deletePackage(Package $package): void
    {
        if ($package->orders()->exists()) {
            throw new \Exception('No se puede eliminar un paquete que ya ha sido vendido en alguna orden. Puedes desactivarlo en su lugar.');
        }

        $package->delete();
    }

    protected function buildFeatures(?string $text): ?array
    {
        $trimmed = trim((string) $text);
        return $trimmed === '' ? null : ['text' => $trimmed];
    }

    protected function syncPhotoTypes(Package $package, array $photoTypes): void
    {
        $syncData = [];
        foreach ($photoTypes as $line) {
            $syncData[$line['photo_type_id']] = ['quantity' => $line['quantity']];
        }
        $package->photoTypes()->sync($syncData);
    }
}