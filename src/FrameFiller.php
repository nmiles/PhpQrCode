<?php

declare(strict_types=1);

namespace PhpQrCode;

class FrameFiller
{
    public int $x;

    public int $y;

    public int $dir = -1;

    public int $bit = -1;

    public function __construct(
        public $width,
        public $frame
    ) {
        $this->x = $this->width - 1;
        $this->y = $this->width - 1;
    }

    public function setFrameAt($at, $val)
    {
        $this->frame[$at['y']][$at['x']] = chr($val);
    }

    public function next()
    {
        do {

            if ($this->bit == -1) {
                $this->bit = 0;
                return [
                    'x' => $this->x,
                    'y' => $this->y,
                ];
            }

            $x = $this->x;
            $y = $this->y;
            $w = $this->width;

            if ($this->bit == 0) {
                $x--;
                $this->bit++;
            } else {
                $x++;
                $y += $this->dir;
                $this->bit--;
            }

            if ($this->dir < 0) {
                if ($y < 0) {
                    $y = 0;
                    $x -= 2;
                    $this->dir = 1;
                    if ($x == 6) {
                        $x--;
                        $y = 9;
                    }
                }
            } elseif ($y == $w) {
                $y = $w - 1;
                $x -= 2;
                $this->dir = -1;
                if ($x == 6) {
                    $x--;
                    $y -= 8;
                }
            }

            if ($x < 0 || $y < 0) {
                return null;
            }

            $this->x = $x;
            $this->y = $y;

        } while (ord($this->frame[$y][$x]) & 0x80);

        return [
            'x' => $x,
            'y' => $y,
        ];
    }
}
