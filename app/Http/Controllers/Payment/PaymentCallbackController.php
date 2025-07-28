<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Events\OrderPaymentConfirmed;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function handleCallback(Request $request, string $gateway)
    {
        Log::info("Callback recibido de la pasarela: {$gateway}", $request->all());

        // Tarea 4.2.4: Implementar la verificación de la firma/autenticidad de la notificación.
        // Esto es específico de cada pasarela.
        // Ejemplo:
        // $isValidSignature = $this->verifyGatewaySignature($request, $gateway);
        // if (!$isValidSignature) {
        //     Log::error("Firma inválida para el callback de {$gateway}.");
        //     return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        // }

        // Tarea 4.2.5: Si la transacción es exitosa, actualizar Order y despachar evento.
        // La lógica para determinar el éxito y obtener el ID del pedido también es específica de la pasarela.
        $orderId = null; // Obtener el ID del pedido desde $request según la pasarela
        $transactionSuccessful = false; // Determinar si la transacción fue exitosa según $request

        // Ejemplo (muy simplificado):
        // $orderId = $request->input('order_id'); // O como venga de la pasarela
        // $paymentStatus = $request->input('payment_status'); // O como venga
        // if ($paymentStatus === 'approved' || $paymentStatus === 'success') {
        //    $transactionSuccessful = true;
        // }


        if ($orderId && $transactionSuccessful) {
            $order = Order::find($orderId);
            if ($order) {
                if ($order->status === 'pending_payment') { // Solo procesar si aún está pendiente
                    $order->status = 'processing'; // O 'paid' directamente
                    $order->paid_at = now();
                    // Guardar ID de transacción de la pasarela si está disponible
                    // $order->payment_gateway_transaction_id = $request->input('transaction_id');
                    $order->save();

                    event(new OrderPaymentConfirmed($order));
                    Log::info("Pedido {$order->id} actualizado a 'processing' y evento OrderPaymentConfirmed despachado vía callback de {$gateway}.");
                    
                    // Redirigir al usuario a una página de éxito o mostrar un mensaje.
                    // Esto depende de si la pasarela hace un POST de servidor a servidor o redirige al usuario.
                    // Si es una redirección del usuario:
                    // return redirect()->route('payment.success', ['order' => $order->id])
                    //     ->with('status', '¡Pago completado exitosamente!');
                } else {
                    Log::info("Callback de {$gateway} recibido para el pedido {$order->id}, pero el pedido no está 'pending_payment' (estado actual: {$order->status}).");
                }
            } else {
                Log::error("Callback de {$gateway} recibido, pero el pedido con ID {$orderId} no fue encontrado.");
            }
        } elseif ($orderId && !$transactionSuccessful) {
            // Tarea 4.2.6: Manejar errores de pago
            $order = Order::find($orderId);
            if ($order) {
                $order->status = 'payment_failed';
                $order->save();
                Log::info("Pago fallido para el pedido {$order->id} según callback de {$gateway}."); // Corregido Log_info a Log::info
                // Redirigir al usuario a una página de fallo de pago o mostrar un mensaje.
                // return redirect()->route('payment.failed', ['order' => $order->id])
                //    ->with('error', 'El pago no pudo ser procesado.');
            }
        } else {
            Log::warning("Callback de {$gateway} recibido con datos insuficientes o transacción no exitosa.", $request->all());
        }

        // La respuesta a la pasarela depende de sus requerimientos (ej. un HTTP 200 OK).
        return response()->json(['status' => 'received']);
    }

    // private function verifyGatewaySignature(Request $request, string $gateway): bool
    // {
    //     // Implementar lógica de verificación de firma específica para cada pasarela
    //     // switch ($gateway) {
    //     //     case 'pasarela_x':
    //     //         // Lógica para Pasarela X
    //     //         return true; // Placeholder
    //     //     default:
    //     //         return false;
    //     // }
    //     return true; // Placeholder para desarrollo
    // }
}
