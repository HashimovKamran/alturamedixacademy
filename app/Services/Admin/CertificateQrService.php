<?php

namespace App\Services\Admin;

use App\Models\Certificate;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;
use Throwable;

class CertificateQrService
{
    public function previewAspectRatio(?Certificate $certificate): ?float
    {
        $sourcePath = trim((string) ($certificate?->file_path ?? ''));
        if ($sourcePath === '') {
            return null;
        }

        $sourceAbsolute = public_path(ltrim($sourcePath, '/'));
        if (! is_file($sourceAbsolute)) {
            return null;
        }

        try {
            $extension = strtolower(pathinfo($sourceAbsolute, PATHINFO_EXTENSION));
            if ($extension === 'pdf') {
                $pdf = $this->newPdf();
                $pdf->setSourceFile($sourceAbsolute);
                $template = $pdf->importPage(1);
                $size = $pdf->getTemplateSize($template);
                $width = (float) $size['width'];
                $height = (float) $size['height'];
            } else {
                [$width, $height] = $this->documentSize($sourceAbsolute, $extension);
            }

            return $width > 0 && $height > 0 ? $width / $height : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function sync(Certificate $certificate): void
    {
        if (! $certificate->cert_no) {
            return;
        }

        $qrPath = $this->generateQr($certificate);
        $documentPath = $this->generateDocumentWithQr($certificate, $qrPath);

        $certificate->forceFill([
            'qr_code_path' => $qrPath,
            'qr_document_path' => $documentPath,
        ])->saveQuietly();
    }

    private function generateQr(Certificate $certificate): string
    {
        $directory = public_path('uploads/certificates/qr');
        File::ensureDirectoryExists($directory);

        $relativePath = 'uploads/certificates/qr/' . $this->baseName($certificate) . '.svg';
        $url = route('certificates.index', [
            'lang' => $certificate->lang_code ?: 'az',
            'cert_no' => $certificate->cert_no,
        ]);

        $options = new QROptions([
            'outputType' => 'svg',
            'outputBase64' => false,
            'scale' => 8,
        ]);

        File::put(public_path($relativePath), (new QRCode($options))->render($url));

        return $relativePath;
    }

    private function generateDocumentWithQr(Certificate $certificate, string $qrPath): ?string
    {
        $sourcePath = trim((string) $certificate->file_path);
        if ($sourcePath === '') {
            return null;
        }

        $sourceAbsolute = public_path(ltrim($sourcePath, '/'));
        if (! is_file($sourceAbsolute)) {
            return null;
        }

        $extension = strtolower(pathinfo($sourceAbsolute, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            return $this->generatePdfWithQr($certificate, $sourceAbsolute, $qrPath);
        }

        if (! in_array($extension, ['svg', 'png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
            return null;
        }

        return $this->generateImagePdfWithQr($certificate, $sourceAbsolute, $extension, $qrPath);
    }

    private function generateImagePdfWithQr(Certificate $certificate, string $sourceAbsolute, string $extension, string $qrPath): ?string
    {
        $relativePath = 'uploads/certificates/with-qr/' . $this->baseName($certificate) . '.pdf';
        File::ensureDirectoryExists(public_path('uploads/certificates/with-qr'));
        $this->deleteLegacySvg($certificate);

        try {
            [$width, $height] = $this->documentSize($sourceAbsolute, $extension);
            $orientation = $width > $height ? 'L' : 'P';
            $pdf = $this->newPdf($orientation, 'pt', [$width, $height]);
            $pdf->AddPage($orientation, [$width, $height]);

            $temporaryImage = null;

            if ($extension === 'svg') {
                $pdf->ImageSVG($sourceAbsolute, 0, 0, $width, $height, '', '', '', 0, false);
            } else {
                [$imagePath, $temporaryImage] = $this->imageForTcpdf($sourceAbsolute, $extension);
                $pdf->Image($imagePath, 0, 0, $width, $height, '', '', '', false, 300, '', false, false, 0, false, false, true);
            }

            [$x, $y, $qrSize] = $this->qrPlacement($certificate, $width, $height);
            $pdf->ImageSVG('@' . File::get(public_path($qrPath)), $x, $y, $qrSize, $qrSize);
            $pdf->Output(public_path($relativePath), 'F');

            if ($temporaryImage) {
                File::delete($temporaryImage);
            }

            return $relativePath;
        } catch (Throwable) {
            if (! empty($temporaryImage)) {
                File::delete($temporaryImage);
            }

            return null;
        }
    }

    private function generatePdfWithQr(Certificate $certificate, string $sourceAbsolute, string $qrPath): ?string
    {
        $relativePath = 'uploads/certificates/with-qr/' . $this->baseName($certificate) . '.pdf';
        File::ensureDirectoryExists(public_path('uploads/certificates/with-qr'));
        $this->deleteLegacySvg($certificate);

        try {
            $pdf = $this->newPdf();
            $pageCount = $pdf->setSourceFile($sourceAbsolute);
            $qrSvg = File::get(public_path($qrPath));

            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                $template = $pdf->importPage($pageNumber);
                $size = $pdf->getTemplateSize($template);
                $width = (float) $size['width'];
                $height = (float) $size['height'];
                $orientation = $width > $height ? 'L' : 'P';

                $pdf->AddPage($orientation, [$width, $height]);
                $pdf->useTemplate($template, 0, 0, $width, $height, true);

                if ($pageNumber !== 1) {
                    continue;
                }

                [$x, $y, $qrSize] = $this->qrPlacement($certificate, $width, $height);
                $pdf->ImageSVG('@' . $qrSvg, $x, $y, $qrSize, $qrSize);
            }

            $pdf->Output(public_path($relativePath), 'F');

            return $relativePath;
        } catch (Throwable) {
            return null;
        }
    }

    private function newPdf(string $orientation = 'P', string $unit = 'mm', array|string $format = 'A4'): Fpdi
    {
        $pdf = new Fpdi($orientation, $unit, $format);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setImageScale(1);

        return $pdf;
    }

    private function qrPlacement(Certificate $certificate, float $width, float $height): array
    {
        $qrPercentSize = $this->clampFloat($certificate->qr_size ?? 16, 8, 35);
        $qrSize = $width * ($qrPercentSize / 100);
        $qrSize = min($qrSize, $width, $height);
        $x = $this->percentToTopLeft($this->clampFloat($certificate->qr_x ?? 72, 0, 100), $width, $qrSize);
        $y = $this->percentToTopLeft($this->clampFloat($certificate->qr_y ?? 72, 0, 100), $height, $qrSize);

        return [$x, $y, $qrSize];
    }

    private function documentSize(string $sourceAbsolute, string $extension): array
    {
        if ($extension === 'svg') {
            return $this->svgSize($sourceAbsolute);
        }

        $size = @getimagesize($sourceAbsolute);
        if (is_array($size) && isset($size[0], $size[1]) && (int) $size[0] > 0 && (int) $size[1] > 0) {
            return [(float) $size[0], (float) $size[1]];
        }

        return [1200.0, 850.0];
    }

    private function imageForTcpdf(string $sourceAbsolute, string $extension): array
    {
        if ($extension !== 'png') {
            return [$sourceAbsolute, null];
        }

        $flattened = $this->flattenPngAlpha($sourceAbsolute);

        return [$flattened ?: $sourceAbsolute, $flattened];
    }

    private function flattenPngAlpha(string $sourceAbsolute): ?string
    {
        $content = File::get($sourceAbsolute);
        if (! str_starts_with($content, "\x89PNG\r\n\x1a\n")) {
            return null;
        }

        $offset = 8;
        $width = 0;
        $height = 0;
        $bitDepth = 0;
        $colorType = 0;
        $interlace = 0;
        $idat = '';

        while ($offset + 8 <= strlen($content)) {
            $length = unpack('N', substr($content, $offset, 4))[1];
            $type = substr($content, $offset + 4, 4);
            $data = substr($content, $offset + 8, $length);
            $offset += 12 + $length;

            if ($type === 'IHDR') {
                $header = unpack('Nwidth/Nheight/CbitDepth/CcolorType/Ccompression/Cfilter/Cinterlace', $data);
                $width = (int) $header['width'];
                $height = (int) $header['height'];
                $bitDepth = (int) $header['bitDepth'];
                $colorType = (int) $header['colorType'];
                $interlace = (int) $header['interlace'];
            } elseif ($type === 'IDAT') {
                $idat .= $data;
            } elseif ($type === 'IEND') {
                break;
            }
        }

        if ($width <= 0 || $height <= 0 || $bitDepth !== 8 || $interlace !== 0 || ! in_array($colorType, [4, 6], true)) {
            return null;
        }

        $decoded = zlib_decode($idat);
        if ($decoded === false) {
            return null;
        }

        $bytesPerPixel = $colorType === 6 ? 4 : 2;
        $rowLength = $width * $bytesPerPixel;
        $cursor = 0;
        $previous = array_fill(0, $rowLength, 0);
        $rgbRows = '';

        for ($row = 0; $row < $height; $row++) {
            if ($cursor >= strlen($decoded)) {
                return null;
            }

            $filter = ord($decoded[$cursor]);
            $cursor++;
            $raw = array_map('ord', str_split(substr($decoded, $cursor, $rowLength)));
            $cursor += $rowLength;
            $scanline = $this->unfilterPngRow($filter, $raw, $previous, $bytesPerPixel);
            $previous = $scanline;
            $rgbRows .= "\x00" . $this->flattenPngRowToRgb($scanline, $colorType);
        }

        $directory = storage_path('app/tmp/certificates');
        File::ensureDirectoryExists($directory);
        $target = $directory . '/' . pathinfo($sourceAbsolute, PATHINFO_FILENAME) . '-flat-' . Str::random(8) . '.png';
        File::put($target, $this->pngFromRgbRows($width, $height, $rgbRows));

        return $target;
    }

    private function unfilterPngRow(int $filter, array $raw, array $previous, int $bytesPerPixel): array
    {
        $row = [];
        $length = count($raw);

        for ($i = 0; $i < $length; $i++) {
            $left = $i >= $bytesPerPixel ? $row[$i - $bytesPerPixel] : 0;
            $up = $previous[$i] ?? 0;
            $upperLeft = $i >= $bytesPerPixel ? ($previous[$i - $bytesPerPixel] ?? 0) : 0;

            $value = match ($filter) {
                1 => $raw[$i] + $left,
                2 => $raw[$i] + $up,
                3 => $raw[$i] + (int) floor(($left + $up) / 2),
                4 => $raw[$i] + $this->paeth($left, $up, $upperLeft),
                default => $raw[$i],
            };

            $row[$i] = $value & 0xff;
        }

        return $row;
    }

    private function flattenPngRowToRgb(array $row, int $colorType): string
    {
        $rgb = '';
        $step = $colorType === 6 ? 4 : 2;

        for ($i = 0; $i < count($row); $i += $step) {
            if ($colorType === 6) {
                $red = $row[$i];
                $green = $row[$i + 1];
                $blue = $row[$i + 2];
                $alpha = $row[$i + 3];
            } else {
                $red = $green = $blue = $row[$i];
                $alpha = $row[$i + 1];
            }

            $rgb .= chr($this->alphaBlendOnWhite($red, $alpha));
            $rgb .= chr($this->alphaBlendOnWhite($green, $alpha));
            $rgb .= chr($this->alphaBlendOnWhite($blue, $alpha));
        }

        return $rgb;
    }

    private function alphaBlendOnWhite(int $channel, int $alpha): int
    {
        return (int) round(($channel * $alpha + 255 * (255 - $alpha)) / 255);
    }

    private function pngFromRgbRows(int $width, int $height, string $rgbRows): string
    {
        $png = "\x89PNG\r\n\x1a\n";
        $png .= $this->pngChunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0));
        $png .= $this->pngChunk('IDAT', gzcompress($rgbRows, 9));
        $png .= $this->pngChunk('IEND', '');

        return $png;
    }

    private function pngChunk(string $type, string $data): string
    {
        return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
    }

    private function paeth(int $left, int $up, int $upperLeft): int
    {
        $p = $left + $up - $upperLeft;
        $pa = abs($p - $left);
        $pb = abs($p - $up);
        $pc = abs($p - $upperLeft);

        if ($pa <= $pb && $pa <= $pc) {
            return $left;
        }

        return $pb <= $pc ? $up : $upperLeft;
    }

    private function svgSize(string $sourceAbsolute): array
    {
        $content = File::get($sourceAbsolute);
        $width = $this->extractSvgNumber($content, 'width');
        $height = $this->extractSvgNumber($content, 'height');

        if ($width > 0 && $height > 0) {
            return [$width, $height];
        }

        if (preg_match('/viewBox=["\']\s*[-\d.]+\s+[-\d.]+\s+([\d.]+)\s+([\d.]+)\s*["\']/i', $content, $matches)) {
            return [(float) $matches[1], (float) $matches[2]];
        }

        return [1200.0, 850.0];
    }

    private function extractSvgNumber(string $content, string $attribute): float
    {
        if (! preg_match('/\b' . preg_quote($attribute, '/') . '=["\']([\d.]+)/i', $content, $matches)) {
            return 0.0;
        }

        return (float) $matches[1];
    }

    private function percentToTopLeft(float $percent, float $axis, float $size): float
    {
        $coordinate = $axis * ($this->clampFloat($percent, 0, 100) / 100);

        return min(max(0.0, $coordinate), max(0.0, $axis - $size));
    }

    private function baseName(Certificate $certificate): string
    {
        return (Str::slug((string) $certificate->cert_no) ?: 'certificate') . '-' . $certificate->getKey();
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    private function clampFloat(mixed $value, float $min, float $max): float
    {
        $number = is_numeric($value) ? (float) $value : $min;

        return max($min, min($max, $number));
    }

    private function num(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private function deleteLegacySvg(Certificate $certificate): void
    {
        $legacyPath = public_path('uploads/certificates/with-qr/' . $this->baseName($certificate) . '.svg');
        if (is_file($legacyPath)) {
            File::delete($legacyPath);
        }
    }
}
