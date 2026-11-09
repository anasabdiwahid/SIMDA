<?php
/**
 * SIMDA brand kit builder: writes every logo version as SVG from one set of vector shapes.
 * Run: C:\xampp\php\php.exe brand/_src/build.php
 */
declare(strict_types=1);

$out = dirname(__DIR__) . '/svg';

// Brand colours
const GREEN = '#123B32';      // primary (system) green
const GREEN_LOGO_TOP = '#16704D';
const GREEN_LOGO_BOT = '#0B3D2C';
const GOLD = '#D6B66C';       // primary (system) gold
const GOLD_LIGHT = '#F6DB95';
const GOLD_MID = '#D6A945';
const GOLD_DEEP = '#B8862B';

// Shapes: [id, kind, svg element with {F} for the fill/stroke paint]
// kind: green = letter body, gold = accents, blend = gold-to-green middle bar of the S
$S = [
    ['S-top', 'green', '<rect id="S-top" x="150" y="100" width="112" height="38" fill="{F}"/>'],
    ['S-lower', 'green', '<path id="S-lower" d="M120 170H214A25.5 25.5 0 0 1 214 221H58" fill="none" stroke="{F}" stroke-width="38"/>'],
    ['S-foot', 'green', '<path id="S-foot" d="M64 202V240H36L56 202Z" fill="{F}"/>'],
    ['S-middle', 'blend', '<path id="S-middle" d="M108 170H206" fill="none" stroke="{F}" stroke-width="38"/>'],
    ['S-sweep', 'gold', '<path id="S-sweep" d="M306 80C190 70 76 96 70 152C68 178 86 189 112 189H124V151H117C104 151 104 138 118 138H152C160 128 160 104 172 100C206 92 254 84 306 80Z" fill="{F}"/>'],
];
$REST = [
    ['I', 'green', '<rect id="I" x="300" y="100" width="40" height="140" fill="{F}"/>'],
    ['I-dot', 'gold', '<rect id="I-dot" x="307" y="59" width="26" height="26" rx="5" transform="rotate(45 320 72)" fill="{F}"/>'],
    ['M', 'green', '<path id="M" d="M370 240V100H412L465 165L518 100H560V240H522V158L465 226L408 158V240Z" fill="{F}"/>'],
    ['D', 'green', '<path id="D" fill-rule="evenodd" d="M590 100H690A70 70 0 0 1 690 240H590ZM628 136V204H688A34 34 0 0 0 688 136Z" fill="{F}"/>'],
    ['A', 'green', '<path id="A" d="M782 240L850 100H892L960 240H918L871 141L824 240Z" fill="{F}"/>'],
    ['A-swoosh', 'gold', '<path id="A-swoosh" d="M792 232C832 172 902 158 994 182C906 170 846 186 808 236Z" fill="{F}"/>'],
];

/** Paint for each kind, per colour scheme. Gradients are referenced by id. */
function paints(string $scheme): array
{
    return match ($scheme) {
        'color' => ['green' => 'url(#g-green)', 'gold' => 'url(#g-gold)', 'blend' => 'url(#g-blend)'],
        'reverse' => ['green' => '#FFFFFF', 'gold' => 'url(#g-gold)', 'blend' => 'url(#g-blend-white)'],
        'flat' => ['green' => GREEN, 'gold' => GOLD, 'blend' => 'url(#g-blend-flat)'],
        'mono-green' => ['green' => GREEN, 'gold' => GREEN, 'blend' => GREEN],
        'black' => ['green' => '#000000', 'gold' => '#000000', 'blend' => '#000000'],
        'white' => ['green' => '#FFFFFF', 'gold' => '#FFFFFF', 'blend' => '#FFFFFF'],
    };
}

function defs(string $p = ''): string
{
    return '<defs>'
        . '<linearGradient id="' . $p . 'g-green" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' . GREEN_LOGO_TOP . '"/><stop offset="1" stop-color="' . GREEN_LOGO_BOT . '"/></linearGradient>'
        . '<linearGradient id="' . $p . 'g-gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="' . GOLD_LIGHT . '"/><stop offset="0.55" stop-color="' . GOLD_MID . '"/><stop offset="1" stop-color="' . GOLD_DEEP . '"/></linearGradient>'
        . '<linearGradient id="' . $p . 'g-blend" gradientUnits="userSpaceOnUse" x1="112" y1="0" x2="205" y2="0"><stop offset="0" stop-color="#CFA040"/><stop offset="0.3" stop-color="#C99A3A"/><stop offset="1" stop-color="#11583F"/></linearGradient>'
        . '<linearGradient id="' . $p . 'g-blend-white" gradientUnits="userSpaceOnUse" x1="112" y1="0" x2="205" y2="0"><stop offset="0" stop-color="#CFA040"/><stop offset="0.3" stop-color="#D9B256"/><stop offset="1" stop-color="#FFFFFF"/></linearGradient>'
        . '<linearGradient id="' . $p . 'g-blend-flat" gradientUnits="userSpaceOnUse" x1="112" y1="0" x2="205" y2="0"><stop offset="0" stop-color="' . GOLD . '"/><stop offset="0.3" stop-color="' . GOLD . '"/><stop offset="1" stop-color="' . GREEN . '"/></linearGradient>'
        . '</defs>';
}

/** Shapes painted for a scheme. $p prefixes gradient ids so several logos can share one file. */
function shapes(array $list, string $scheme, string $p = ''): string
{
    $paint = paints($scheme);
    $s = '';
    foreach ($list as [$id, $kind, $el]) {
        $f = str_replace('url(#', 'url(#' . $p, $paint[$kind]);
        $el = str_replace(['{F}', 'id="'], [$f, 'id="' . $p], $el);
        $s .= '    ' . $el . "\n";
    }
    return $s;
}

function svg(string $viewBox, string $w, string $h, string $title, string $body): string
{
    return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . $viewBox . '" width="' . $w . '" height="' . $h . '">' . "\n"
        . '  <title>' . htmlspecialchars($title) . '</title>' . "\n"
        . '  ' . defs() . "\n"
        . $body
        . '</svg>' . "\n";
}

$all = array_merge($S, $REST);
$logoBox = '20 50 990 205';

// 1. Wordmark logos
$versions = [
    'simda-logo-primary' => ['color', 'SIMDA logo, full colour (light backgrounds)'],
    'simda-logo-reverse' => ['reverse', 'SIMDA logo, reverse (dark / green backgrounds)'],
    'simda-logo-flat' => ['flat', 'SIMDA logo, flat brand colours (print, embroidery, signs)'],
    'simda-logo-mono-green' => ['mono-green', 'SIMDA logo, one colour green'],
    'simda-logo-black' => ['black', 'SIMDA logo, one colour black (stamps, fax, newspaper)'],
    'simda-logo-white' => ['white', 'SIMDA logo, one colour white (on photos and dark colours)'],
];
foreach ($versions as $file => [$scheme, $title]) {
    file_put_contents("$out/$file.svg", svg($logoBox, '990', '205', $title, '  <g id="logo">' . "\n" . shapes($all, $scheme) . "  </g>\n"));
}

// 2. Logo with tagline (two lines under the wordmark)
$tag = function (string $scheme): string {
    $ink = in_array($scheme, ['reverse', 'white'], true) ? '#FFFFFF' : GREEN;
    $gold = match ($scheme) { 'mono-green' => GREEN, 'black' => '#000000', 'white' => '#FFFFFF', default => GOLD_DEEP };
    if ($scheme === 'reverse') {
        $gold = GOLD;
    }
    return '  <g id="tagline" font-family="Montserrat, Arial, Helvetica, sans-serif" font-weight="700" text-anchor="middle">' . "\n"
        . '    <text x="515" y="300" font-size="31" letter-spacing="7.2" fill="' . $ink . '">SOMALI ISLAMIC MICROFINANCE</text>' . "\n"
        . '    <text x="515" y="342" font-size="31" letter-spacing="7.2" fill="' . $gold . '">DEVELOPMENT ASSOCIATION</text>' . "\n"
        . "  </g>\n";
};
foreach (['color' => 'primary', 'reverse' => 'reverse', 'mono-green' => 'mono-green', 'black' => 'black'] as $scheme => $name) {
    file_put_contents(
        "$out/simda-logo-tagline-$name.svg",
        svg('20 50 990 310', '990', '310', "SIMDA logo with tagline ($name)", '  <g id="logo">' . "\n" . shapes($all, $scheme) . "  </g>\n" . $tag($scheme))
    );
}

// 3. S mark (symbol only)
foreach (['color' => 'primary', 'reverse' => 'reverse', 'mono-green' => 'mono-green', 'black' => 'black'] as $scheme => $name) {
    file_put_contents("$out/simda-mark-$name.svg", svg('21 10 300 300', '300', '300', "SIMDA S mark ($name)", '  <g id="mark">' . "\n" . shapes($S, $scheme) . "  </g>\n"));
}

// 4. App icon (green tile, white + gold S)
$icon = function (string $tile, string $scheme): string {
    return '  <rect id="tile" width="512" height="512" rx="112" fill="' . $tile . '"/>' . "\n"
        . '  <g id="mark" transform="translate(256 262) scale(1.36) translate(-171 -160)">' . "\n" . shapes($GLOBALS['S'], $scheme) . "  </g>\n";
};
file_put_contents("$out/simda-app-icon.svg", svg('0 0 512 512', '512', '512', 'SIMDA app icon', $icon(GREEN, 'reverse')));
file_put_contents("$out/simda-app-icon-light.svg", svg('0 0 512 512', '512', '512', 'SIMDA app icon (light)', $icon('#FFFFFF', 'color')));

// 5. Master sheet: every version on one canvas, for the Illustrator file (one artboard per group).
$cells = [
    // [id, x, y, w, h, background, scheme or special, title]
    ['primary', 0, 0, 1100, 320, '#FFFFFF', 'color', 'Full colour'],
    ['reverse', 1140, 0, 1100, 320, GREEN, 'reverse', 'Reverse'],
    ['flat', 0, 360, 1100, 320, '#FFFFFF', 'flat', 'Flat colours'],
    ['mono-green', 1140, 360, 1100, 320, '#FFFFFF', 'mono-green', 'One colour green'],
    ['black', 0, 720, 1100, 320, '#FFFFFF', 'black', 'Black'],
    ['white', 1140, 720, 1100, 320, '#1B1B1B', 'white', 'White'],
];
$body = '';
foreach ($cells as [$id, $x, $y, $w, $h, $bg, $scheme, $title]) {
    $p = $id . '-';
    $body .= '  <g id="AB-' . $id . '">' . "\n"
        . '    <rect id="' . ($bg === '#FFFFFF' ? 'bg-drop-' : 'bg-keep-') . $id . '" x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" fill="' . $bg . '"/>' . "\n"
        . '    ' . defs($p) . "\n"
        . '    <g transform="translate(' . ($x + 35) . ' ' . ($y + 38) . ')">' . "\n" . shapes($all, $scheme, $p) . "    </g>\n"
        . "  </g>\n";
}
// Tagline lockup, mark, app icon
$body .= '  <g id="AB-tagline"><rect id="bg-drop-tagline" x="0" y="1080" width="1100" height="420" fill="#FFFFFF"/>' . defs('tg-')
    . '<g transform="translate(35 1112)">' . shapes($all, 'color', 'tg-') . str_replace('  ', '', $tag('color')) . '</g></g>' . "\n";
$body .= '  <g id="AB-mark"><rect id="bg-drop-mark" x="1140" y="1080" width="420" height="420" fill="#FFFFFF"/>' . defs('mk-')
    . '<g transform="translate(1200 1130)">' . shapes($S, 'color', 'mk-') . '</g></g>' . "\n";
$body .= '  <g id="AB-icon"><rect id="bg-drop-icon" x="1600" y="1080" width="420" height="420" fill="#FFFFFF"/>' . defs('ic-')
    . '<g transform="translate(1614 1094) scale(0.766)"><rect width="512" height="512" rx="112" fill="' . GREEN . '"/>'
    . '<g transform="translate(256 262) scale(1.36) translate(-171 -160)">' . shapes($S, 'reverse', 'ic-') . '</g></g></g>' . "\n";

file_put_contents(
    dirname(__DIR__) . '/_src/simda-logo-master.svg',
    '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2240 1500" width="2240" height="1500">' . "\n"
    . '  <title>SIMDA logo master</title>' . "\n" . $body . '</svg>' . "\n"
);

echo "Built:\n";
foreach (glob("$out/*.svg") as $f) {
    echo '  ' . basename($f) . "\n";
}
