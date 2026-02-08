<?php

declare(strict_types=1);

namespace Kulturman\MobileMoneyParser\DTOs;

readonly class ParsedSmsDTO
{
    public function __construct(
        public int $amount,
        public string $reference,
        public ?string $transactionId,
        public ?string $senderPhone,
    ) {}

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'reference' => $this->reference,
            'transaction_id' => $this->transactionId,
            'sender_phone' => $this->senderPhone,
        ];
    }
}
