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

/**
 * Único lugar que construye/edita el contenido (paquetes, líneas, servicios)
 * de una Order o una Quote. Reglas:
 *  - Elemento NUEVO  -> toma datos y precio del catálogo vigente (solo activos).
 *  - Elemento EXISTENTE -> conserva su unit_price / price_snapshot; nunca consulta precios.
 *  - El total se calcula SIEMPRE desde las filas propias del documento.
 * Usar dentro de DB::transaction().
 */
class ContentSnapshotService
{
    private function r(Model $doc): array
    {
        return $doc instanceof Order
            ? ['packages' => 'orderPackages', 'lines' => 'orderPhotoTypes', 'fk' => 'order_package_id']
            : ['packages' => 'quotePackages', 'lines' => 'quotePhotoTypes', 'fk' => 'quote_package_id'];
    }

    /* ---------------- creación ---------------- */

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

    /* ---------------- edición ---------------- */

    /**
     * Sincroniza el contenido con el payload.
     * - package_ids: los ya presentes se conservan (snapshot intacto), los ausentes se quitan
     *   (con sus líneas incluidas), los nuevos se agregan desde el catálogo.
     * - photo_types[].id: línea existente => solo cantidad/formato/papel/sustitución.
     *   Sin id => línea nueva a precio vigente.
     * - additional_services: se empareja por additional_service_id; el nombre snapshot se conserva.
     */
    public function syncFor(Model $doc, array $data): void
    {
        $r = $this->r($doc);

        // Paquetes
        $wanted = array_values(array_unique($data['package_ids'] ?? []));
        $existing = $doc->{$r['packages']}()->get()->keyBy('package_id');
        $existing->filter(fn ($row, $pkgId) => !in_array($pkgId, $wanted))->each->delete(); // cascade borra sus líneas incluidas
        $new = array_values(array_diff($wanted, $existing->keys()->all()));
        $this->loadPackages($new)->each(fn (Package $p) => $this->addPackage($doc, $p));

        // Líneas de foto
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

        // Servicios (si la clave no viene en el payload, se conservan tal cual)
        if (!array_key_exists('additional_services', $data)) {
            return;
        }
        $currentSvc = $doc->services()->get()->keyBy('additional_service_id');
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
        $doc->services()->whereNotIn('id', $keepSvc)->delete();
    }

    /* ---------------- total ---------------- */

    public function recalculate(Model $doc): float
    {
        $r = $this->r($doc);

        $packages = (float) $doc->{$r['packages']}()->sum('price_snapshot');
        $lines = (float) $doc->{$r['lines']}()
            ->selectRaw('COALESCE(SUM(unit_price * quantity), 0) AS t')->value('t');
        $services = (float) $doc->services()
            ->selectRaw('COALESCE(SUM(price * quantity), 0) AS t')->value('t');

        return round($packages + $lines + $services, 2);
    }

    /* ---------------- cotización -> orden ---------------- */

    /** Copia tal cual (sin consultar catálogo) el contenido de la cotización a la orden. */
    public function copyQuoteToOrder(Quote $quote, Order $order): void
    {
        $map = [];
        foreach ($quote->quotePackages as $qp) {
            $map[$qp->id] = $order->orderPackages()->create($qp->only([
                'package_id', 'school_id', 'package_name_snapshot', 'school_name_snapshot', 'price_snapshot',
            ]))->id;
        }

        foreach ($quote->quotePhotoTypes as $ql) {
            $order->orderPhotoTypes()->create([
                ...$ql->only([
                    'photo_type_id', 'photo_type_name_snapshot', 'quantity', 'unit_price',
                    'format', 'paper_type_id', 'paper_type_name_snapshot',
                ]),
                'order_package_id' => $ql->quote_package_id ? $map[$ql->quote_package_id] : null,
            ]);
        }

        foreach ($quote->services as $s) {
            $order->services()->create($s->only([
                'additional_service_id', 'additional_service_name_snapshot', 'price', 'quantity', 'notes',
            ]));
        }
    }

    /* ---------------- internos ---------------- */

    public function loadPackages(array $ids): Collection
    {
        $ids = array_values(array_unique($ids));
        if (!$ids) {
            return collect();
        }
        $packages = Package::with(['school', 'photoTypes'])->whereIn('id', $ids)->get();
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
            'package_name_snapshot' => $pkg->name ?: 'Paquete ' . $pkg->package_number,
            'school_name_snapshot' => $pkg->school->name,
            'price_snapshot' => $pkg->total_price,
        ]);

        // El contenido del paquete se copia como líneas propias, ya pagadas dentro del precio del paquete.
        foreach ($pkg->photoTypes as $pt) {
            $doc->{$r['lines']}()->create([
                'photo_type_id' => $pt->id,
                'photo_type_name_snapshot' => $pt->name,
                'quantity' => $pt->pivot->quantity,
                'unit_price' => 0,
                'format' => 'color',
                'paper_type_id' => null,
                'paper_type_name_snapshot' => null,
                $r['fk'] => $row->id,
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
            'paper_type_id' => $paper?->id,
            'paper_type_name_snapshot' => $paper?->name,
        ]);
    }

    private function updateLine(Model $line, array $in): void
    {
        $attrs = ['quantity' => $in['quantity'], 'format' => $in['format']];
        $inPackage = ($line->order_package_id ?? $line->quote_package_id) !== null;

        // Sustitución explícita de concepto: datos nuevos del catálogo.
        // (Una línea incluida en un paquete sigue a $0: el paquete ya la cubre.)
        if ((int) $in['photo_type_id'] !== (int) $line->photo_type_id) {
            $pt = PhotoType::find($in['photo_type_id'])
                ?? throw new DomainException('Un tipo de fotografía ya no está disponible.');
            $attrs['photo_type_id'] = $pt->id;
            $attrs['photo_type_name_snapshot'] = $pt->name;
            if (!$inPackage) {
                $attrs['unit_price'] = $pt->base_price;
            }
        }

        // Papel: solo se relee del catálogo si el usuario lo cambió.
        $newPaper = $in['paper_type_id'] ?? null;
        if ((int) $newPaper !== (int) $line->paper_type_id) {
            $paper = $this->paper($newPaper);
            $attrs['paper_type_id'] = $paper?->id;
            $attrs['paper_type_name_snapshot'] = $paper?->name;
        }

        $line->update($attrs); // unit_price del cliente se ignora siempre
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
}