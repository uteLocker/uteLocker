<?php

namespace App\Services\Wallets;

use App\Classes\Common;
use App\Classes\Reply;
use App\Enums\NotificationParentTable;
use App\Enums\NotificationType;
use App\Enums\PaymentMethodType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\BaseService;
use App\Traits\HandleNotification;

class TransactionService extends BaseService
{
    use HandleNotification;
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    public function initDefaultData(): static
    {
        $this->model->status = TransactionStatus::PENDING;
        $this->model->type = TransactionType::DEPOSIT;

        return $this;
    }

    public function add(array $inputs, array $options = []): Transaction
    {
        $this->new();
        $this->formatInputData($inputs);
        $this->setModelFields($inputs);
        $this->model->save();

        return $this->model;
    }

    protected function formatInputData(&$inputs)
    {
        return;
    }

    protected function setModelFields($inputs): void
    {
        Common::assignField($this->model, 'user_id', $inputs);
        Common::assignField($this->model, 'payment_method_id', $inputs);
        Common::assignField($this->model, 'amount', $inputs);
        Common::assignField($this->model, 'status', $inputs);
        Common::assignField($this->model, 'type', $inputs);
        Common::assignField($this->model, 'reference', $inputs);
        Common::assignField($this->model, 'reference_transaction_id', $inputs);
        Common::assignField($this->model, 'balance', $inputs);
        Common::assignField($this->model, 'promotion_balance', $inputs);
        Common::assignField($this->model, 'time', $inputs);
        Common::assignField($this->model, 'content', $inputs);
    }

    public function validateUniqueReference(string $reference): bool
    {
        return $this->model
            ->where('reference', $reference)
            ->count() === 0;
    }

    public function handlePayment(Wallet $wallet, int $amount, string $content = '') {
        $resultPayment = $this->diffBalance($wallet, $amount);
        if ($resultPayment) {
            $paymentMethod = PaymentMethod::where('client_id', user()->client_id)
                ->where('type', PaymentMethodType::UTEPAY)
                ->first();
            $reference = $this->genderReference('UTEPAY');
            $inputs = [
                'user_id' => $wallet->user_id,
                'payment_method_id' =>  $paymentMethod->id,
                'amount' => $amount,
                'status' =>  TransactionStatus::SUCCESS,
                'type' =>  TransactionType::PAYMENT,
                'reference' =>  $reference,
                'reference_transaction_id' =>  $reference,
                'balance' =>  $resultPayment['balance'],
                'promotion_balance' =>  $resultPayment['promotion_balance'],
                'time' =>  now(),
                'content' => $content,
            ];
            $transaction = $this->add($inputs);
            $this->sendNotification(
                NotificationType::PAYMENT,
                $content . ' số tiền ' . $amount . 'đ',
                $wallet->user_id,
                user()->client_id,
                NotificationParentTable::TABLE_TRANSACTIONS,
                $transaction->id,
            );
            return true;
        }
        return false;
    }

    private function diffBalance(Wallet $wallet, int $amount)
    {
        $wallet->promotion_balance -= $amount;
        if ($wallet->promotion_balance < 0) {
            $wallet->balance += $wallet->promotion_balance;
            $wallet->promotion_balance = 0;

            if ($wallet->balance < 0) {
                return false;
            }
        }
        $result = [
            'balance' => $wallet->getOriginal('balance') - $wallet->balance,
            'promotion_balance' => $wallet->getOriginal('promotion_balance') - $wallet->promotion_balance,
        ];
        $wallet->save();

        return $result;
    }

    private function genderReference($prefix = '')
    {
        while (true) {
            $number = rand(1000000000, 9999999999);
            $reference = $prefix . $number;
            if ($this->validateUniqueReference($reference)) {
                return $reference;
            }
        }
    }
}
