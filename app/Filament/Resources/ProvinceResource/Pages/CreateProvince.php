<?php

namespace App\Filament\Resources\ProvinceResource\Pages;

use App\Filament\Resources\ProvinceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Province; // Necesario para crear instancias
use Illuminate\Support\Facades\DB; // Para transacciones si es necesario
use Filament\Notifications\Notification; // Para feedback
use Illuminate\Support\Str; // Para trim
use Illuminate\Support\Facades\Log; // Añadir Log

class CreateProvince extends CreateRecord
{
    protected static string $resource = ProvinceResource::class;

    protected function handleRecordCreation(array $data): Province
    {
        Log::channel('import')->info('[CreateProvince] handleRecordCreation INVOCADO. Datos recibidos:', $data);

        if (isset($data['creation_mode']) && $data['creation_mode'] === 'bulk') {
            $countryId = $data['country_id'];
            $isActive = $data['is_active'];
            $provinceNamesList = $data['province_names_list'] ?? '';

            $provinceNames = array_map('trim', explode(',', $provinceNamesList));
            $provinceNames = array_filter($provinceNames); // Eliminar nombres vacíos

            if (empty($provinceNames)) {
                Notification::make()
                    ->title('Entrada Vacía')
                    ->body('No se proporcionaron nombres de provincia para la creación masiva.')
                    ->warning()
                    ->send();
                // Devolver una instancia no guardada o lanzar una excepción para detener el flujo normal
                // o redirigir. Por ahora, devolvemos una instancia no guardada,
                // pero esto podría necesitar un manejo más elegante para evitar el "guardado" exitoso.
                // Lo ideal sería que esto se valide antes.
                $this->halt(); // Detiene la ejecución de la acción y cierra el modal.
                return new Province(); // Necesita devolver una instancia del modelo.
            }

            // Inicializar contadores y errores aquí para asegurar su scope
            $createdCount = 0;
            $skippedCount = 0; // Esta es la inicialización que Intelephense parece no ver a veces.
            $errors = [];

            DB::beginTransaction();
            try {
                // $createdCount, $skippedCount, $errors se modifican dentro de este bloque try
                // y se usan después del bucle pero antes del catch.

                foreach ($provinceNames as $provinceName) {
                    if (empty($provinceName)) continue;

                    // Validar longitud (ejemplo simple, se puede expandir)
                    if (Str::length($provinceName) > 255) {
                        $errors[] = "Nombre '{$provinceName}' excede los 255 caracteres.";
                        $skippedCount++;
                        continue;
                    }

                    $existingProvince = Province::where('country_id', $countryId)
                                                ->where('name', $provinceName)
                                                ->first();
                    
                    if ($existingProvince) {
                        $errors[] = "Provincia '{$provinceName}' ya existe para este país.";
                        $skippedCount++;
                        continue;
                    }

                    Province::create([
                        'country_id' => $countryId,
                        'name' => $provinceName,
                        'is_active' => $isActive,
                    ]);
                    $createdCount++;
                }
                DB::commit();

                $notificationBody = "Proceso de creación masiva completado.<br>";
                $notificationBody .= "Provincias creadas: {$createdCount}.<br>";
                $notificationBody .= "Provincias omitidas/duplicadas: {$skippedCount}."; // Corregido a $skippedCount
                if (!empty($errors)) {
                    $notificationBody .= "<br><br>Detalles:<br>" . implode("<br>", $errors);
                }

                Notification::make() // Descomentada
                    ->title('Resultado de Creación Masiva')
                    ->body(str($notificationBody)->toHtmlString())
                    ->success($skippedCount === 0 && $createdCount > 0 && $createdCount > 0) // Asegurar que createdCount > 0 para success
                    ->warning($skippedCount > 0 && $createdCount > 0)
                    ->danger($createdCount === 0 && $skippedCount > 0) // Si solo hubo omitidos/errores
                    ->info($createdCount === 0 && $skippedCount === 0 && empty($errors) && !empty($provinceNames)) // Si se procesó pero no se creó nada y no hubo errores (ej. lista vacía después de filtrar)
                    // ->persistent() // Eliminado para que se cierre automáticamente
                    ->duration(10000) // Opcional: 10 segundos
                    ->send();
                Log::channel('import')->info('[CreateProvince] Notificación de resultado enviada. Creadas: ' . $createdCount . ', Omitidas: ' . $skippedCount);
                
                // Después de la creación masiva (exitosa o no en términos de registros creados),
                // la redirección se manejará con getRedirectUrl().
                // No necesitamos halt() aquí si la redirección es correcta.
                // $this->redirect($this->getResource()::getUrl('index')); // Eliminado, getRedirectUrl lo maneja
                // $this->halt(); // Eliminado
                // Devolvemos una instancia para satisfacer el tipo de retorno.
                // La notificación de éxito (actualmente comentada) informará al usuario.
                return new Province();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::channel('import')->error('[CreateProvince] EXCEPCIÓN CAPTURADA en handleRecordCreation (bulk):', [
                    'message' => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]);

                $errorMessage = 'Ocurrió un error inesperado.';
                if (!empty($e->getMessage())) {
                    $errorMessage .= ' Detalles: ' . $e->getMessage();
                } else {
                    $errorMessage .= ' La excepción no proporcionó un mensaje detallado. Revise los logs del sistema.';
                }

                Notification::make()
                    ->title('Error en Creación Masiva')
                    ->body($errorMessage)
                    ->danger()
                    ->persistent() // Hacerla persistente para que el usuario la vea bien
                    ->send();
                 $this->halt();
                 return new Province(); // Para satisfacer el tipo de retorno, no se usará si halt() funciona.
            }
        } else {
            // Modo 'single', usar la lógica estándar de CreateRecord
            return parent::handleRecordCreation($data);
        }
    }

    protected function getRedirectUrl(): string
    {
        // Si estamos en modo 'bulk', siempre redirigimos al índice después de la acción.
        // Accedemos a los datos del formulario a través de $this->data, que está disponible
        // después de que el formulario ha sido enviado y procesado por handleRecordCreation.
        if (isset($this->data['creation_mode']) && $this->data['creation_mode'] === 'bulk') {
            return $this->getResource()::getUrl('index');
        }
        
        // Para el modo 'single', usamos la lógica de redirección estándar de CreateRecord,
        // que intentará ir a la página de vista del registro recién creado.
        return parent::getRedirectUrl();
    }
}
