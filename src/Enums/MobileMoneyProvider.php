<?php

declare(strict_types=1);

namespace Kulturman\MobileMoneyParser\Enums;

enum MobileMoneyProvider: string
{
    case ORANGE_MONEY = 'orangemoney';
    case MOOV_MONEY = 'moovmoney';

    public static function fromSenderId(string $senderId): ?self
    {
        $normalized = strtolower(str_replace(' ', '', $senderId));

        return match ($normalized) {
            'orangemoney' => self::ORANGE_MONEY,
            'moovmoney' => self::MOOV_MONEY,
            default => null,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ORANGE_MONEY => 'Orange Money',
            self::MOOV_MONEY => 'Moov Money',
        };
    }
}
