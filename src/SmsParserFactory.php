<?php

declare(strict_types=1);

namespace Kulturman\MobileMoneyParser;

use Kulturman\MobileMoneyParser\Contracts\SmsParserInterface;
use Kulturman\MobileMoneyParser\Enums\MobileMoneyProvider;
use Kulturman\MobileMoneyParser\Parsers\MoovMoneyParser;
use Kulturman\MobileMoneyParser\Parsers\OrangeMoneyParser;

class SmsParserFactory
{
    public function getParser(string $senderId): ?SmsParserInterface
    {
        $provider = $this->getProvider($senderId);

        if ($provider === null) {
            return null;
        }

        return match ($provider) {
            MobileMoneyProvider::ORANGE_MONEY => new OrangeMoneyParser,
            MobileMoneyProvider::MOOV_MONEY => new MoovMoneyParser,
        };
    }

    public function getProvider(string $senderId): ?MobileMoneyProvider
    {
        return MobileMoneyProvider::fromSenderId($senderId);
    }

    public function supportsProvider(string $senderId): bool
    {
        return $this->getProvider($senderId) !== null;
    }
}
