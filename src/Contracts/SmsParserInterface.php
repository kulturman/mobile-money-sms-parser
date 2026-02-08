<?php

declare(strict_types=1);

namespace Kulturman\MobileMoneyParser\Contracts;

use Kulturman\MobileMoneyParser\DTOs\ParsedSmsDTO;
use Kulturman\MobileMoneyParser\Exceptions\SmsParsingException;

interface SmsParserInterface
{
    /**
     * @throws SmsParsingException
     */
    public function parse(string $smsText): ParsedSmsDTO;
}
