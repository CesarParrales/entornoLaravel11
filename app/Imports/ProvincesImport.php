<?php

namespace App\Imports;

use App\Models\Country;
use App\Models\Province;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProvincesImport extends Importer
{
    protected static ?string $model = Province::class;

    public static function getColumns(): array
    {
        Log::channel('import')->info('[ProvincesImport] getColumns() INVOCADO.');
        return [
            ImportColumn::make('country_iso_code_2')
                ->label('ISO 2 del País')
                ->requiredMapping()
                ->rules(['required', 'string', 'exists:countries,iso_code_2']),
            ImportColumn::make('province_name')
                ->label('Nombre Provincia')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('is_active')
                ->label('Activo (1=Sí, 0=No; omitir para Sí por defecto)')
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): ?Province
    {
        $countryIsoCode = isset($this->data['country_iso_code_2']) ? strtoupper(trim($this->data['country_iso_code_2'])) : null;
        $provinceName = isset($this->data['province_name']) ? trim($this->data['province_name']) : null;
        
        Log::channel('import')->info("----------------------------------------------------");
        Log::channel('import')->info('[ProvincesImport] resolveRecord(): INICIO. Datos CSV fila:', $this->data);

        if (!$countryIsoCode || !$provinceName) {
            Log::channel('import')->warning('[ProvincesImport] resolveRecord(): Faltan country_iso_code_2 o province_name. Omitiendo fila.');
            return null;
        }

        $country = Country::where('iso_code_2', $countryIsoCode)->first();
        if (!$country) {
            Log::channel('import')->warning("[ProvincesImport] resolveRecord(): País no encontrado con ISO '{$countryIsoCode}'. Omitiendo fila.");
            return null;
        }
        Log::channel('import')->info("[ProvincesImport] resolveRecord(): País encontrado ID: {$country->id} ({$country->name}).");

        $province = Province::where('country_id', $country->id)
                       ->where('name', $provinceName)
                       ->first();
        
        if ($province) {
            Log::channel('import')->info("[ProvincesImport] resolveRecord(): Provincia ENCONTRADA '{$provinceName}' en país '{$country->name}'. ID: {$province->id}. Se actualizará.");
        } else {
            Log::channel('import')->info("[ProvincesImport] resolveRecord(): Provincia NO encontrada '{$provinceName}' en país '{$country->name}'. Se creará.");
        }
        return $province;
    }

    protected function handleRecordCreation(array $data): ?Province
    {
        Log::channel('import')->info("----------------------------------------------------");
        Log::channel('import')->info('[ProvincesImport] handleRecordCreation(): INICIO. Datos mapeados por Filament:', $data);

        $countryIsoCode = strtoupper(trim($data['country_iso_code_2']));
        $provinceName = trim($data['province_name']);
        
        $country = Country::where('iso_code_2', $countryIsoCode)->first();
        if (!$country) {
            Log::channel('import')->error("[ProvincesImport] handleRecordCreation(): País no encontrado {$countryIsoCode} para provincia {$provinceName}. Esto no debería pasar si las reglas de getColumns() funcionaron. Omitiendo.");
            return null;
        }
        
        $preparedData = [
            'country_id'  => $country->id,
            'name'        => $provinceName,
            'is_active'   => array_key_exists('is_active', $data) && $data['is_active'] !== '' && $data['is_active'] !== null
                           ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true
                           : true,
        ];
        Log::channel('import')->info('[ProvincesImport] handleRecordCreation(): Datos preparados para creación:', $preparedData);
        
        try {
            // Verificar si ya existe una provincia con el mismo nombre en el mismo país
            // Esta es una doble verificación, ya que resolveRecord debería haberlo manejado si la provincia ya existía.
            // Pero si resolveRecord devuelve null (para crear), y luego aquí encontramos que ya existe
            // (quizás por una condición de carrera o un CSV con duplicados que no tienen otros campos para diferenciar en resolveRecord),
            // es mejor no intentar crearla y fallar con error de BD.
            $existingProvince = Province::where('country_id', $country->id)
                                   ->where('name', $provinceName)
                                   ->first();
            if ($existingProvince) {
                Log::channel('import')->error("[ProvincesImport] handleRecordCreation(): Provincia '{$provinceName}' ya existe en país ID {$country->id} (ID: {$existingProvince->id}). Omitiendo creación duplicada.");
                return null; // No intentar crear si ya existe
            }

            $newProvince = new Province();
            $newProvince->fill($preparedData);
            $saved = $newProvince->save();

            if ($saved) {
                Log::channel('import')->info("[ProvincesImport] handleRecordCreation(): Provincia CREADA. ID: {$newProvince->id}, Nombre: {$newProvince->name}");
                return $newProvince;
            } else {
                Log::channel('import')->error("[ProvincesImport] handleRecordCreation(): Province::save() devolvió FALSE para provincia {$provinceName}. Datos:", $preparedData);
                return null;
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::channel('import')->error("[ProvincesImport] handleRecordCreation(): QueryException al crear provincia {$provinceName}: " . $e->getMessage() . " SQLSTATE: " . $e->getCode() . " Datos: ", $preparedData);
            if (str_contains(strtolower($e->getMessage()), 'unique constraint') || str_contains(strtolower($e->getMessage()), 'duplicate entry')) {
                 Log::channel('import')->error("[ProvincesImport] handleRecordCreation(): Parece ser un error de restricción UNIQUE para {$provinceName}.");
            }
            return null;
        } catch (\Throwable $e) {
            Log::channel('import')->error("[ProvincesImport] handleRecordCreation(): EXCEPCIÓN GENERAL al crear provincia {$provinceName}: " . $e->getMessage(), ['data' => $preparedData, 'trace' => substr($e->getTraceAsString(), 0, 1000)]);
            return null;
        }
    }

    protected function handleRecordUpdate(Province $record, array $data): Province
    {
        Log::channel('import')->info("----------------------------------------------------");
        Log::channel('import')->info("[ProvincesImport] handleRecordUpdate(): INICIO. Actualizando provincia ID: {$record->id} ({$record->name}) con datos mapeados:", $data);
        
        $updateData = [
            // No actualizamos 'name' aquí si la provincia se resolvió por nombre y país.
            // Si se permite cambiar el nombre vía importación, se necesitaría una lógica más compleja
            // para manejar la unicidad del nuevo nombre. Por ahora, asumimos que el nombre no cambia en la actualización.
            // 'name'        => trim($data['province_name']),
            'is_active'   => array_key_exists('is_active', $data) && $data['is_active'] !== '' && $data['is_active'] !== null
                           ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $record->is_active
                           : $record->is_active,
        ];
        Log::channel('import')->info("[ProvincesImport] handleRecordUpdate(): Datos preparados para actualización:", $updateData);

        $record->fill($updateData);

        if ($record->isDirty()) {
            Log::channel('import')->info("[ProvincesImport] handleRecordUpdate(): Modelo sucio. Cambios:", $record->getChanges());
            try {
                $saved = $record->save();
                if ($saved) {
                    Log::channel('import')->info("[ProvincesImport] handleRecordUpdate(): Provincia ACTUALIZADA exitosamente. ID: {$record->id}");
                } else {
                    Log::channel('import')->error("[ProvincesImport] handleRecordUpdate(): Province::save() devolvió FALSE para ID: {$record->id}. Datos:", $updateData);
                }
            } catch (\Illuminate\Database\QueryException $e) {
                Log::channel('import')->error("[ProvincesImport] handleRecordUpdate(): QueryException al actualizar provincia ID {$record->id}: " . $e->getMessage() . " SQLSTATE: " . $e->getCode() . " Datos: ", $updateData);
                 if (str_contains(strtolower($e->getMessage()), 'unique constraint') || str_contains(strtolower($e->getMessage()), 'duplicate entry')) {
                    Log::channel('import')->error("[ProvincesImport] handleRecordUpdate(): Parece ser un error de restricción UNIQUE para ID {$record->id}.");
                }
            } catch (\Throwable $e) {
                Log::channel('import')->error("[ProvincesImport] handleRecordUpdate(): EXCEPCIÓN GENERAL al actualizar provincia ID {$record->id}: " . $e->getMessage(), ['data' => $updateData, 'trace' => substr($e->getTraceAsString(), 0, 1000)]);
            }
        } else {
            Log::channel('import')->info("[ProvincesImport] handleRecordUpdate(): No hay cambios detectados para provincia ID: {$record->id}. No se guarda.");
        }
        return $record;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Tu importación de provincias ha sido completada y ' . number_format($import->successful_rows) . ' ' . str('fila')->plural($import->successful_rows) . ' han sido importadas.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('fila')->plural($failedRowsCount) . ' fallaron al importar.';
        }

        return $body;
    }

    public static function getOptionsFormComponents(): array
    {
        return [];
    }
}
