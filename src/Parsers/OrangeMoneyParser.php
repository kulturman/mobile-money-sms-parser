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
        // Remove commas used as thousands separator (e.g. 1,800) but not decimal separator (e.g. 8080,00)
        $smsText = preg_replace('/(\d),(\d{3})(?!\d)/', '$1$2', $smsText);
        // Replace remaining commas between a digit and non-digit with a space (e.g. "57652730,MARIE" -> "57652730 MARIE")
        $smsText = preg_replace('/(\d),(?!\d)/', '$1 ', $smsText);
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
        // Format Orange: paiement de 5 000 FCFA ou 1800.00 FCFA ou 8080,00 FCFA
        if (preg_match('/(\d[\d\s.,]*\d)\s*FCFA/i', $smsText, $matches)) {
            $cleaned = preg_replace('/\s/', '', $matches[1]);
            // Replace decimal comma with dot, then truncate decimals
            $cleaned = str_replace(',', '.', $cleaned);
            return (int) $cleaned;
        }

        throw new SmsParsingException('Unable to extract amount from SMS');
    }

    private function extractReference(string $smsText): ?string
    {
        // Support pour "Ref:", "Reference:" et "Motif:" (format local BF)
        if (preg_match('/(?:Ref|Reference|Motif)\s*:\s*([A-Za-z0-9\-_]+)/i', $smsText, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractTransactionId(string $smsText): ?string
    {
        // Format Orange: Trans ID: CI241227.1234.A00001
        if (preg_match('/Trans ID:\s*([A-Z0-9]+(?:\.[A-Z0-9]+)*)/i', $smsText, $matches)) {
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

        // Format alternatif: du 57652730 ou de 22670123456
        if (preg_match('/(?:de|du|depuis)\s+(\d{8,14})/i', $smsText, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
