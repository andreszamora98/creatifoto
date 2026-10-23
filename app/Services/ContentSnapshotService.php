<?php

namespace App\Services;

use App\Models\AdditionalService;
use App\Models\Order;
use App\Models\Package;
use App\Models\PaperType;
use App\Models\PhotoType;
use App\Models\Quote;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ContentSnapshotService
{
    private function r(Model $doc): array
    {
        return $doc instanceof Order
            ? ['packages' => 'orderPackages', 'lines' => 'orderPhotoTypes', 'fk' => 'order_package_id', 'serviceFk' => 'order_package_id']
            : ['packages' => 'quotePackages', 'lines' => 'quotePhotoTypes', 'fk' => 'quote_package_id', 'serviceFk' => 'quote_package_id'];
    }

    public function createFor(Model $doc, array $data): void
    {
        $this->loadPackages($data['package_ids'] ?? [])->each(fn (Package $p) => $this->addPackage($doc, $p));

        foreach ($data['photo_types'] ?? [] as $line) {
            $this->addLine($doc, $line);
        }

        foreach ($data['additional_services'] ?? [] as $svc) {
            $this->addService($doc, $svc);
        }
    }

    public function syncFor(Model $doc, array $data): void
    {
        $r = $this->r($doc);

        $wanted = array_values(array_unique($data['package_ids'] ?? []));
        $existing = $doc->{$r['packages']}()->get()->keyBy('package_id');
        $existing->filter(fn ($row, $pkgId) => !in_array($pkgId, $wanted))->each->delete();
        $new = array_values(array_diff($wanted, $existing->keys()->all()));
        $this->loadPackages($new)->each(fn (Package $p) => $this->addPackage($doc, $p));

        $current = $doc->{$r['lines']}()->get()->keyBy('id');
        $keep = [];
        foreach ($data['photo_types'] ?? [] as $in) {
            if (!empty($in['id'])) {
                $line = $current[$in['id']] ?? throw new DomainException(
                    'Una línea de la orden ya no existe. Recarga e inténtalo de nuevo.'
                );
                $this->updateLine($line, $in);
                $keep[] = $line->id;
            } else {
                $keep[] = $this->addLine($doc, $in)->id;
            }
        }
        $doc->{$r['lines']}()->whereNotIn('id', $keep)->delete();

        if (!array_key_exists('additional_services', $data)) {
            return;
        }

        $currentSvc = $doc->services()
            ->whereNull('order_package_id')
            ->whereNull('quote_package_id')
            ->get()
            ->keyBy('additional_service_id');
        $keepSvc = [];
        foreach ($data['additional_services'] ?? [] as $s) {
            if ($row = $currentSvc[$s['additional_service_id']] ?? null) {
                $row->update([
                    'price' => $s['price'],
                    'quantity' => $s['quantity'],
                    'notes' => $s['notes'] ?? null,
                ]);
                $keepSvc[] = $row->id;
            } else {
                $keepSvc[] = $this->addService($doc, $s)->id;
            }
        }
        $doc->services()
            ->whereNull('order_package_id')
            ->whereNull('quote_package_id')
            ->whereNotIn('id', $keepSvc)
            ->delete();
    }

    public function recalculate(Model $doc): float
    {
        $r = $this->r($doc);

        $packages = (float) $doc->{$r['packages']}()->sum('price_snapshot');

        $lines = (float) $doc->{$r['lines']}()
            ->whereNull($r['fk'])
            ->selectRaw('COALESCE(SUM(unit_price * quantity), 0) AS t')->value('t');

        $services = (float) $doc->services()
            ->whereNull('order_package_id')
            ->whereNull('quote_package_id')
            ->selectRaw('COALESCE(SUM(price * quantity), 0) AS t')->value('t');

        return round($packages + $lines + $services, 2);
    }

    public function recalculateWithDiscount(Model $doc, ?string $type, ?float $value, bool $applyToPackages = false): array
    {
        $total = $this->recalculate($doc);
        $r = $this->r($doc);

        $directLines = (float) $doc->{$r['lines']}()
            ->whereNull($r['fk'])
            ->selectRaw('COALESCE(SUM(unit_price * quantity), 0) AS t')->value('t');

        $directServices = (float) $doc->services()
            ->whereNull('order_package_id')
            ->whereNull('quote_package_id')
            ->selectRaw('COALESCE(SUM(price * quantity), 0) AS t')->value('t');

        $baseDirect = round($directLines + $directServices, 2);

        if ($applyToPackages) {
            $packagesTotal = (float) $doc->{$r['packages']}()->sum('price_snapshot');
            $baseDirect = round($baseDirect + $packagesTotal, 2);
        }

        $discountAmount = 0.0;

        if ($type === 'percent' && $value !== null) {
            $clamped = max(0.0, min(100.0, $value));
            $discountAmount = round($baseDirect * $clamped / 100, 2);
        } elseif ($type === 'fixed' && $value !== null) {
            $discountAmount = round(min(max(0.0, $value), $baseDirect), 2);
        }

        return [
            'total' => $total,
            'base_direct' => $baseDirect,
            'discount_amount' => $discountAmount,
            'net_total' => round($total - $discountAmount, 2),
        ];
    }

    public function copyQuoteToOrder(Quote $quote, Order $order): void
    {
        $map = [];
        foreach ($quote->quotePackages as $qp) {
            $map[$qp->id] = $order->orderPackages()->create($qp->only([
                'package_id', 'school_id', 'package_name_snapshot', 'school_name_snapshot',
                'price_snapshot', 'list_price_snapshot', 'discount_amount_snapshot',
                'package_notes_snapshot',
            ]))->id;
        }

        foreach ($quote->quotePhotoTypes as $ql) {
            $order->orderPhotoTypes()->create([
                ...$ql->only([
                    'photo_type_id', 'photo_type_name_snapshot', 'quantity', 'unit_price',
                    'format', 'background', 'paper_type_id', 'paper_type_name_snapshot',
                ]),
                'order_package_id' => $ql->quote_package_id ? $map[$ql->quote_package_id] : null,
            ]);
        }

        foreach ($quote->services as $s) {
            $order->services()->create([
                ...$s->only([
                    'additional_service_id', 'additional_service_name_snapshot', 'price', 'quantity', 'notes',
                ]),
                'order_package_id' => $s->quote_package_id ? ($map[$s->quote_package_id] ?? null) : null,
            ]);
        }
    }

    public function loadPackages(array $ids): Collection
    {
        $ids = array_values(array_unique($ids));

        if (!$ids) {
            return collect();
        }

        $packages = Package::with(['school', 'photoTypes', 'additionalServices'])
            ->whereIn('id', $ids)
            ->get();

        if ($packages->count() !== count($ids)) {
            throw new DomainException('Uno de los paquetes seleccionados ya no está disponible. Actualiza e inténtalo de nuevo.');
        }

        return $packages;
    }

    private function addPackage(Model $doc, Package $pkg): void
    {
        $r = $this->r($doc);

        $row = $doc->{$r['packages']}()->create([
            'package_id' => $pkg->id,
            'school_id' => $pkg->school_id,
            'package_name_snapshot' => $pkg->name ?: ('Paquete #' . $pkg->id),
            'school_name_snapshot' => $pkg->school->name,
            'price_snapshot' => $pkg->total_price,
            'list_price_snapshot' => $pkg->list_price,
            'discount_amount_snapshot' => $pkg->discount_amount ?? 0,
            'package_notes_snapshot' => $pkg->package_notes,
        ]);

        foreach ($pkg->photoTypes as $pt) {
            $paperId = $pt->pivot->paper_type_id;
            $paperName = $paperId
                ? PaperType::withTrashed()->find($paperId)?->name
                : null;

            $doc->{$r['lines']}()->create([
                'photo_type_id' => $pt->id,
                'photo_type_name_snapshot' => $pt->name,
                'quantity' => $pt->pivot->quantity,
                'unit_price' => (float) ($pt->pivot->unit_price ?? 0),
                'format' => $pt->pivot->format ?? 'color',
                'background' => $this->normalizeBackground($pt->pivot->background ?? null),
                'paper_type_id' => $paperId,
                'paper_type_name_snapshot' => $paperName,
                $r['fk'] => $row->id,
            ]);
        }

        foreach ($pkg->additionalServices as $svc) {
            $doc->services()->create([
                'additional_service_id' => $svc->id,
                'additional_service_name_snapshot' => $svc->name,
                'price' => (float) ($svc->pivot->unit_price ?? 0),
                'quantity' => $svc->pivot->quantity,
                'notes' => null,
                $r['serviceFk'] => $row->id,
            ]);
        }
    }

    private function addLine(Model $doc, array $in): Model
    {
        $pt = PhotoType::find($in['photo_type_id'])
            ?? throw new DomainException('Un tipo de fotografía ya no está disponible. Actualiza e inténtalo de nuevo.');
        $paper = $this->paper($in['paper_type_id'] ?? null);

        return $doc->{$this->r($doc)['lines']}()->create([
            'photo_type_id' => $pt->id,
            'photo_type_name_snapshot' => $pt->name,
            'quantity' => $in['quantity'],
            'unit_price' => $pt->base_price,
            'format' => $in['format'],
            'background' => $this->normalizeBackground($in['background'] ?? null),
            'paper_type_id' => $paper?->id,
            'paper_type_name_snapshot' => $paper?->name,
        ]);
    }

    private function updateLine(Model $line, array $in): void
    {
        $attrs = [
            'quantity' => $in['quantity'],
            'format' => $in['format'],
        ];

        if (array_key_exists('background', $in)) {
            $attrs['background'] = $this->normalizeBackground($in['background']);
        }

        $inPackage = ($line->order_package_id ?? $line->quote_package_id) !== null;

        if ((int) $in['photo_type_id'] !== (int) $line->photo_type_id) {
            $pt = PhotoType::find($in['photo_type_id'])
                ?? throw new DomainException('Un tipo de fotografía ya no está disponible.');
            $attrs['photo_type_id'] = $pt->id;
            $attrs['photo_type_name_snapshot'] = $pt->name;

            if (!$inPackage) {
                $attrs['unit_price'] = $pt->base_price;
            }
        }

        $newPaper = $in['paper_type_id'] ?? null;
        if ((int) $newPaper !== (int) $line->paper_type_id) {
            $paper = $this->paper($newPaper);
            $attrs['paper_type_id'] = $paper?->id;
            $attrs['paper_type_name_snapshot'] = $paper?->name;
        }

        $line->update($attrs);
    }

    private function addService(Model $doc, array $s): Model
    {
        $svc = AdditionalService::find($s['additional_service_id'])
            ?? throw new DomainException('Un servicio adicional ya no está disponible.');

        return $doc->services()->create([
            'additional_service_id' => $svc->id,
            'additional_service_name_snapshot' => $svc->name,
            'price' => $s['price'],
            'quantity' => $s['quantity'],
            'notes' => $s['notes'] ?? null,
        ]);
    }

    private function paper(?int $id): ?PaperType
    {
        if (!$id) {
            return null;
        }

        return PaperType::find($id) ?? throw new DomainException('Un tipo de papel ya no está disponible.');
    }

    private function normalizeBackground(?string $value): ?string
    {
        $trimmed = $value !== null ? trim($value) : '';

        return $trimmed === '' ? null : $trimmed;
    }
}