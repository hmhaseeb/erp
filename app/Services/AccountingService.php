<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class AccountingService
{
    /**
     * Record a financial transaction for an account.
     */
    public function recordTransaction(
        int $accountId,
        string $date,
        string $type,
        float $debit,
        float $credit,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null
    ): AccountTransaction {
        $account = Account::findOrFail($accountId);

        // Calculate new running balance for account
        $newBalance = $account->current_balance + $debit - $credit;

        $transaction = AccountTransaction::create([
            'account_id' => $account->id,
            'transaction_date' => $date,
            'transaction_type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $newBalance,
            'description' => $description,
        ]);

        $account->update(['current_balance' => $newBalance]);

        return $transaction;
    }

    /**
     * Transfer funds between two accounts.
     */
    public function transfer(
        int $fromAccountId,
        int $toAccountId,
        float $amount,
        string $date,
        ?string $notes = null
    ): void {
        if ($amount <= 0) {
            throw new Exception("Transfer amount must be greater than zero.");
        }

        DB::transaction(function () use ($fromAccountId, $toAccountId, $amount, $date, $notes) {
            $fromAccount = Account::findOrFail($fromAccountId);
            $toAccount = Account::findOrFail($toAccountId);

            // Debit target account (Inflow)
            $this->recordTransaction(
                $toAccount->id,
                $date,
                'Transfer In',
                $amount,
                0,
                'Transfer',
                null,
                "Transfer from {$fromAccount->name}. " . ($notes ?? '')
            );

            // Credit source account (Outflow)
            $this->recordTransaction(
                $fromAccount->id,
                $date,
                'Transfer Out',
                0,
                $amount,
                'Transfer',
                null,
                "Transfer to {$toAccount->name}. " . ($notes ?? '')
            );
        });
    }
}
