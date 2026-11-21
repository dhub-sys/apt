<?php
namespace Koinoxrista\Component\Koinoxrista\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Σχεδιάζει τις σελίδες A4 ενός προτύπου εκτύπωσης. Καθαρή PHP (χωρίς Joomla).
 *
 * Όλες οι θέσεις είναι σε χιλιοστά του χαρτιού, ώστε η εκτύπωση να «πέφτει» ακριβώς πάνω
 * στα προτυπωμένα έντυπα. Κάθε στοιχείο ανήκει σε ένα επίπεδο:
 *   - form: ό,τι είναι ήδη τυπωμένο στο προτυπωμένο χαρτί (τίτλοι, γραμμές, επικεφαλίδες)
 *   - data: τα δεδομένα του μήνα (ποσά, ονόματα, ημερομηνίες)
 * Τρόποι εκτύπωσης: data = μόνο δεδομένα (προτυπωμένο χαρτί), full = όλα (λευκό χαρτί),
 * form = μόνο το έντυπο (εκτύπωση κενών εντύπων).
 *
 * $ctx = [
 *   'fields' => [key => string],                 // πεδία κεφαλίδας
 *   'units' => [[key => string], ...],           // γραμμές ιδιοκτησιών (και πεδία κάθε ειδοποιητηρίου)
 *   'expenses' => [[key => string], ...],        // ανάλυση δαπανών
 *   'unit_expenses' => [index => [[key => string], ...]],
 *   'images' => [key => url],
 * ]
 */
final class Render
{
    public static function pageSize(array $layout): array
    {
        return ($layout['orientation'] ?? 'portrait') === 'landscape' ? [297.0, 210.0] : [210.0, 297.0];
    }

    public static function document(string $type, array $layout, array $ctx, array $opt = []): string
    {
        [$pw, $ph] = self::pageSize($layout);
        $opt += ['mode' => 'full', 'offset_x' => 0, 'offset_y' => 0, 'grid' => false, 'background' => '', 'draft' => false];
        $dx = (float) ($layout['offset_x'] ?? 0) + (float) $opt['offset_x'];
        $dy = (float) ($layout['offset_y'] ?? 0) + (float) $opt['offset_y'];
        $elements = array_values(array_filter((array) ($layout['elements'] ?? []), 'is_array'));
        $font = preg_replace('~[^A-Za-z0-9 ,\'-]~', '', (string) ($layout['font'] ?? 'Arial')) ?: 'Arial';
        $ctx['fields'] = self::derive((array) ($ctx['fields'] ?? []), (array) ($ctx['images'] ?? []));

        $pages = [];
        if ($type === 'notice') {
            $items = array_values($ctx['units'] ?? []);
            $perPage = max(1, min(4, (int) ($layout['per_page'] ?? 1)));
            $pitch = (float) ($layout['pitch'] ?? ($ph / $perPage));
            $chunks = $items ? array_chunk(array_keys($items), $perPage) : [[]];
            $count = count($chunks);
            foreach ($chunks as $p => $idxs) {
                $html = '';
                foreach ($idxs as $slot => $i) {
                    $html .= '<div class="kx-slot" style="top:' . round($slot * $pitch, 2) . 'mm;height:' . round($pitch, 2) . 'mm">';
                    $fields = ($items[$i] ?? []) + ($ctx['fields'] ?? []) + ['page.no' => (string) ($p + 1), 'page.count' => (string) $count, 'page.label' => 'Σελίδα ' . ($p + 1) . ' από ' . $count];
                    $tables = ['unit_expenses' => $ctx['unit_expenses'][$i] ?? [], 'expenses' => $ctx['expenses'] ?? [], 'units' => [$items[$i]]];
                    foreach ($elements as $el) {
                        $html .= self::element($el, $fields, $tables, $ctx['images'] ?? [], 0, $opt['mode'], $dx, $dy);
                    }
                    $html .= '</div>';
                }
                $pages[] = $html;
            }
        } else {
            $units = array_values($ctx['units'] ?? []);
            $expenses = array_values($ctx['expenses'] ?? []);
            $plans = self::sheetPlans($elements, $units, $expenses);
            $count = count($plans);
            foreach ($plans as $p => $plan) {
                $fields = ($ctx['fields'] ?? []) + ['page.no' => (string) ($p + 1), 'page.count' => (string) $count, 'page.label' => 'Σελίδα ' . ($p + 1) . ' από ' . $count];
                $last = $p === $count - 1;
                if (!$last) {
                    // Τα σύνολα τυπώνονται μόνο στην τελευταία σελίδα.
                    foreach ($fields as $k => $v) {
                        if (str_starts_with($k, 'totals.')) $fields[$k] = '';
                    }
                }
                $html = '';
                foreach ($elements as $el) {
                    if (!empty($el['last_only']) && !$last) continue;
                    $follow = (string) ($el['follow'] ?? '');
                    $shift = $follow === 'units' ? $plan['shift']['units'] : ($follow === 'expenses' ? $plan['shift']['expenses'] : 0.0);
                    $html .= self::element($el, $fields, ['units' => $units, 'expenses' => $expenses], $ctx['images'] ?? [], $p, $opt['mode'], $dx, $dy + $shift, $plan['tables'][(string) ($el['id'] ?? '')] ?? null);
                }
                $pages[] = $html;
            }
        }

        $out = '<style>@page{size:A4 ' . (($layout['orientation'] ?? 'portrait') === 'landscape' ? 'landscape' : 'portrait') . ';margin:0}</style>';
        foreach ($pages as $html) {
            $out .= '<section class="kx-page" style="width:' . $pw . 'mm;height:' . $ph . 'mm;font-family:' . Fmt::e(str_contains($font, ' ') ? "'" . $font . "'" : $font) . ',Arial,Helvetica,sans-serif">';
            if ($opt['background'] !== '') {
                $out .= '<img class="kx-page-bg kx-screen-only" src="' . Fmt::e($opt['background']) . '" alt="" style="opacity:' . (float) ($layout['bg_opacity'] ?? 0.45) . '">';
            }
            if ($opt['grid']) {
                $out .= self::grid($pw, $ph);
            }
            if ($opt['draft']) {
                $out .= '<div class="kx-draft-mark">ΠΡΟΣΧΕΔΙΟ</div>';
            }
            $out .= $html . '</section>';
        }
        return $out;
    }

    private static function visible(array $el, string $mode): bool
    {
        $layer = ($el['layer'] ?? 'data') === 'form' ? 'form' : 'data';
        return $mode === 'full' || $mode === $layer;
    }

    private static function box(array $el, float $dx, float $dy, ?float $y = null, ?float $x = null, ?float $w = null, ?float $h = null): string
    {
        $x = $x ?? (float) ($el['x'] ?? 0);
        $y = $y ?? (float) ($el['y'] ?? 0);
        $w = $w ?? (float) ($el['w'] ?? 0);
        $h = $h ?? (float) ($el['h'] ?? 0);
        $s = 'left:' . round($x + $dx, 2) . 'mm;top:' . round($y + $dy, 2) . 'mm;';
        if ($w > 0) $s .= 'width:' . round($w, 2) . 'mm;';
        if ($h > 0) $s .= 'height:' . round($h, 2) . 'mm;';
        return $s;
    }

    private static function textStyle(array $el, ?float $size = null): string
    {
        $size = $size ?? (float) ($el['size'] ?? 9);
        $s = 'font-size:' . max(4, min(48, $size)) . 'pt;';
        if (!empty($el['bold'])) $s .= 'font-weight:700;';
        if (!empty($el['italic'])) $s .= 'font-style:italic;';
        $align = in_array($el['align'] ?? '', ['left', 'center', 'right'], true) ? $el['align'] : 'left';
        $s .= 'text-align:' . $align . ';';
        if (!empty($el['color']) && preg_match('~^#[0-9a-fA-F]{3,8}$~', (string) $el['color'])) $s .= 'color:' . $el['color'] . ';';
        if (!empty($el['upper'])) $s .= 'text-transform:uppercase;';
        return $s;
    }

    private static function value(array $el, array $fields): string
    {
        $key = (string) ($el['field'] ?? '');
        $v = (string) ($fields[$key] ?? '');
        if ($v === '' && !empty($el['hide_empty'])) {
            return '';
        }
        return $v === '' ? '' : ((string) ($el['prefix'] ?? '') . $v . (string) ($el['suffix'] ?? ''));
    }

    private static function element(array $el, array $fields, array $tables, array $images, int $page, string $mode, float $dx, float $dy, ?array $plan = null): string
    {
        $type = (string) ($el['type'] ?? 'field');
        if ($type === 'table') {
            return self::table($el, $tables, $page, $mode, $dx, $dy, $plan);
        }
        if (!self::visible($el, $mode)) {
            return '';
        }
        $layerClass = ($el['layer'] ?? 'data') === 'form' ? ' kx-l-form' : ' kx-l-data';
        switch ($type) {
            case 'text':
            case 'field':
                $text = $type === 'text' ? (string) ($el['text'] ?? '') : self::value($el, $fields);
                if ($text === '') return '';
                $cls = 'kx-el' . $layerClass . (!empty($el['wrap']) ? ' kx-wrap' : '');
                return '<div class="' . $cls . '" style="' . self::box($el, $dx, $dy) . self::textStyle($el) . '">' . nl2br(Fmt::e($text), false) . '</div>';
            case 'line':
                $w = (float) ($el['w'] ?? 0);
                $h = (float) ($el['h'] ?? 0);
                $t = max(0.1, (float) ($el['stroke'] ?? 0.25));
                $style = !empty($el['dashed']) ? 'dashed' : 'solid';
                $color = !empty($el['color']) && preg_match('~^#[0-9a-fA-F]{3,8}$~', (string) $el['color']) ? $el['color'] : '#000';
                $css = $w >= $h
                    ? self::box($el, $dx, $dy, null, null, $w, 0.01) . 'border-top:' . $t . 'mm ' . $style . ' ' . $color . ';'
                    : self::box($el, $dx, $dy, null, null, 0.01, $h) . 'border-left:' . $t . 'mm ' . $style . ' ' . $color . ';';
                return '<div class="kx-el' . $layerClass . '" style="' . $css . '"></div>';
            case 'box':
                $t = max(0.1, (float) ($el['stroke'] ?? 0.25));
                $css = self::box($el, $dx, $dy) . 'border:' . $t . 'mm solid ' . (!empty($el['color']) && preg_match('~^#[0-9a-fA-F]{3,8}$~', (string) $el['color']) ? $el['color'] : '#000') . ';';
                if (!empty($el['fill']) && preg_match('~^#[0-9a-fA-F]{3,8}$~', (string) $el['fill'])) $css .= 'background:' . $el['fill'] . ';';
                if (!empty($el['radius'])) $css .= 'border-radius:' . (float) $el['radius'] . 'mm;';
                return '<div class="kx-el' . $layerClass . '" style="' . $css . '"></div>';
            case 'image':
                $src = (string) ($images[(string) ($el['field'] ?? '')] ?? '');
                if ($src === '') return '';
                return '<img class="kx-el kx-img' . $layerClass . '" src="' . Fmt::e($src) . '" alt="" style="' . self::box($el, $dx, $dy) . '">';
        }
        return '';
    }

    private static function table(array $el, array $tables, int $page, string $mode, float $dx, float $dy, ?array $plan = null): string
    {
        if ($plan && !empty($plan['hide'])) {
            return '';
        }
        $source = (string) ($el['source'] ?? 'units');
        $rowsData = array_values($tables[$source] ?? []);
        $rows = max(1, (int) ($el['rows'] ?? 20));
        $rowH = max(2.0, (float) ($el['row_h'] ?? 6));
        $designRowH = $rowH;
        $headH = max(0.0, (float) ($el['header_h'] ?? 0));
        $x0 = (float) ($el['x'] ?? 10);
        $y0 = (float) ($el['y'] ?? 50);
        $cols = array_values(array_filter((array) ($el['columns'] ?? []), 'is_array'));
        $width = array_sum(array_map(static fn($c) => (float) ($c['w'] ?? 10), $cols));
        $size = (float) ($el['size'] ?? 8);
        $paginate = $el['paginate'] ?? true;
        if ($plan) {
            $slice = $plan['data'];
            $rows = max(1, (int) $plan['rows']);
            $rowH = (float) $plan['row_h'];
        } elseif (!empty($el['fit']) && !$paginate) {
            // Μόνο όσες γραμμές χρειάζονται· αν δεν χωρούν, μικραίνει το ύψος γραμμής (έως 3,2 mm).
            $n = count($rowsData);
            if ($n > $rows) {
                $rowH = max(3.2, $rows * $rowH / $n);
                $rows = min($n, (int) floor($rows * $designRowH / $rowH + 0.01));
            } else {
                $rows = max(1, $n);
            }
            $slice = array_slice($rowsData, 0, $rows);
        } else {
            $slice = $paginate ? array_slice($rowsData, $page * $rows, $rows) : array_slice($rowsData, 0, $rows);
        }
        if ($rowH < $designRowH) {
            $size = max(5.0, $size * max(0.72, $rowH / $designRowH));
        }
        $radius = max(0.0, (float) ($el['radius'] ?? 0));
        $out = '';

        $gc = !empty($el['grid_color']) && preg_match('~^#[0-9a-fA-F]{3,8}$~', (string) $el['grid_color']) ? $el['grid_color'] : '#000';
        $hc = !empty($el['header_color']) && preg_match('~^#[0-9a-fA-F]{3,8}$~', (string) $el['header_color']) ? 'color:' . $el['header_color'] . ';' : '';
        if (self::visible(['layer' => 'form'], $mode)) {
            $grid = !empty($el['grid']);
            $stroke = max(0.1, (float) ($el['stroke'] ?? 0.2));
            if ($headH > 0) {
                $fill = !empty($el['header_fill']) && preg_match('~^#[0-9a-fA-F]{3,8}$~', (string) $el['header_fill']) ? 'background:' . $el['header_fill'] . ';' : '';
                $out .= '<div class="kx-el kx-l-form" style="' . self::box($el, $dx, $dy, $y0 - $headH, $x0, $width, $headH) . $fill . ($grid ? 'border:' . $stroke . 'mm solid ' . $gc . ';box-sizing:border-box;' : '')
                    . ($radius > 0 ? 'border-radius:' . $radius . 'mm ' . $radius . 'mm 0 0;' : '') . '"></div>';
                $cx = $x0;
                foreach ($cols as $i => $c) {
                    $w = (float) ($c['w'] ?? 10);
                    $out .= '<div class="kx-el kx-l-form kx-cell kx-head" style="' . self::box($el, $dx, $dy, $y0 - $headH, $cx, $w, $headH)
                        . 'font-size:' . max(4, (float) ($el['header_size'] ?? $size)) . 'pt;font-weight:700;text-align:' . (in_array($el['header_align'] ?? '', ['left', 'center', 'right'], true) ? $el['header_align'] : (in_array($c['align'] ?? '', ['left', 'center', 'right'], true) ? $c['align'] : 'left')) . ';line-height:1.05;' . $hc
                        . ($grid && $i > 0 ? 'border-left:' . $stroke . 'mm solid ' . $gc . ';' : '') . '">' . nl2br(Fmt::e((string) ($c['title'] ?? '')), false) . '</div>';
                    $cx += $w;
                }
            }
            if ($grid) {
                $h = $rows * $rowH;
                $out .= '<div class="kx-el kx-l-form" style="' . self::box($el, $dx, $dy, $y0, $x0, $width, $h) . 'border:' . $stroke . 'mm solid ' . $gc . ';box-sizing:border-box;'
                    . ($radius > 0 ? 'border-radius:' . ($headH > 0 ? '0 0 ' . $radius . 'mm ' . $radius . 'mm' : $radius . 'mm') . ';border-top-width:' . ($headH > 0 ? '0' : $stroke . 'mm') . ';' : '') . '"></div>';
                for ($r = 1; $r < $rows; $r++) {
                    $out .= '<div class="kx-el kx-l-form" style="' . self::box($el, $dx, $dy, $y0 + $r * $rowH, $x0, $width, 0.01) . 'border-top:' . ($stroke / 2) . 'mm solid ' . $gc . ';"></div>';
                }
                $cx = $x0;
                foreach ($cols as $i => $c) {
                    if ($i > 0) {
                        $out .= '<div class="kx-el kx-l-form" style="' . self::box($el, $dx, $dy, $y0, $cx, 0.01, $h) . 'border-left:' . $stroke . 'mm solid ' . $gc . ';"></div>';
                    }
                    $cx += (float) ($c['w'] ?? 10);
                }
            }
        }
        if (self::visible(['layer' => 'data'], $mode)) {
            foreach ($slice as $r => $row) {
                $cx = $x0;
                $y = $y0 + $r * $rowH;
                foreach ($cols as $c) {
                    $w = (float) ($c['w'] ?? 10);
                    $v = (string) ($row[(string) ($c['field'] ?? '')] ?? '');
                    if ($v !== '') {
                        $align = in_array($c['align'] ?? '', ['left', 'center', 'right'], true) ? $c['align'] : 'left';
                        $out .= '<div class="kx-el kx-l-data kx-cell" style="' . self::box($el, $dx, $dy, $y, $cx, $w, $rowH)
                            . 'font-size:' . max(4, $size) . 'pt;line-height:' . $rowH . 'mm;text-align:' . $align . ';' . (!empty($c['bold']) ? 'font-weight:700;' : '') . '">' . Fmt::e($v) . '</div>';
                    }
                    $cx += $w;
                }
            }
        }
        return $out;
    }

    /**
     * Σχέδιο σελίδων κατάστασης. Με «fit» ο πίνακας ιδιοκτησιών έχει μόνο όσες γραμμές χρειάζονται
     * και ό,τι τον ακολουθεί («follow: units») ανεβαίνει. Οι δαπάνες («fit») μπαίνουν όλες στην
     * τελευταία σελίδα, μέχρι το «max_y», μικραίνοντας το ύψος γραμμής αν χρειαστεί.
     * Επιστρέφει [['shift' => ['units'=>mm,'expenses'=>mm], 'tables' => [id => ['data','rows','row_h','hide']]], ...]
     */
    private static function sheetPlans(array $elements, array $units, array $expenses): array
    {
        $U = $E = null;
        foreach ($elements as $el) {
            if (($el['type'] ?? '') !== 'table') continue;
            $src = (string) ($el['source'] ?? 'units');
            if ($src === 'units' && $U === null) $U = $el;
            if ($src === 'expenses' && $E === null) $E = $el;
        }
        $fitU = $U && !empty($U['fit']);
        $fitE = $E && !empty($E['fit']);
        $blank = ['shift' => ['units' => 0.0, 'expenses' => 0.0], 'tables' => []];
        if (!$fitU && !$fitE) {
            $count = 1;
            foreach ($elements as $el) {
                if (($el['type'] ?? '') === 'table' && ($el['paginate'] ?? true)) {
                    $n = count(($el['source'] ?? 'units') === 'expenses' ? $expenses : $units);
                    $count = max($count, (int) ceil($n / max(1, (int) ($el['rows'] ?? 20))));
                }
            }
            return array_fill(0, $count, $blank);
        }

        $plans = [];
        $uId = (string) ($U['id'] ?? '');
        $uRows = max(1, (int) ($U['rows'] ?? 20));
        $uRh = max(2.0, (float) ($U['row_h'] ?? 6));
        if ($U) {
            $chunks = ($U['paginate'] ?? true) ? array_chunk($units, $uRows) : [array_slice($units, 0, $uRows)];
            foreach ($chunks ?: [[]] as $chunk) {
                $drawn = $fitU ? max(1, count($chunk)) : $uRows;
                $plan = $blank;
                $plan['shift']['units'] = ($drawn - $uRows) * $uRh;
                $plan['tables'][$uId] = ['data' => $chunk, 'rows' => $drawn, 'row_h' => $uRh];
                $plans[] = $plan;
            }
        } else {
            $plans[] = $blank;
        }

        if ($E) {
            $eId = (string) ($E['id'] ?? '');
            $eRows = max(1, (int) ($E['rows'] ?? 10));
            $eRh = max(2.0, (float) ($E['row_h'] ?? 5));
            $designEnd = (float) ($E['y'] ?? 0) + $eRows * $eRh;
            if ($fitE) {
                $minRh = max(2.5, (float) ($E['min_row_h'] ?? 3.6));
                $remaining = $expenses;
                $p = count($plans) - 1;
                for ($i = 0; $i < $p; $i++) {
                    $plans[$i]['tables'][$eId] = ['data' => [], 'rows' => 0, 'row_h' => $eRh, 'hide' => true];
                }
                while (true) {
                    $shiftY = ($E['follow'] ?? '') === 'units' ? $plans[$p]['shift']['units'] : 0.0;
                    $top = (float) ($E['y'] ?? 0) + $shiftY;
                    $maxY = !empty($E['max_y']) ? (float) $E['max_y'] : $designEnd;
                    $avail = max($eRh, $maxY - $top);
                    $n = count($remaining);
                    if ($n * $eRh <= $avail + 0.01) {
                        [$rh, $take] = [$eRh, $n];
                    } elseif ($n * $minRh <= $avail + 0.01) {
                        [$rh, $take] = [$avail / $n, $n];
                    } else {
                        [$rh, $take] = [$minRh, max(1, (int) floor(($avail + 0.01) / $minRh))];
                    }
                    $slice = array_splice($remaining, 0, $take);
                    $drawn = max(1, count($slice));
                    $plans[$p]['tables'][$eId] = ['data' => $slice, 'rows' => $drawn, 'row_h' => $rh];
                    $plans[$p]['shift']['expenses'] = $top + $drawn * $rh - $designEnd;
                    if (!$remaining) break;
                    // Σελίδα μόνο για τις δαπάνες που περισσεύουν: χωρίς πίνακα ιδιοκτησιών.
                    $extra = $blank;
                    $extra['shift']['units'] = -$uRows * $uRh - (float) ($U['header_h'] ?? 0);
                    if ($U) $extra['tables'][$uId] = ['data' => [], 'rows' => 0, 'row_h' => $uRh, 'hide' => true];
                    $plans[] = $extra;
                    $p++;
                }
            } else {
                $need = (int) ceil(count($expenses) / $eRows);
                while (count($plans) < $need) {
                    $extra = $blank;
                    if ($U) $extra['tables'][$uId] = ['data' => [], 'rows' => $fitU ? 1 : $uRows, 'row_h' => $uRh];
                    $plans[] = $extra;
                }
                foreach ($plans as $i => $pl) {
                    $plans[$i]['tables'][$eId] = ['data' => array_slice($expenses, $i * $eRows, $eRows), 'rows' => $eRows, 'row_h' => $eRh];
                }
            }
        }
        return $plans;
    }

    /** Πεδία που προκύπτουν από άλλα (ισχύουν και για μήνες που εκδόθηκαν πριν υπάρξουν). */
    private static function derive(array $f, array $images): array
    {
        $g = static fn($k) => trim((string) ($f[$k] ?? ''));
        $f['account.name_nologo'] = empty($images['account.logo']) ? $g('account.name') : '';
        $f['account.line'] = implode(' · ', array_filter([$g('account.address'), $g('account.phone'), $g('account.email')]));
        // Διεύθυνση κτιρίου χωρίς επαναλήψεις: αν η ονομασία περιέχει ήδη την οδό, μόνο Τ.Κ. & πόλη.
        $title = $g('building.title');
        $street = $g('building.street');
        $city = $g('building.city');
        $area = $g('building.area');
        $pc = trim($g('building.postcode') . ' ' . $city);
        if ($street !== '' && $title !== '' && mb_stripos($title, $street) !== false) {
            $loc = $pc !== '' ? $pc : $area;
            if ($area !== '' && mb_strtolower($area) !== mb_strtolower($city) && mb_stripos($title, $area) === false) $loc = $area . ($loc !== '' ? ', ' . $loc : '');
        } else {
            $parts = [$street];
            if ($area !== '' && mb_strtolower($area) !== mb_strtolower($city)) $parts[] = $area;
            $parts[] = $pc;
            $loc = implode(', ', array_filter($parts));
        }
        $f['building.location'] = $loc !== '' ? $loc : $g('building.address');
        return $f;
    }

    public const EXPENSE_GROUPS = [
        'general' => 'Κοινόχρηστες δαπάνες',
        'elevator' => 'Δαπάνες ανελκυστήρα',
        'heating' => 'Δαπάνες θέρμανσης',
        'special' => 'Ειδικές δαπάνες',
        'owner' => 'Δαπάνες ιδιοκτητών',
    ];

    /**
     * Ανάλυση δαπανών ειδοποιητηρίου «ανά κατηγορία» αντί για αναλυτικά: μία γραμμή ανά στήλη
     * (κοινόχρηστα, ανελκυστήρας, θέρμανση, ειδικές, ιδιοκτητών) με το σύνολο του κτιρίου και το μερίδιο
     * της ιδιοκτησίας. Υπολογίζεται από τα ήδη μορφοποιημένα ποσά (ισχύει και για εκδοθέντες μήνες).
     */
    public static function groupExpenses(array $ctx): array
    {
        $f = (array) ($ctx['fields'] ?? []);
        $byLabel = [];
        foreach (Calc::CATEGORIES as $col => $label) $byLabel[$label] = $col;
        foreach (array_values((array) ($ctx['units'] ?? [])) as $i => $u) {
            // Άθροισμα των δαπανών στις οποίες συμμετέχει η ιδιοκτησία, ανά στήλη.
            $amount = [];
            foreach ((array) ($ctx['unit_expenses'][$i] ?? []) as $r) {
                $col = (string) ($r['uexp.col'] ?? ($byLabel[(string) ($r['uexp.category'] ?? '')] ?? 'general'));
                $amount[$col] = ($amount[$col] ?? 0) + Calc::cents($r['uexp.amount'] ?? 0);
            }
            $rows = [];
            foreach (self::EXPENSE_GROUPS as $col => $label) {
                $share = trim((string) ($u['amt.' . $col] ?? ''));
                if ($share === '' || Calc::parseNumber($share) == 0.0) continue;
                $total = isset($amount[$col]) ? Fmt::moneyCents($amount[$col]) : (string) ($f['totals.' . $col] ?? '');
                $rows[] = ['uexp.title' => $label, 'uexp.category' => $label, 'uexp.amount' => $total, 'uexp.share' => $share, 'uexp.col' => $col];
            }
            $ctx['unit_expenses'][$i] = $rows;
        }
        return $ctx;
    }

    /** Πλέγμα βαθμονόμησης: γραμμή ανά 10 mm και αριθμοί στα άκρα. */
    private static function grid(float $pw, float $ph): string
    {
        $s = '<div class="kx-grid">';
        for ($x = 10; $x < $pw; $x += 10) {
            $s .= '<i class="v" style="left:' . $x . 'mm"></i><b style="left:' . ($x + 0.5) . 'mm;top:0.5mm">' . $x . '</b>';
        }
        for ($y = 10; $y < $ph; $y += 10) {
            $s .= '<i class="h" style="top:' . $y . 'mm"></i><b style="top:' . ($y + 0.3) . 'mm;left:0.5mm">' . $y . '</b>';
        }
        return $s . '<em style="left:20mm;top:20mm"></em><em style="left:' . ($pw - 20) . 'mm;top:' . ($ph - 20) . 'mm"></em></div>';
    }
}
