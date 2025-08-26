<?php

declare(strict_types=1);

namespace PhpQrCode;

class RsBlock
{
    public function __construct(
        public int $dataLength,
        public array $data = [],
        public int $eccLength,
        public array $ecc = [],
        public RsItem $rs
    ) {
        $rs->encode_rs_char($data, $ecc);
        $this->ecc = $ecc;
    }
}
