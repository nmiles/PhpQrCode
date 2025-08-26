<?php

declare(strict_types=1);

namespace PhpQrCode;

class Bitstream
{
    public array $data = [];

    public function size(): int
    {
        return count($this->data);
    }

    public function allocate(int $setLength): int
    {
        $this->data = array_fill(0, $setLength, 0);
        return 0;
    }

    public static function newFromNum(int $bits, int $num): self
    {
        $bstream = new self();
        $bstream->allocate($bits);

        $mask = 1 << ($bits - 1);
        for ($i = 0; $i < $bits; $i++) {
            $bstream->data[$i] = ($num & $mask) !== 0 ? 1 : 0;

            $mask >>= 1;
        }

        return $bstream;
    }

    public static function newFromBytes(int $size, array $data): self
    {
        $bstream = new self();
        $bstream->allocate($size * 8);

        $p = 0;

        for ($i = 0; $i < $size; $i++) {
            $mask = 0x80;
            for ($j = 0; $j < 8; $j++) {
                $bstream->data[$p] = ($data[$i] & $mask) !== 0 ? 1 : 0;

                $p++;
                $mask >>= 1;
            }
        }

        return $bstream;
    }

    public function append(self $arg): int
    {
        if ($arg->size() == 0) {
            return 0;
        }

        if ($this->size() == 0) {
            $this->data = $arg->data;
            return 0;
        }

        $this->data = array_values(array_merge($this->data, $arg->data));

        return 0;
    }

    public function appendNum(int $bits, int $num): int
    {
        if ($bits == 0) {
            return 0;
        }

        $b = self::newFromNum($bits, $num);

        $ret = $this->append($b);
        unset($b);

        return $ret;
    }

    public function appendBytes(int $size, array $data): int
    {
        if ($size == 0) {
            return 0;
        }

        $b = self::newFromBytes($size, $data);

        $ret = $this->append($b);
        unset($b);

        return $ret;
    }

    public function toByte(): array
    {
        $size = $this->size();

        if ($size == 0) {
            return [];
        }

        $data = array_fill(0, (int) (($size + 7) / 8), 0);
        $bytes = (int) ($size / 8);

        $p = 0;

        for ($i = 0; $i < $bytes; $i++) {
            $v = 0;
            for ($j = 0; $j < 8; $j++) {
                $v <<= 1;
                $v |= $this->data[$p];
                $p++;
            }

            $data[$i] = $v;
        }

        if (($size & 7) !== 0) {
            $v = 0;
            for ($j = 0; $j < ($size & 7); $j++) {
                $v <<= 1;
                $v |= $this->data[$p];
                $p++;
            }

            $data[$bytes] = $v;
        }

        return $data;
    }
}
