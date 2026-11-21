<?php
// php tests/calc_test.php  — tests του μηχανισμού κατανομής (χωρίς Joomla).
define('_JEXEC', 1);
require __DIR__ . '/../components/com_koinoxrista/src/Helper/Fmt.php';
require __DIR__ . '/../components/com_koinoxrista/src/Helper/Calc.php';

use Koinoxrista\Component\Koinoxrista\Site\Helper\Calc;

$fail = 0;
function check(string $name, $got, $want): void
{
    global $fail;
    if ($got === $want) {
        echo "ok   $name\n";
    } else {
        $fail++;
        echo "FAIL $name\n     got:  " . json_encode($got) . "\n     want: " . json_encode($want) . "\n";
    }
}

// allocate: το άθροισμα ισούται πάντα με το ποσό.
check('allocate 100.00 / 3', Calc::allocate(10000, [1 => 1, 2 => 1, 3 => 1]), [1 => 3334, 2 => 3333, 3 => 3333]);
check('allocate negative', Calc::allocate(-100, [1 => 1, 2 => 1, 3 => 1]), [1 => -34, 2 => -33, 3 => -33]);
check('allocate zero weights skipped', Calc::allocate(1000, [1 => 0, 2 => 5]), [2 => 1000]);
check('allocate no weights', Calc::allocate(1000, [1 => 0]), []);
for ($i = 0; $i < 200; $i++) {
    $w = [];
    for ($j = 1; $j <= rand(1, 30); $j++) $w[$j] = rand(0, 300) / 3;
    $c = rand(-100000, 1000000);
    $r = Calc::allocate($c, $w);
    if (array_sum($w) > 0 && array_sum($r) !== $c) { check("allocate random #$i sum", array_sum($r), $c); }
}
echo "ok   allocate random sums\n";

check('parse 1.234,56', Calc::parseNumber('1.234,56'), 1234.56);
check('parse 1234.56', Calc::parseNumber('1234.56'), 1234.56);
check('parse 12,5 €', Calc::parseNumber('12,5 €'), 12.5);
check('cents 0.1+0.2', Calc::cents(0.1 + 0.2), 30);

// Πολυκατοικία 4 διαμερισμάτων. Κλείδες: 1 γενικά, 2 ανελκυστήρας, 3 θέρμανση.
$units = [
    ['id' => 1, 'code' => 'Ισ', 'mills' => [1 => 150, 2 => 0, 3 => 160], 'area' => 60, 'heat_coef' => 1.0, 'consumption' => 100],
    ['id' => 2, 'code' => 'Α1', 'mills' => [1 => 250, 2 => 200, 3 => 240], 'area' => 90, 'heat_coef' => 1.1, 'consumption' => 0],
    ['id' => 3, 'code' => 'Β1', 'mills' => [1 => 280, 2 => 350, 3 => 280], 'area' => 95, 'heat_coef' => 1.0, 'consumption' => 200],
    ['id' => 4, 'code' => 'Γ1', 'mills' => [1 => 320, 2 => 450, 3 => 320], 'area' => 110, 'heat_coef' => 0.9, 'consumption' => 300],
];
$expenses = [
    ['id' => 10, 'title' => 'ΔΕΗ κοινοχρήστων', 'amount' => '123,45', 'category' => 'general', 'method' => 'key', 'key_id' => 1, 'payer' => 'tenant'],
    ['id' => 11, 'title' => 'Συντήρηση ανελκυστήρα', 'amount' => 80, 'category' => 'elevator', 'method' => 'key', 'key_id' => 2, 'payer' => 'tenant'],
    ['id' => 12, 'title' => 'Πετρέλαιο', 'amount' => 1000, 'category' => 'heating', 'method' => 'heating', 'key_id' => 3, 'payer' => 'tenant'],
    ['id' => 13, 'title' => 'Καθαρισμός', 'amount' => 40, 'category' => 'general', 'method' => 'equal', 'payer' => 'tenant'],
    ['id' => 14, 'title' => 'Αποθεματικό', 'amount' => 100, 'category' => 'general', 'method' => 'key', 'key_id' => 1, 'payer' => 'owner'],
    ['id' => 15, 'title' => 'Τζάμι εισόδου Β1', 'amount' => 25, 'category' => 'special', 'method' => 'custom', 'custom' => [3 => 25], 'payer' => 'tenant'],
    ['id' => 16, 'title' => 'Λαμπτήρες ορόφων Α-Γ', 'amount' => 10, 'category' => 'special', 'method' => 'equal', 'unit_ids' => [2, 3, 4], 'payer' => 'tenant'],
];
$r = Calc::compute($units, $expenses, ['heating_key' => 3, 'heating_mode' => 'hours', 'heating_fixed_pct' => 30]);

check('no warnings', $r['warnings'], []);
check('total = sum of expenses', $r['totals']['total'], 12345 + 8000 + 100000 + 4000 + 10000 + 2500 + 1000);
foreach ($r['expenses'] as $id => $x) check("expense $id fully allocated", $x['allocated'], $x['amount']);
check('ground floor pays no elevator', $r['units'][1]['elevator'], 0);
check('Β1 special = 25 + 3.33/3.34', $r['units'][3]['special'], 2500 + 333);
check('owner column', [$r['units'][1]['owner'], $r['units'][4]['owner']], [1500, 3200]);
// Θέρμανση: πάγιο 300€ με χιλιοστά θέρμανσης (σύνολο 1000), μεταβλητό 700€ με ώρες × συντελεστή.
// βάρη: 100, 0, 200, 270 → σύνολο 570.
$fixedA1 = 300 * 240 / 1000; // 72€ — το Α1 δεν άναψε, πληρώνει μόνο πάγιο
check('heating A1 fixed only', $r['units'][2]['heating'], (int) round($fixedA1 * 100));
check('heating total', $r['totals']['heating'], 100000);
$sumUnits = 0;
foreach ($r['units'] as $u) {
    $sumUnits += $u['total'];
    if ($u['total'] !== $u['general'] + $u['elevator'] + $u['heating'] + $u['special'] + $u['owner']) check('row sums', false, true);
}
check('sum of rows = total', $sumUnits, $r['totals']['total']);

// Χωρίς καταναλώσεις: προειδοποίηση και κατανομή με χιλιοστά.
$u2 = array_map(static fn($u) => ['consumption' => 0] + $u, $units);
foreach ($u2 as &$u) $u['consumption'] = 0; unset($u);
$r2 = Calc::compute($u2, [$expenses[2]], ['heating_key' => 3, 'heating_mode' => 'hours', 'heating_fixed_pct' => 30]);
check('heating fallback warns', count($r2['warnings']), 1);
check('heating fallback by mills', $r2['units'][4]['heating'], 32000);

// Custom amounts που δεν ταιριάζουν → προειδοποίηση.
$r3 = Calc::compute($units, [['id' => 1, 'title' => 'x', 'amount' => 30, 'method' => 'custom', 'custom' => [1 => 10, 2 => 10]]], []);
check('custom mismatch warns', count($r3['warnings']), 1);

// Καύσιμο (FIFO), παράδειγμα: 1/10 υπόλοιπο 280 lt × 1,10· 15/10 αγορά 700 lt × 1,32· 1/11 μέτρηση 560 lt.
$f = Calc::fuel(['stock' => [['qty' => '280', 'price' => '1,10']], 'purchases' => [['date' => '2026-10-15', 'qty' => '700', 'price' => '1,32']], 'end_qty' => '560']);
check('fifo total qty', $f['total'], 980.0);
check('fifo total value', $f['total_value'], 1232.0);
check('fifo consumed', $f['consumed'], 420.0);
check('fifo cost 280×1,10 + 140×1,32', $f['cost'], 492.8);
check('fifo breakdown', array_map(static fn($b) => [$b['qty'], $b['price']], $f['breakdown']), [[280.0, 1.1], [140.0, 1.32]]);
check('fifo remaining', $f['remaining'], [['qty' => 560.0, 'price' => 1.32]]);
check('fifo remaining value', $f['end_value'], 739.2);
// Δεύτερη αγορά μέσα στον μήνα και υπόλοιπο που καλύπτει δύο αγορές.
$f = Calc::fuel(['stock' => [['qty' => 100, 'price' => 1]], 'purchases' => [['qty' => 200, 'price' => 1.2], ['qty' => 300, 'price' => 1.3]], 'end_qty' => 400]);
check('fifo two purchases cost', $f['cost'], 100 * 1 + 100 * 1.2);
check('fifo remaining layers', $f['remaining'], [['qty' => 100.0, 'price' => 1.2], ['qty' => 300.0, 'price' => 1.3]]);
// Χωρίς μέτρηση: καμία κατανάλωση. Μέτρηση μεγαλύτερη από το διαθέσιμο: άκυρη.
check('fuel not measured', [Calc::fuel(['stock' => [['qty' => 100, 'price' => 1]], 'end_qty' => ''])['consumed'], Calc::fuel(['stock' => [['qty' => 100, 'price' => 1]]])['measured']], [0.0, false]);
check('fuel invalid', Calc::fuel(['stock' => [['qty' => 100, 'price' => 1]], 'end_qty' => 150])['invalid'], true);
// Παλιά μορφή αποθήκευσης.
$f = Calc::fuel(['start_qty' => 500, 'start_price' => '1,10', 'bought_qty' => 1000, 'bought_amount' => '1.250,00', 'end_qty' => 300]);
check('legacy fuel fifo cost', $f['cost'], round(500 * 1.1 + 700 * 1.25, 2));

// Εξαίρεση από απώλειες: το Ι1 δεν πληρώνει πάγιο θέρμανσης (μόνο τις ώρες του).
$ux = $units;
$ux[0]['heat_excluded'] = 1;
$ux[0]['consumption'] = 0;
$r4 = Calc::compute($ux, [$expenses[2]], ['heating_key' => 3, 'heating_mode' => 'hours', 'heating_fixed_pct' => 30]);
check('excluded unit pays nothing', $r4['units'][1]['heating'], 0);
check('excluded: total still allocated', $r4['totals']['heating'], 100000);
// Πάγιο 300€ στα χιλιοστά 240+280+320=840 → Α1 = 300×240/840.
check('excluded: A1 fixed share', $r4['units'][2]['heating'], (int) round(30000 * 240 / 840));
// Ολική εξαίρεση: ακόμη κι αν ο ωρομετρητής γράψει ώρες, δεν πληρώνει ούτε κατανάλωση.
$ux[0]['consumption'] = 100;
$r5 = Calc::compute($ux, [$expenses[2], ['id' => 20, 'title' => 'Συντήρηση καυστήρα', 'amount' => 60, 'category' => 'heating', 'method' => 'key', 'key_id' => 3]], ['heating_key' => 3, 'heating_mode' => 'hours', 'heating_fixed_pct' => 30]);
check('excluded: no consumption share', $r5['units'][1]['heating'], 0);
check('excluded: heating-category key expense', $r5['expenses'][20]['allocated'], 6000);
check('excluded: totals intact', $r5['totals']['heating'], 106000);

// Τύπος αυτονομίας με τα νούμερα του ειδοποιητηρίου Ιανουαρίου: E = 468,00 €, Σ(εi·fi) = 31,85%,
// Α1: εi = 0,0942, fi ώστε εi·fi = 0,0200, 28 ώρες· τα υπόλοιπα 354,8 σταθμισμένες ώρες.
$fiA1 = 0.0200 / 0.0942 * 100;                   // ≈ 21,23%
$fiRest = (0.3185 - 0.0200) / 0.9058 * 100;      // ≈ 32,95%
$uf = [
    ['id' => 1, 'mills' => [3 => 94.2], 'heat_coef' => 1, 'consumption' => 28, 'heat_fi' => $fiA1],
    ['id' => 2, 'mills' => [3 => 905.8], 'heat_coef' => 1, 'consumption' => 354.8, 'heat_fi' => $fiRest],
];
$rf = Calc::compute($uf, [['id' => 1, 'title' => 'Πετρέλαιο', 'amount' => 468, 'category' => 'heating', 'method' => 'heating', 'key_id' => 3]], ['heating_key' => 3, 'heating_mode' => 'hours', 'heating_fixed_pct' => 30]);
check('formula: building fixed 149,06', $rf['expenses'][1]['fixed'], 14906);
check('formula: building variable 318,94', $rf['expenses'][1]['var'], 31894);
check('formula: A1 fixed 9,37 (±1)', abs($rf['units'][1]['heat_fixed'] - 937) <= 1, true);
check('formula: A1 variable 23,33', $rf['units'][1]['heat_var'], 2333);
// Φεβρουάριος: E = 312,00 € → πάγιο 99,37.
$rf2 = Calc::compute($uf, [['id' => 1, 'title' => 'Πετρέλαιο', 'amount' => 312, 'category' => 'heating', 'method' => 'heating', 'key_id' => 3]], ['heating_key' => 3, 'heating_mode' => 'hours', 'heating_fixed_pct' => 30]);
check('formula: February fixed 99,37', $rf2['expenses'][1]['fixed'], 9937);
// Πάγιο στις Ειδικές δαπάνες.
$rf3 = Calc::compute($uf, [['id' => 1, 'title' => 'Πετρέλαιο', 'amount' => 468, 'category' => 'heating', 'method' => 'heating', 'key_id' => 3]], ['heating_key' => 3, 'heating_mode' => 'hours', 'heating_fixed_pct' => 30, 'heating_fixed_column' => 'special']);
check('fixed in special column', [$rf3['totals']['special'], $rf3['totals']['heating']], [14906, 31894]);
check('A1 special = fixed', $rf3['units'][1]['special'], $rf3['units'][1]['heat_fixed']);

// Πίνακας κατανομής της μελέτης (Ολυμπιονικών 6): εi και fi ανά διαμέρισμα, ei = εi (ώρες × χιλιοστά).
$study = [['Α1', 94.2, 21, 28], ['Α2', 92.6, 37, 40], ['Β', 203.3, 35, 30], ['Γ', 203.3, 35, 35], ['Δ', 203.3, 35, 25], ['Ε', 203.3, 25, 20]];
$us = [];
foreach ($study as $i => [$code, $mills, $fi, $hours]) {
    $us[] = ['id' => $i + 1, 'code' => $code, 'mills' => [3 => $mills], 'heat_fi' => $fi, 'heat_coef' => 1, 'consumption' => $hours];
}
$rs = Calc::compute($us, [['id' => 1, 'title' => 'Πετρέλαιο', 'amount' => 468, 'category' => 'heating', 'method' => 'heating', 'key_id' => 3]],
    ['heating_key' => 3, 'heating_mode' => 'hours', 'heating_fixed_pct' => 30, 'heating_ei' => 'mills']);
// Σ(εi·fi) ακριβώς = 0,318334 → πάγιο 148,98 € (η εταιρεία, με στρογγυλεμένα γινόμενα 31,85%, βγάζει 149,06 €).
check('study: fixed = 468 × 0,318334', $rs['expenses'][1]['fixed'], 14898);
check('study: A1 fixed = 468 × 0,0942 × 0,21', $rs['units'][1]['heat_fixed'], 926);
// Κατανάλωση με ώρες × εi: Α1 = 28 × 94,2 / Σ(ώρες × χιλιοστά).
$sumW = 0; foreach ($study as [, $m, , $h]) $sumW += $m * $h;
check('study: A1 consumption by hours × mills (±1)', abs($rs['units'][1]['heat_var'] - (int) round((46800 - 14898) * 28 * 94.2 / $sumW)) <= 1, true);

// Δίκαιη στρογγυλοποίηση (Ολυμπιονικών 6): 6 διαμερίσματα + 7 υπόγεια, «Έκδοση» 10,00 σε όλα,
// ΔΕΗ 65,99 / Καθαριότητα 15,00 / Αμοιβή 30,00 ισόποσα μόνο στο υπόγειο → τα υπόγεια διαφέρουν το πολύ 1 λεπτό.
$ub = [];
for ($i = 1; $i <= 13; $i++) $ub[] = ['id' => $i, 'mills' => [1 => $i <= 6 ? 1000 / 6 : 0]];
$basement = range(7, 13);
$rb = Calc::compute($ub, [
    ['id' => 1, 'title' => 'Έκδοση', 'amount' => 10, 'method' => 'equal'],
    ['id' => 2, 'title' => 'ΔΕΗ Υπογείου', 'amount' => '65,99', 'method' => 'equal', 'unit_ids' => $basement],
    ['id' => 3, 'title' => 'Καθαριότητα Υπογείου', 'amount' => 15, 'method' => 'equal', 'unit_ids' => $basement],
    ['id' => 4, 'title' => 'Αμοιβή Υπογείου', 'amount' => 30, 'method' => 'equal', 'unit_ids' => $basement],
], []);
$bt = array_map(static fn($id) => $rb['units'][$id]['total'], $basement);
check('fair rounding: basement spread ≤ 1 cent', max($bt) - min($bt) <= 1, true);
check('fair rounding: basement total exact', array_sum($bt) + array_sum(array_map(static fn($id) => $rb['units'][$id]['total'], range(1, 6))), 1000 + 6599 + 1500 + 3000);
$all13 = array_map(static fn($u) => $rb['units'][$u['id']]['total'], $ub);
// Χωρίς επαναλαμβανόμενο «τυχερό»: κανένα υπόγειο δεν παίρνει έξτρα λεπτό σε όλες τις δαπάνες.
check('fair rounding: values', array_values(array_unique($bt)) == array_values(array_unique([max($bt), min($bt)])), true);

echo $fail ? "\n$fail test(s) failed\n" : "\nall tests passed\n";
exit($fail ? 1 : 0);
