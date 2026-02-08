<?php

declare(strict_types=1);

namespace Kulturman\MobileMoneyParser\Parsers;

use Kulturman\MobileMoneyParser\Contracts\SmsParserInterface;
use Kulturman\MobileMoneyParser\DTOs\ParsedSmsDTO;
use Kulturman\MobileMoneyParser\Exceptions\SmsParsingException;

class OrangeMoneyParser implements SmsParserInterface
{
    public function parse(string $smsText): ParsedSmsDTO
    {
        $amount = $this->extractAmount($smsText);
        $reference = $this->extractReference($smsText);
        $transactionId = $this->extractTransactionId($smsText);
        $senderPhone = $this->extractSenderPhone($smsText);

        return new ParsedSmsDTO(
            amount: $amount,
            reference: $reference,
            transactionId: $transactionId,
            senderPhone: $senderPhone,
        );
    }

    private function extractAmount(string $smsText): int
    {
        // Format Orange: paiement de 5 000 FCFA ou 5,000 FCFA
        if (preg_match('/(\d[\d\s,]*)\s*FCFA/i', $smsText, $matches)) {
            return (int) preg_replace('/[\s,]/', '', $matches[1]);
        }

        throw new SmsParsingException('Unable to extract amount from SMS');
    }

    private function extractReference(string $smsText): string
    {
        // Support pour "Ref:", "Reference:" et "Motif:" (format local BF)
        if (preg_match('/(?:Ref|Reference|Motif)\s*:\s*([A-Za-z0-9\-_]+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        throw new SmsParsingException('Unable to extract reference from SMS');
    }

    private function extractTransactionId(string $smsText): ?string
    {
        // Format Orange: Trans ID: CI241227.1234.A00001
        if (preg_match('/Trans ID:\s*([A-Z0-9\.]+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        // Format alternatif: ID: TXN123 ou Transaction: XXX
        if (preg_match('/(?:ID|Transaction)\s*:\s*([A-Za-z0-9]+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractSenderPhone(string $smsText): ?string
    {
        // Format Orange: du numero 22670123456
        if (preg_match('/du numero\s*(\d+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        // Format alternatif: de 22670123456
        if (preg_match('/(?:de|depuis)\s+(\d{11,14})/i', $smsText, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
