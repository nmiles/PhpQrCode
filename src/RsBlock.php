<?php

namespace PhpQrCode;

/**
 * Class RsBlock
 * @package PhpQrCode
 */
class RsBlock
{
    public $dataLength;
    public $data = [];
    public $eccLength;
    public $ecc = [];

    public function __construct($dl, $data, $el, &$ecc, RsItem $rs)
    {
        $rs->encode_rs_char($data, $ecc);

        $this->dataLength = $dl;
        $this->data = $data;
        $this->eccLength = $el;
        $this->ecc = $ecc;
    }
}

