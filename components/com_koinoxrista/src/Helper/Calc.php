<?php
namespace Koinoxrista\Component\Koinoxrista\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Κατανομή δαπανών. Καθαρή PHP, χωρίς εξαρτήσεις από το Joomla, ώστε να ελέγχεται με tests.
 *
 * Όλα τα ποσά υπολογίζονται σε λεπτά (int). Κάθε δαπάνη μοιράζεται με τη μέθοδο του
 * «μεγαλύτερου υπολοίπου», οπότε το άθροισμα των μεριδίων ισούται πάντα ακριβώς με το ποσό
 * της δαπάνης, χωρίς διαφορές στρογγυλοποίησης.
 *
 * Μονάδα (unit):  ['id'=>int, 'code'=>string, 'mills'=>[key_id=>float], 'area'=>float,
 *                  'heat_coef'=>float, 'consumption'=>float]
 * Δαπάνη:         ['id'=>int, 'title'=>string, 'amount'=>float, 'category'=>general|elevator|heating|special,
 *                  'method'=>key|equal|area|heating|consumption|custom, 'key_id'=>int,
 *                  'payer'=>tenant|owner, 'unit_ids'=>int[], 'custom'=>[unit_id=>float]]
 * Επιλογές:       ['heating_key'=>int, 'heating_mode'=>mills|hours|units, 'heating_fixed_pct'=>float]
 */
final class Calc
{
    public const CATEGORIES = [
        'general' => 'Κοινόχρηστα',
        'elevator' => 'Ανελκυστήρας',
        'heating' => 'Θέρμανση',
        'special' => 'Ειδικές δαπάνες',
    ];

    public const METHODS = [
        'key' => 'Με χιλιοστά',
        'equal' => 'Ισόποσα ανά ιδιοκτησία',
        'area' => 'Με τετραγωνικά μέτρα',
        'heating' => 'Θέρμανση: πάγιο + κατανάλωση',
        'consumption' => 'Μόνο με κατανάλωση (ώρες/μονάδες)',
        'custom' => 'Συγκεκριμένα ποσά ανά ιδιοκτησία',
    ];

    public const PAYERS = [
        'tenant' => 'Ένοικος (χρήστης)',
        'owner' => 'Ιδιοκτήτης',
    ];

    /** Στήλες αποτελέσματος ανά ιδιοκτησία, με τη σειρά που εμφανίζονται. */
    public const COLUMNS = ['general', 'elevator', 'heating', 'special', 'owner'];

    public static function cents($value): int
    {
        if (is_string($value)) {
            $value = self::parseNumber($value);
        }
        return (int) round(((float) $value) * 100);
    }

    /** Δέχεται «1.234,56», «1234,56» και «1234.56». */
    public static function parseNumber($value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        $s = trim(str_replace(["\xc2\xa0", ' ', '€'], '', (string) $value));
        if ($s === '') {
            return 0.0;
        }
        if (strpos($s, ',') !== false) {
            // Ελληνική μορφή: η τελεία είναι διαχωριστικό χιλιάδων.
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif (substr_count($s, '.') > 1) {
            $s = str_replace('.', '', $s);
        }
        return is_numeric($s) ? (float) $s : 0.0;
    }

    /**
     * Μοιράζει $cents λεπτά ανάλογα με τα βάρη. Επιστρέφει [id => λεπτά] μόνο για τα θετικά βάρη.
     * Μέθοδος μεγαλύτερου υπολοίπου· σε ισοπαλία προηγείται η σειρά των βαρών.
     */
    /**
     * Λεπτά που έχει πάρει «παραπάνω» κάθε ιδιοκτησία από τη στρογγυλοποίηση στον τρέχοντα υπολογισμό.
     * Όταν δύο ιδιοκτησίες ισοβαθμούν για ένα λεπτό που περισσεύει, το παίρνει όποια έχει πάρει λιγότερα
     * μέχρι τώρα, ώστε ιδιοκτησίες με ίδια συμμετοχή να διαφέρουν το πολύ 1 λεπτό στο σύνολο.
     */
    private static ?array $extra = null;

    public static function allocate(int $cents, array $weights): array
    {
        $weights = array_filter($weights, static fn($w) => (float) $w > 0);
        if (!$weights || $cents === 0) {
            return array_fill_keys(array_keys($weights), 0);
        }
        $sign = $cents < 0 ? -1 : 1;
        $abs = abs($cents);
        $sum = array_sum(array_map('floatval', $weights));
        $out = [];
        $rema = [];
        $given = 0;
        $order = 0;
        foreach ($weights as $id => $w) {
            $exact = $abs * ((float) $w) / $sum;
            $floor = (int) floor($exact + 1e-9);
            $out[$id] = $floor;
            $given += $floor;
            $rema[] = [$id, $exact - $floor, $order++];
        }
        $extra = self::$extra;
        $sgn = $sign;
        usort($rema, static function ($a, $b) use ($extra, $sgn) {
            $r = round($b[1], 9) <=> round($a[1], 9);
            if ($r !== 0 || $extra === null) return $r ?: ($a[2] <=> $b[2]);
            return (($extra[$a[0]] ?? 0) * $sgn <=> ($extra[$b[0]] ?? 0) * $sgn) ?: ($a[2] <=> $b[2]);
        });
        $left = $abs - $given;
        for ($i = 0; $left > 0; $i = ($i + 1) % count($rema), $left--) {
            $out[$rema[$i][0]]++;
            if (self::$extra !== null) self::$extra[$rema[$i][0]] = (self::$extra[$rema[$i][0]] ?? 0) + $sign;
        }
        if ($sign < 0) {
            foreach ($out as $id => $v) {
                $out[$id] = -$v;
            }
        }
        return $out;
    }

    public static function compute(array $units, array $expenses, array $opts = []): array
    {
        $heatKey = (int) ($opts['heating_key'] ?? 0);
        $heatMode = (string) ($opts['heating_mode'] ?? 'mills');
        $fixedPct = max(0.0, min(100.0, (float) ($opts['heating_fixed_pct'] ?? 30)));
        // Πού τυπώνεται το πάγιο (απώλειες) της αυτονομίας: στη Θέρμανση ή στις Ειδικές δαπάνες.
        $fixedColumn = ($opts['heating_fixed_column'] ?? 'heating') === 'special' ? 'special' : 'heating';
        // Συντελεστής κατανάλωσης ei: ξεχωριστή τιμή ανά διαμέρισμα ή τα ίδια τα χιλιοστά θέρμανσης (εi),
        // όταν η μελέτη δεν δίνει ei' («Αυτόνομη θέρμανση με χιλιοστά × ώρες»).
        $eiKey = ($opts['heating_ei'] ?? 'coef') === 'mills' ? $heatKey : 0;

        $blank = array_fill_keys(array_merge(self::COLUMNS, ['tenant', 'total']), 0);
        $result = ['units' => [], 'expenses' => [], 'totals' => $blank, 'warnings' => []];
        self::$extra = [];
        $byId = [];
        foreach ($units as $u) {
            $id = (int) $u['id'];
            $byId[$id] = $u;
            $result['units'][$id] = $blank + ['expenses' => [], 'heat_fixed' => 0, 'heat_var' => 0];
        }

        foreach ($expenses as $e) {
            $eid = (int) ($e['id'] ?? 0);
            $title = (string) ($e['title'] ?? '');
            $amount = self::cents($e['amount'] ?? 0);
            $method = (string) ($e['method'] ?? 'key');
            $category = isset(self::CATEGORIES[$e['category'] ?? '']) ? (string) $e['category'] : 'general';
            $payer = ($e['payer'] ?? 'tenant') === 'owner' ? 'owner' : 'tenant';
            $keyId = (int) ($e['key_id'] ?? 0);
            $subset = array_values(array_filter(array_map('intval', (array) ($e['unit_ids'] ?? []))));
            $eligible = $subset ? array_intersect_key($byId, array_flip($subset)) : $byId;
            // Ιδιοκτησίες εξαιρεμένες από τη θέρμανση (π.χ. δικός τους λέβητας) δεν συμμετέχουν σε καμία
            // δαπάνη θέρμανσης: ούτε απώλειες/πάγιο ούτε κατανάλωση.
            if ($category === 'heating' || in_array($method, ['heating', 'consumption'], true)) {
                $eligible = array_filter($eligible, static fn($u) => empty($u['heat_excluded']));
            }

            $shares = [];
            $split = null; // θέρμανση: ['fixed' => [uid => λεπτά], 'var' => [uid => λεπτά]]
            switch ($method) {
                case 'equal':
                    $shares = self::allocate($amount, array_map(static fn() => 1, $eligible));
                    break;
                case 'area':
                    $shares = self::allocate($amount, array_map(static fn($u) => (float) ($u['area'] ?? 0), $eligible));
                    break;
                case 'consumption':
                    $w = self::consumptionWeights($eligible, $eiKey);
                    if (array_sum($w) <= 0) {
                        $result['warnings'][] = "«{$title}»: δεν υπάρχουν καταναλώσεις· η δαπάνη μοιράστηκε με τα χιλιοστά θέρμανσης.";
                        $w = self::millWeights($eligible, $keyId ?: $heatKey);
                    }
                    $shares = self::allocate($amount, $w);
                    break;
                case 'heating':
                    $key = $keyId ?: $heatKey;
                    $mills = self::millWeights($eligible, $key);
                    $cons = self::consumptionWeights($eligible, $eiKey);
                    if ($heatMode === 'mills' || $fixedPct >= 100) {
                        $shares = self::allocate($amount, $mills);
                    } elseif (array_sum($cons) <= 0) {
                        $result['warnings'][] = "«{$title}»: δεν υπάρχουν μετρήσεις κατανάλωσης· η δαπάνη μοιράστηκε ολόκληρη με τα χιλιοστά θέρμανσης.";
                        $shares = self::allocate($amount, $mills);
                    } else {
                        // Τύπος αυτονομίας:  Ei = [ εi·fi + Ωi·ei / Σ(Ωi·ei) · (1 − Σ εi·fi) ] · E
                        // εi = χιλιοστά θέρμανσης / Σ χιλιοστών, fi = συντελεστής απωλειών του διαμερίσματος
                        // (κενό = το ποσοστό του κτιρίου), Ωi = ώρες, ei = θερμικός συντελεστής.
                        $sumMills = array_sum($mills);
                        $fw = [];
                        $fixedFrac = 0.0;
                        foreach ($mills as $id => $m) {
                            $fi = isset($eligible[$id]['heat_fi']) && $eligible[$id]['heat_fi'] !== null && $eligible[$id]['heat_fi'] !== '' ? (float) $eligible[$id]['heat_fi'] : $fixedPct;
                            $fw[$id] = $m * max(0.0, min(100.0, $fi));
                            if ($sumMills > 0) $fixedFrac += ($m / $sumMills) * max(0.0, min(100.0, $fi)) / 100;
                        }
                        $fixed = (int) round($amount * $fixedFrac);
                        $a = self::allocate($fixed, $fw);
                        $b = self::allocate($amount - $fixed, $cons);
                        $split = ['fixed' => $a, 'var' => $b];
                        $shares = $a;
                        foreach ($b as $id => $v) {
                            $shares[$id] = ($shares[$id] ?? 0) + $v;
                        }
                        if ($fixed !== 0 && $sumMills <= 0) {
                            $result['warnings'][] = "«{$title}»: δεν υπάρχουν χιλιοστά θέρμανσης για το πάγιο μέρος.";
                        }
                    }
                    break;
                case 'custom':
                    foreach ((array) ($e['custom'] ?? []) as $uid => $v) {
                        $uid = (int) $uid;
                        $c = self::cents($v);
                        if (isset($byId[$uid]) && $c !== 0) {
                            $shares[$uid] = $c;
                        }
                    }
                    if (array_sum($shares) !== $amount) {
                        $result['warnings'][] = sprintf('«%s»: τα συγκεκριμένα ποσά αθροίζουν %s αντί για %s.', $title, Fmt::money(array_sum($shares) / 100), Fmt::money($amount / 100));
                    }
                    break;
                case 'key':
                default:
                    $shares = self::allocate($amount, self::millWeights($eligible, $keyId));
                    break;
            }

            $allocated = array_sum($shares);
            if ($amount !== 0 && $allocated === 0 && $method !== 'custom') {
                $result['warnings'][] = "«{$title}»: δεν κατανεμήθηκε (καμία ιδιοκτησία με χιλιοστά/βάρος στη συγκεκριμένη κλείδα).";
            }
            $result['expenses'][$eid] = ['amount' => $amount, 'allocated' => $allocated, 'category' => $category, 'payer' => $payer,
                'fixed' => $split ? array_sum($split['fixed']) : 0, 'var' => $split ? array_sum($split['var']) : 0];
            $column = $payer === 'owner' ? 'owner' : $category;
            // Θέρμανση με πάγιο: το πάγιο μπορεί να πηγαίνει σε άλλη στήλη (Ειδικές δαπάνες).
            $parts = [[$column, $shares]];
            if ($split) {
                foreach ($split['fixed'] as $uid => $c) if (isset($result['units'][$uid])) $result['units'][$uid]['heat_fixed'] += $c;
                foreach ($split['var'] as $uid => $c) if (isset($result['units'][$uid])) $result['units'][$uid]['heat_var'] += $c;
                if ($payer === 'tenant' && $fixedColumn !== $column) {
                    $parts = [[$column, $split['var']], [$fixedColumn, $split['fixed']]];
                }
            }
            foreach ($parts as [$col, $list]) foreach ($list as $uid => $c) {
                if (!isset($result['units'][$uid])) {
                    continue;
                }
                $row = &$result['units'][$uid];
                $row['expenses'][$eid] = ($row['expenses'][$eid] ?? 0) + $c;
                $row[$col] += $c;
                if ($payer === 'tenant') {
                    $row['tenant'] += $c;
                }
                $row['total'] += $c;
                unset($row);
                $result['totals'][$col] += $c;
                if ($payer === 'tenant') {
                    $result['totals']['tenant'] += $c;
                }
                $result['totals']['total'] += $c;
            }
        }
        self::$extra = null;
        return $result;
    }

    private static function millWeights(array $units, int $keyId): array
    {
        $w = [];
        foreach ($units as $id => $u) {
            $w[$id] = (float) ($u['mills'][$keyId] ?? 0);
        }
        return $w;
    }

    private static function consumptionWeights(array $units, int $millsKey = 0): array
    {
        $w = [];
        foreach ($units as $id => $u) {
            $coef = $millsKey > 0
                ? (float) ($u['mills'][$millsKey] ?? 0)
                : (isset($u['heat_coef']) && $u['heat_coef'] !== '' ? (float) $u['heat_coef'] : 1.0);
            $w[$id] = max(0.0, (float) ($u['consumption'] ?? 0)) * $coef;
        }
        return $w;
    }

    /**
     * Στοιχεία καυσίμου σε ενιαία μορφή:
     * ['stock' => [['qty','price'],...], 'purchases' => [['date','qty','price'],...], 'end_qty' => float|null]
     * Δέχεται και την παλιά μορφή (start_qty/start_price/bought_qty/bought_amount/end_qty).
     */
    public static function fuelInput(array $h): array
    {
        $n = static fn($v) => max(0.0, self::parseNumber($v ?? 0));
        if (!isset($h['stock']) && !isset($h['purchases']) && (isset($h['start_qty']) || isset($h['bought_qty']))) {
            $bq = $n($h['bought_qty'] ?? 0);
            $h = [
                'stock' => [['qty' => $h['start_qty'] ?? 0, 'price' => $h['start_price'] ?? 0]],
                'purchases' => $bq > 0 ? [['date' => '', 'qty' => $bq, 'price' => $n($h['bought_amount'] ?? 0) / $bq]] : [],
                'end_qty' => $h['end_qty'] ?? '',
            ];
        }
        $rows = static function ($list, bool $date) use ($n) {
            $out = [];
            foreach ((array) $list as $r) {
                if (!is_array($r)) continue;
                $q = $n($r['qty'] ?? 0);
                if ($q <= 0) continue;
                $row = ['qty' => round($q, 3), 'price' => round($n($r['price'] ?? 0), 5)];
                if ($date) $row['date'] = (string) ($r['date'] ?? '');
                $out[] = $row;
            }
            return $out;
        };
        $end = $h['end_qty'] ?? '';
        return [
            'stock' => $rows($h['stock'] ?? [], false),
            'purchases' => $rows($h['purchases'] ?? [], true),
            'end_qty' => ($end === '' || $end === null) ? null : round($n($end), 3),
        ];
    }

    /**
     * Κοστολόγηση καυσίμου με FIFO: κατανάλωση = υπόλοιπο έναρξης + αγορές − υπόλοιπο λήξης (μέτρηση),
     * και εξοφλούνται πρώτα τα παλαιότερα λίτρα (υπόλοιπο έναρξης, μετά οι αγορές με τη σειρά τους).
     * Το υπόλοιπο λήξης κρατά τις τιμές των αγορών από τις οποίες προέρχεται και μεταφέρεται στον
     * επόμενο μήνα ('remaining').
     */
    public static function fuel(array $h): array
    {
        $in = self::fuelInput($h);
        $layers = array_merge($in['stock'], $in['purchases']);
        $sum = static fn($list) => array_sum(array_map(static fn($l) => $l['qty'], $list));
        $val = static fn($list) => round(array_sum(array_map(static fn($l) => round($l['qty'] * $l['price'], 2), $list)), 2);
        $total = round($sum($layers), 3);
        $measured = $in['end_qty'] !== null;
        $end = $measured ? $in['end_qty'] : $total;
        $consumed = max(0.0, round($total - $end, 3));
        $left = $consumed;
        $breakdown = [];
        $remaining = [];
        foreach ($layers as $l) {
            $take = min($l['qty'], $left);
            if ($take > 0) {
                $breakdown[] = ['qty' => round($take, 3), 'price' => $l['price'], 'amount' => round($take * $l['price'], 2)];
                $left = round($left - $take, 3);
            }
            if ($l['qty'] - $take > 0.0005) {
                $remaining[] = ['qty' => round($l['qty'] - $take, 3), 'price' => $l['price']];
            }
        }
        $cost = round(array_sum(array_column($breakdown, 'amount')), 2);
        return [
            'stock' => $in['stock'], 'purchases' => $in['purchases'],
            'start' => round($sum($in['stock']), 3), 'start_value' => $val($in['stock']),
            'bought' => round($sum($in['purchases']), 3), 'bought_value' => $val($in['purchases']),
            'total' => $total, 'total_value' => $val($layers),
            'end' => $end, 'end_value' => $val($remaining), 'measured' => $measured,
            'consumed' => $consumed, 'cost' => $cost, 'price' => $consumed > 0 ? round($cost / $consumed, 4) : 0.0,
            'breakdown' => $breakdown, 'remaining' => $remaining,
            'invalid' => $measured && $in['end_qty'] > $total + 0.0005,
        ];
    }
}
