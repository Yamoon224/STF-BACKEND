<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class MfaService
{
    protected Google2FA $engine;

    public function __construct()
    {
        $this->engine = new Google2FA;
    }

    public function generateSecret(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->engine->verifyKey($secret, $code, 4);
    }

    public function qrCodeSvg(string $holderEmail, string $secret): string
    {
        $otpauthUrl = $this->engine->getQRCodeUrl('STF', $holderEmail, $secret);

        $renderer = new ImageRenderer(new RendererStyle(240), new SvgImageBackEnd);
        $writer = new Writer($renderer);

        return $writer->writeString($otpauthUrl);
    }

    public function otpAuthUrl(string $holderEmail, string $secret): string
    {
        return $this->engine->getQRCodeUrl('STF', $holderEmail, $secret);
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }
}
