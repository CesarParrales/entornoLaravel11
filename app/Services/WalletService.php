<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class WalletService
{
    protected string $roleSocioMultinivel = 'Socio Multinivel'; // Define el nombre exacto del rol

    /**
     * Find a wallet for a user.
     *
     * @param User $user
     * @return Wallet|null
     */
    public function findUserWallet(User $user): ?Wallet
    {
        return Wallet::where('user_id', $user->id)->first();
    }

    /**
     * Get or create a wallet for a user, ensuring it only creates for 'Socio Multinivel'.
     * This method is primarily for internal use by credit/debit operations
     * which should only operate on existing, active wallets of socios.
     * For explicit creation during registration, use ensureWalletExistsForSocio.
     *
     * @param User $user
     * @return Wallet|null Returns the wallet if the user is a socio and wallet exists or is created, null otherwise.
     */
    protected function getOrCreateWalletForSocio(User $user): ?Wallet
    {
        if (!$user->hasRole($this->roleSocioMultinivel)) {
            Log::info("User {$user->id} is not a '{$this->roleSocioMultinivel}'. Wallet not created or retrieved by getOrCreateWalletForSocio.");
            return null;
        }

        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 0.00,
                'currency_code' => 'USD', // Default currency
                'status' => 'active',     // Default status
            ]
        );
    }

    /**
     * Ensures a wallet exists for a user if they are a 'Socio Multinivel'.
     * Intended to be called during user registration or role assignment.
     *
     * @param User $user
     * @return Wallet|null The created or existing wallet, or null if user is not a Socio.
     */
    public function ensureWalletExistsForSocio(User $user): ?Wallet
    {
        if ($user->hasRole($this->roleSocioMultinivel)) {
            $wallet = $this->findUserWallet($user);
            if (!$wallet) {
                return Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0.00,
                    'currency_code' => 'USD',
                    'status' => 'active',
                ]);
            }
            return $wallet;
        }
        Log::info("User {$user->id} is not a '{$this->roleSocioMultinivel}'. Wallet creation skipped by ensureWalletExistsForSocio.");
        return null;
    }


    /**
     * Credit an amount to a user's wallet.
     *
     * @param User $user
     * @param float $amount
     * @param string $type The type of transaction (e.g., 'commission_payout', 'bonus_payout', 'deposit')
     * @param string $description
     * @param Model|null $sourceable The source model of the transaction (e.g., Bonus, Order)
     * @param array|null $metadata Additional data for the transaction
     * @return WalletTransaction|null
     */
    public function credit(User $user, float $amount, string $type, string $description, $sourceable = null, ?array $metadata = null): ?WalletTransaction
    {
        if ($amount <= 0) {
            Log::warning("Attempted to credit a non-positive amount ({$amount}) to user {$user->id}.");
            return null;
        }

        // Use getOrCreateWalletForSocio which also checks role
        $wallet = $this->getOrCreateWalletForSocio($user);

        if (!$wallet) {
            Log::warning("No active wallet found or user {$user->id} is not a '{$this->roleSocioMultinivel}'. Credit operation aborted.");
            return null;
        }

        if ($wallet->status !== 'active') {
            Log::warning("Attempted to credit to a non-active wallet (ID: {$wallet->id}, Status: {$wallet->status}) for user {$user->id}.");
            return null;
        }

        try {
            return DB::transaction(function () use ($wallet, $user, $amount, $type, $description, $sourceable, $metadata) {
                $balanceBefore = $wallet->balance;
                $wallet->balance += $amount;
                $wallet->last_transaction_at = now();
                $wallet->save();

                $transaction = $wallet->transactions()->create([
                    'user_id' => $user->id, // Denormalized for easier querying
                    'type' => $type,
                    'amount' => $amount,
                    'balance_before_transaction' => $balanceBefore,
                    'balance_after_transaction' => $wallet->balance,
                    'currency_code' => $wallet->currency_code,
                    'description' => $description,
                    'sourceable_id' => $sourceable ? $sourceable->id : null,
                    'sourceable_type' => $sourceable ? get_class($sourceable) : null,
                    'metadata' => $metadata,
                    'status' => 'completed', // Credits are usually completed immediately
                    'processed_at' => now(),
                ]);
                return $transaction;
            });
        } catch (Throwable $e) {
            Log::error("Failed to credit wallet for user {$user->id}: " . $e->getMessage(), [
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'exception' => $e
            ]);
            return null;
        }
    }

    /**
     * Debit an amount from a user's wallet.
     *
     * @param User $user
     * @param float $amount
     * @param string $type The type of transaction (e.g., 'withdrawal', 'purchase', 'fee')
     * @param string $description
     * @param Model|null $sourceable The source model of the transaction
     * @param array|null $metadata Additional data for the transaction
     * @return WalletTransaction|null
     */
    public function debit(User $user, float $amount, string $type, string $description, $sourceable = null, ?array $metadata = null): ?WalletTransaction
    {
        if ($amount <= 0) {
            Log::warning("Attempted to debit a non-positive amount ({$amount}) from user {$user->id}.");
            return null;
        }

        $wallet = $this->getOrCreateWalletForSocio($user); // Ensures socio and gets/creates wallet

        if (!$wallet) {
            Log::warning("No active wallet found or user {$user->id} is not a '{$this->roleSocioMultinivel}'. Debit operation aborted.");
            return null;
        }

        if ($wallet->status !== 'active') {
            Log::warning("Attempted to debit from a non-active wallet (ID: {$wallet->id}, Status: {$wallet->status}) for user {$user->id}.");
            return null;
        }

        if ($wallet->balance < $amount) {
            Log::warning("Insufficient balance for debit for user {$user->id}. Requested: {$amount}, Available: {$wallet->balance}.");
            return null; 
        }

        try {
            return DB::transaction(function () use ($wallet, $user, $amount, $type, $description, $sourceable, $metadata) {
                $balanceBefore = $wallet->balance;
                $wallet->balance -= $amount;
                $wallet->last_transaction_at = now();
                $wallet->save();

                $transaction = $wallet->transactions()->create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'amount' => $amount, 
                    'balance_before_transaction' => $balanceBefore,
                    'balance_after_transaction' => $wallet->balance,
                    'currency_code' => $wallet->currency_code,
                    'description' => $description,
                    'sourceable_id' => $sourceable ? $sourceable->id : null,
                    'sourceable_type' => $sourceable ? get_class($sourceable) : null,
                    'metadata' => $metadata,
                    'status' => 'completed', 
                    'processed_at' => now(),
                ]);
                return $transaction;
            });
        } catch (Throwable $e) {
            Log::error("Failed to debit wallet for user {$user->id}: " . $e->getMessage(), [
                'amount' => $amount,
                'type' => $type,
                'description' => $description,
                'exception' => $e
            ]);
            return null;
        }
    }
}