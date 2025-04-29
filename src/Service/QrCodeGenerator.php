<?php

namespace App\Service;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;

class QrCodeGenerator 
{
    public function createQrCode(string $data): string
    {
        $result = Builder::create()
            ->writer(new SvgWriter())
            ->writerOptions([])
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            ->size(200)
            ->margin(10)
            ->validateResult(false)
            ->build();

        return $result->getString();
    }
} 