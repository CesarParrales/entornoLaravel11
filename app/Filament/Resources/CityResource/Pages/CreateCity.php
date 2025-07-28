<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\City;
use App\Models\Province; // Necesario para la validación
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log; // Para logs

class CreateCity extends CreateRecord
{
    protected static string $resource = CityResource::class;

    protected function handleRecordCreation(array $data): City
    {
        Log::channel('import')->info('[CreateCity] handleRecordCreation INVOCADO. Datos recibidos:', $data);

        if (isset($data['creation_mode_city']) && $data['creation_mode_city'] === 'bulk') {
            $countryId = $data['country_id'];
            $provinceId = $data['province_id'];
            $isActive = $data['is_active'];
            $cityNamesList = $data['city_names_list'] ?? '';

            // Validar que la provincia seleccionada realmente pertenezca al país seleccionado
            $province = Province::where('id', $provinceId)->where('country_id', $countryId)->first();
            if (!$province) {
                Notification::make()
                    ->title('Error de Consistencia')
                    ->body('La provincia seleccionada no pertenece al país seleccionado.')
                    ->danger()
                    ->send();
                $this->halt();
                return new City(); // Instancia dummy
            }

            $cityNames = array_map('trim', explode(',', $cityNamesList));
            $cityNames = array_filter($cityNames);

            if (empty($cityNames)) {
                Notification::make()
                    ->title('Entrada Vacía')
                    ->body('No se proporcionaron nombres de ciudad para la creación masiva.')
                    ->warning()
                    ->send();
                $this->halt();
                return new City();
            }

            $createdCount = 0;
            $skippedCount = 0;
            $errors = [];

            DB::beginTransaction();
            try {
                foreach ($cityNames as $cityName) {
                    if (empty($cityName)) continue;

                    if (Str::length($cityName) > 255) {
                        $errors[] = "Nombre '{$cityName}' excede los 255 caracteres.";
                        $skippedCount++;
                        continue;
                    }

                    $existingCity = City::where('province_id', $provinceId)
                                        ->where('name', $cityName)
                                        ->first();
                    
                    if ($existingCity) {
                        $errors[] = "Ciudad '{$cityName}' ya existe en la provincia '{$province->name}'.";
                        $skippedCount++;
                        continue;
                    }

                    City::create([
                        'country_id' => $countryId, // Denormalizado
                        'province_id' => $provinceId,
                        'name' => $cityName,
                        'is_active' => $isActive,
                    ]);
                    $createdCount++;
                }
                DB::commit();

                $notificationBody = "Creación masiva de ciudades completada para la provincia '{$province->name}'.<br>";
                $notificationBody .= "Ciudades creadas: {$createdCount}.<br>";
                $notificationBody .= "Ciudades omitidas/duplicadas: {$skippedCount}.";
                if (!empty($errors)) {
                    $notificationBody .= "<br><br>Detalles:<br>" . implode("<br>", $errors);
                }

                Notification::make()
                    ->title('Resultado Creación Masiva de Ciudades')
                    ->body(str($notificationBody)->toHtmlString())
                    ->success($skippedCount === 0 && $createdCount > 0)
                    ->warning($skippedCount > 0 && $createdCount > 0)
                    ->danger($createdCount === 0 && $skippedCount > 0)
                    ->info($createdCount === 0 && $skippedCount === 0 && empty($errors) && !empty($cityNames))
                    ->duration(10000)
                    ->send();
                
                return new City(); // Dummy, la redirección se maneja abajo

            } catch (\Exception $e) {
                DB::rollBack();
                Log::channel('import')->error('[CreateCity] EXCEPCIÓN CAPTURADA (bulk):', [
                    'message' => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'trace' => Str::substr($e->getTraceAsString(), 0, 1000)
                ]);
                Notification::make()
                    ->title('Error en Creación Masiva')
                    ->body('Ocurrió un error inesperado: ' . $e->getMessage())
                    ->danger()
                    ->persistent()
                    ->send();
                 $this->halt();
                 return new City();
            }
        } else {
            // Modo 'single'
            // Asegurar que country_id se incluya si no está directamente en $data para City::create
            // El modelo City tiene country_id en $fillable, y el formulario lo tiene.
            // La lógica de CreateRecord debería manejarlo.
            return parent::handleRecordCreation($data);
        }
    }

    protected function getRedirectUrl(): string
    {
        if (isset($this->data['creation_mode_city']) && $this->data['creation_mode_city'] === 'bulk') {
            return $this->getResource()::getUrl('index');
        }
        return parent::getRedirectUrl();
    }
}
