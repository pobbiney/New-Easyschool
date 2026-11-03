<?php

namespace App\Support;

use App\Models\SchoolSetting;

class LogoPalette
{
    /**
     * @return array{dark: string, accent: string, ink: string}
     */
    public static function fromSchool(?SchoolSetting $school = null): array
    {
        $school = $school ?? SchoolSetting::current();
        $path = $school?->logoFilePath();

        if ($path && is_file($path)) {
            $extracted = self::fromFile($path);
            if ($extracted !== null) {
                return $extracted;
            }
        }

        return [
            'dark' => '#3B0A5C',
            'accent' => '#F5C518',
            'ink' => '#1A0B2E',
        ];
    }

    /**
     * @return array{dark: string, accent: string, ink: string}|null
     */
    public static function fromFile(string $path): ?array
    {
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }

        $source = @imagecreatefromstring($raw);
        if ($source === false) {
            return null;
        }

        $sampleW = 72;
        $sampleH = 72;
        $sample = imagecreatetruecolor($sampleW, $sampleH);
        imagecopyresampled($sample, $source, 0, 0, 0, 0, $sampleW, $sampleH, imagesx($source), imagesy($source));
        imagedestroy($source);

        $buckets = [];

        for ($y = 0; $y < $sampleH; $y++) {
            for ($x = 0; $x < $sampleW; $x++) {
                $rgb = imagecolorat($sample, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $max = max($r, $g, $b);
                $min = min($r, $g, $b);
                $delta = $max - $min;

                if ($max > 245 && $min > 230) {
                    continue;
                }
                if ($max < 28) {
                    continue;
                }
                if ($delta < 40) {
                    continue;
                }

                $key = sprintf('%02x%02x%02x', (int) round($r / 24) * 24, (int) round($g / 24) * 24, (int) round($b / 24) * 24);
                if (! isset($buckets[$key])) {
                    $buckets[$key] = ['n' => 0, 'r' => 0, 'g' => 0, 'b' => 0];
                }
                $buckets[$key]['n']++;
                $buckets[$key]['r'] += $r;
                $buckets[$key]['g'] += $g;
                $buckets[$key]['b'] += $b;
            }
        }

        imagedestroy($sample);

        if ($buckets === []) {
            return null;
        }

        uasort($buckets, fn ($a, $b) => $b['n'] <=> $a['n']);
        $top = array_slice($buckets, 0, 8);

        $colors = [];
        foreach ($top as $bucket) {
            $n = max(1, $bucket['n']);
            $colors[] = [
                'r' => (int) round($bucket['r'] / $n),
                'g' => (int) round($bucket['g'] / $n),
                'b' => (int) round($bucket['b'] / $n),
            ];
        }

        $dark = null;
        $accent = null;
        $darkScore = -1;
        $accentScore = -1;

        foreach ($colors as $c) {
            $lum = (0.2126 * $c['r'] + 0.7152 * $c['g'] + 0.0722 * $c['b']) / 255;
            $sat = self::saturation($c['r'], $c['g'], $c['b']);

            $asDark = $sat * 0.72 + (1 - $lum) * 0.28;
            if ($lum < 0.72 && $asDark > $darkScore) {
                $darkScore = $asDark;
                $dark = $c;
            }

            $asAccent = $sat * 0.4 + $lum * 0.6;
            if ($lum > 0.35 && $asAccent > $accentScore) {
                $accentScore = $asAccent;
                $accent = $c;
            }
        }

        if ($dark === null || $accent === null) {
            return null;
        }

        if (self::distance($dark, $accent) < 40) {
            $accent = self::shift($dark, 70);
        }

        $darkHex = self::hex(self::toLuminance($dark, 0.18));
        $accentHex = self::hex(self::toLuminance($accent, 0.72));
        $inkHex = self::hex(self::toLuminance($dark, 0.10));

        return [
            'dark' => $darkHex,
            'accent' => $accentHex,
            'ink' => $inkHex,
        ];
    }

    private static function saturation(int $r, int $g, int $b): float
    {
        $max = max($r, $g, $b) / 255;
        $min = min($r, $g, $b) / 255;
        if ($max <= 0) {
            return 0;
        }

        return ($max - $min) / $max;
    }

    /**
     * @param  array{r:int,g:int,b:int}  $a
     * @param  array{r:int,g:int,b:int}  $b
     */
    private static function distance(array $a, array $b): float
    {
        return sqrt((($a['r'] - $b['r']) ** 2) + (($a['g'] - $b['g']) ** 2) + (($a['b'] - $b['b']) ** 2));
    }

    /**
     * @param  array{r:int,g:int,b:int}  $c
     * @return array{r:int,g:int,b:int}
     */
    private static function toLuminance(array $c, float $target): array
    {
        $lum = (0.2126 * $c['r'] + 0.7152 * $c['g'] + 0.0722 * $c['b']) / 255;
        if ($lum <= 0.001) {
            return $c;
        }

        $factor = $target / $lum;

        return [
            'r' => max(0, min(255, (int) round($c['r'] * $factor))),
            'g' => max(0, min(255, (int) round($c['g'] * $factor))),
            'b' => max(0, min(255, (int) round($c['b'] * $factor))),
        ];
    }

    /**
     * @param  array{r:int,g:int,b:int}  $c
     * @return array{r:int,g:int,b:int}
     */
    private static function shade(array $c, float $factor): array
    {
        return [
            'r' => max(0, min(255, (int) round($c['r'] * $factor))),
            'g' => max(0, min(255, (int) round($c['g'] * $factor))),
            'b' => max(0, min(255, (int) round($c['b'] * $factor))),
        ];
    }

    /**
     * @param  array{r:int,g:int,b:int}  $c
     * @return array{r:int,g:int,b:int}
     */
    private static function shift(array $c, int $amount): array
    {
        return [
            'r' => max(0, min(255, $c['r'] + $amount)),
            'g' => max(0, min(255, $c['g'] + (int) round($amount * 0.7))),
            'b' => max(0, min(255, $c['b'] - (int) round($amount * 0.4))),
        ];
    }

    /**
     * @param  array{r:int,g:int,b:int}  $c
     */
    private static function hex(array $c): string
    {
        return sprintf('#%02X%02X%02X', $c['r'], $c['g'], $c['b']);
    }
}
