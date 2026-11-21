<?php
// php tests/render_preview.php <out-dir>  — γράφει HTML προεπισκοπήσεις των έτοιμων προτύπων με δείγμα δεδομένων.
define('_JEXEC', 1);
$base = __DIR__ . '/../components/com_koinoxrista/src/Helper/';
foreach (['Fmt', 'Calc', 'Render', 'Layouts'] as $c) require $base . $c . '.php';

use Koinoxrista\Component\Koinoxrista\Site\Helper\Layouts;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Render;

$out = rtrim($argv[1] ?? sys_get_temp_dir(), '/');
$css = file_get_contents(__DIR__ . '/../media/css/print.css');
$sample = Layouts::sample();
// Περίπτωση 1: 6 διαμερίσματα, 12 δαπάνες → μία σελίδα.
$few = $sample;
$few['units'] = array_slice($sample['units'], 0, 6);
for ($i = 0; $i < 6; $i++) {
    $e = $sample['expenses'][$i % 6];
    $e['exp.no'] = (string) (7 + $i);
    $few['expenses'][] = $e;
}
// Περίπτωση 2: 30 ιδιοκτησίες → συνέχεια σε δεύτερη σελίδα.
for ($i = 0; $i < 20; $i++) {
    $u = $sample['units'][$i % 10];
    $u['unit.code'] = 'X' . ($i + 1);
    $sample['units'][] = $u;
}
$ledger = $few;
foreach ($ledger['units'] as $i => $u) {
    $ledger['units'][$i] += $i % 3 === 2 ? [] : ['pay.date' => '0' . (3 + $i) . '/11/2026', 'pay.amount' => $u['amt.total'], 'pay.method' => $i % 2 ? 'Κατάθεση' : 'Μετρητά', 'pay.note' => 'απόδ. ' . (100 + $i)];
}
$ledger['fields'] += ['totals.payable' => '812,40', 'totals.paid' => '540,10', 'totals.unpaid' => '2 ιδιοκτησίες'];
$cases = [
    'sheet-few' => ['sheet', Layouts::sheet(), ['mode' => 'full'], $few],
    'sheet-many' => ['sheet', Layouts::sheet(), ['mode' => 'full'], $sample],
    'sheet-data' => ['sheet', Layouts::sheet(), ['mode' => 'data', 'grid' => true], $few],
    'notice-full' => ['notice', Layouts::notice(), ['mode' => 'full'], $few],
    'ledger' => ['ledger', Layouts::ledger(), ['mode' => 'full'], $ledger],
];
foreach ($cases as $name => [$type, $layout, $opt, $ctx]) {
    $layout = Layouts::sanitize(json_decode(json_encode($layout), true));
    $html = '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geom:wght@300..900&display=swap"><style>body{margin:0;background:#e5e7eb}' . $css . '</style><div class="kx-pages">'
        . Render::document($type, $layout, $ctx, $opt) . '</div>';
    file_put_contents("$out/$name.html", $html);
    echo "$out/$name.html\n";
}
