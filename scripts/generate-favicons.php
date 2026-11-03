<?php

/**
 * Generate favicon assets from the Scale-Up logo.
 *
 * Usage: php scripts/generate-favicons.php
 */
$source = __DIR__.'/../public/assets/Scale-Up_Logo.png';
$public = __DIR__.'/../public';

$info = getimagesize($source);
$logo = imagecreatefrompng($source);

$logoWidth = $info[0];
$logoHeight = $info[1];

/**
 * Render the logo centred on a transparent square canvas at the given size.
 */
function renderIcon(GdImage $logo, int $logoWidth, int $logoHeight, int $size, float $padding = 0.06): GdImage
{
    $canvas = imagecreatetruecolor($size, $size);

    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
    imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);
    imagealphablending($canvas, true);

    $inner = (int) round($size * (1 - $padding * 2));
    $scale = min($inner / $logoWidth, $inner / $logoHeight);
    $targetWidth = (int) round($logoWidth * $scale);
    $targetHeight = (int) round($logoHeight * $scale);

    $offsetX = (int) round(($size - $targetWidth) / 2);
    $offsetY = (int) round(($size - $targetHeight) / 2);

    imagecopyresampled($canvas, $logo, $offsetX, $offsetY, 0, 0, $targetWidth, $targetHeight, $logoWidth, $logoHeight);

    imagesavealpha($canvas, true);

    return $canvas;
}

$targets = [
    'favicon-16x16.png' => 16,
    'favicon-32x32.png' => 32,
    'favicon-48x48.png' => 48,
    'favicon-128x128.png' => 128,
    'apple-touch-icon.png' => 180,
    'favicon-512x512.png' => 512,
];

$payloads = [];

foreach ($targets as $filename => $size) {
    $icon = renderIcon($logo, $logoWidth, $logoHeight, $size);
    $path = $public.'/'.$filename;
    imagepng($icon, $path, 9);
    imagedestroy($icon);

    if (in_array($filename, ['favicon-16x16.png', 'favicon-32x32.png', 'favicon-48x48.png'], true)) {
        $payloads[$filename] = file_get_contents($path);
    }

    echo "wrote {$filename} ({$size}x{$size})".PHP_EOL;
}

/**
 * Build an ICO container holding the PNG payloads.
 */
function buildIco(array $payloads): string
{
    $sizes = [16, 32, 48];
    $images = [];
    $offset = 6 + (16 * count($sizes));

    foreach ($sizes as $size) {
        $data = $payloads["favicon-{$size}x{$size}.png"];
        $images[] = [
            'width' => $size,
            'height' => $size,
            'data' => $data,
            'offset' => $offset,
        ];
        $offset += strlen($data);
    }

    // ICONDIR: reserved (2), type (2), image count (2) = 6 bytes
    $ico = pack('vvv', 0, 1, count($images));

    // ICONDIRENTRY: width, height, palette, reserved, planes, bit count, size, offset
    foreach ($images as $image) {
        $ico .= pack('CCCC', $image['width'], $image['height'], 0, 0);
        $ico .= pack('vv', 1, 32);
        $ico .= pack('VV', strlen($image['data']), $image['offset']);
    }

    foreach ($images as $image) {
        $ico .= $image['data'];
    }

    return $ico;
}

file_put_contents($public.'/favicon.ico', buildIco($payloads));
echo 'wrote favicon.ico'.PHP_EOL;

/**
 * Write an optimized mark for in-page branding (header, footer).
 */
$mark = renderIcon($logo, $logoWidth, $logoHeight, 256, 0.02);
imagepng($mark, $public.'/assets/scale-up-mark.png', 9);
imagedestroy($mark);
echo 'wrote assets/scale-up-mark.png'.PHP_EOL;

/**
 * Write the white variant of the mark for dark surfaces.
 */
$whiteSource = $public.'/assets/Scale-Up Logo_White.png';

if (is_file($whiteSource)) {
    $whiteInfo = getimagesize($whiteSource);
    $whiteLogo = imagecreatefrompng($whiteSource);

    $whiteMark = renderIcon($whiteLogo, $whiteInfo[0], $whiteInfo[1], 256, 0.02);
    imagepng($whiteMark, $public.'/assets/scale-up-mark-white.png', 9);
    imagedestroy($whiteMark);
    imagedestroy($whiteLogo);

    echo 'wrote assets/scale-up-mark-white.png'.PHP_EOL;
}

/**
 * Write an optimized letterhead strip for the footer.
 */
$letterheadSource = $public.'/assets/Scale-Up_Letterhead.png';
$letterheadInfo = getimagesize($letterheadSource);
$letterhead = imagecreatefrompng($letterheadSource);

$targetWidth = 900;
$targetHeight = (int) round($letterheadInfo[1] * ($targetWidth / $letterheadInfo[0]));

$strip = imagecreatetruecolor($targetWidth, $targetHeight);
imagealphablending($strip, false);
imagesavealpha($strip, true);
$transparent = imagecolorallocatealpha($strip, 255, 255, 255, 127);
imagefilledrectangle($strip, 0, 0, $targetWidth, $targetHeight, $transparent);
imagealphablending($strip, true);

imagecopyresampled(
    $strip,
    $letterhead,
    0,
    0,
    0,
    0,
    $targetWidth,
    $targetHeight,
    $letterheadInfo[0],
    $letterheadInfo[1]
);

imagesavealpha($strip, true);
imagepng($strip, $public.'/assets/scale-up-letterhead.png', 9);
imagedestroy($strip);
imagedestroy($letterhead);
echo 'wrote assets/scale-up-letterhead.png'.PHP_EOL;

/**
 * Build a self-contained SVG favicon from the 128px render.
 */
$svgBase64 = base64_encode(file_get_contents($public.'/favicon-128x128.png'));

file_put_contents($public.'/favicon.svg', <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="128" height="128">
    <image href="data:image/png;base64,{$svgBase64}" x="0" y="0" width="128" height="128"/>
</svg>
SVG);

echo 'wrote favicon.svg'.PHP_EOL;

imagedestroy($logo);
