<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedOfflinePaymentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public Order $order;
    public string $paymentMethodName;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Order $order, string $paymentMethodName)
    {
        $this->user = $user;
        $this->order = $order;
        $this->paymentMethodName = $paymentMethodName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de tu Pedido #' . $this->order->order_number . ' y Próximos Pasos',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order.placed-offline',
            with: [
                'userName' => $this->user->first_name,
                'orderNumber' => $this->order->order_number,
                'orderTotal' => $this->order->grand_total,
                'paymentMethodName' => $this->paymentMethodName,
                // Aquí se podrían añadir dinámicamente las instrucciones específicas del método de pago
                // Por ejemplo, obteniéndolas de una configuración o del módulo de CompanySettings.
                'paymentInstructions' => $this->getPaymentInstructions(),
            ],
        );
    }

    protected function getPaymentInstructions(): string
    {
        // Lógica placeholder para obtener instrucciones.
        // Esto debería obtenerse de una configuración o de la BD en una implementación real.
        $instructions = "Por favor, realiza el pago de tu pedido utilizando {$this->paymentMethodName}.\n";
        if (str_contains(strtolower($this->paymentMethodName), 'depósito') || str_contains(strtolower($this->paymentMethodName), 'transferencia')) {
            // Ejemplo: Obtener de CompanySetting o una tabla de PaymentMethodDetails
            // $bankAccount = \App\Models\CompanyBankAccount::where('is_default_for_payments', true)->first();
            // if ($bankAccount) {
            //     $instructions .= "Banco: {$bankAccount->bank_name}\n";
            //     $instructions .= "Número de Cuenta: {$bankAccount->account_number}\n";
            //     $instructions .= "Titular: {$bankAccount->account_holder_name}\n";
            //     $instructions .= "Tipo de Cuenta: {$bankAccount->account_type}\n";
            //     $instructions .= "Referencia: Pedido #{$this->order->order_number}\n";
            // } else {
            //     $instructions .= "Contacta a soporte para los detalles bancarios.\n";
            // }
            $instructions .= "Una vez realizado el pago, envía el comprobante a pagos@example.com con el asunto: Pedido #{$this->order->order_number}.";
        } elseif (str_contains(strtolower($this->paymentMethodName), 'punto de venta')) {
            $instructions .= "Acércate a nuestro punto de venta más cercano para completar tu pago en efectivo o con tarjeta.";
        } else {
            $instructions .= "Sigue las instrucciones específicas para {$this->paymentMethodName} que te proporcionará nuestro equipo de soporte si es necesario.";
        }
        return $instructions;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
