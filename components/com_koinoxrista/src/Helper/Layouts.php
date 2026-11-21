<?php
namespace Koinoxrista\Component\Koinoxrista\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Κατάλογος πεδίων, έτοιμα πρότυπα εκτύπωσης και δείγμα δεδομένων για τον σχεδιαστή.
 * Καθαρή PHP (χωρίς Joomla).
 */
final class Layouts
{
    public const TYPES = [
        'sheet' => 'Κατάσταση κοινοχρήστων (πίνακας κτιρίου)',
        'notice' => 'Ειδοποιητήρια ανά ιδιοκτησία',
        'ledger' => 'Φύλλο διαχειριστή (βεβαίωση εισπράξεων)',
    ];

    /** Πεδία κεφαλίδας: διαθέσιμα σε όλα τα πρότυπα. */
    public static function headerFields(): array
    {
        return [
            'Διαχειριστική εταιρεία' => [
                'account.name' => 'Επωνυμία', 'account.address' => 'Διεύθυνση', 'account.phone' => 'Τηλέφωνο',
                'account.email' => 'Email', 'account.website' => 'Website', 'account.afm' => 'ΑΦΜ', 'account.contact' => 'Τηλέφωνο · email (μία γραμμή)',
                'account.line' => 'Διεύθυνση · τηλέφωνο · email (μία γραμμή)', 'account.name_nologo' => 'Επωνυμία μόνο όταν δεν υπάρχει λογότυπο',
            ],
            'Κτίριο' => [
                'building.title' => 'Ονομασία', 'building.address' => 'Πλήρης διεύθυνση', 'building.location' => 'Διεύθυνση χωρίς επανάληψη της ονομασίας', 'building.street' => 'Οδός & αριθμός',
                'building.area' => 'Περιοχή', 'building.city' => 'Πόλη', 'building.postcode' => 'Τ.Κ.', 'building.afm' => 'ΑΦΜ κτιρίου',
                'building.iban' => 'IBAN', 'building.bank' => 'Τράπεζα', 'building.beneficiary' => 'Δικαιούχος λογαριασμού',
                'building.units_count' => 'Πλήθος ιδιοκτησιών',
            ],
            'Διαχειριστής' => [
                'manager.name' => 'Ονοματεπώνυμο', 'manager.phone' => 'Τηλέφωνο', 'manager.mobile' => 'Κινητό', 'manager.email' => 'Email',
                'manager.address' => 'Διεύθυνση', 'manager.hours' => 'Ώρες επικοινωνίας', 'manager.contact' => 'Πλήρης γραμμή επικοινωνίας',
                'rep.name' => 'Εκπρόσωπος κτιρίου', 'rep.phone' => 'Τηλέφωνο εκπροσώπου',
            ],
            'Περίοδος' => [
                'period.title' => 'Μήνας (π.χ. Οκτώβριος 2026)', 'period.month' => 'Μήνας', 'period.year' => 'Έτος',
                'period.issue_date' => 'Ημερομηνία έκδοσης', 'period.due_date' => 'Πληρωμή έως', 'period.notes' => 'Ανακοινώσεις',
                'print.date' => 'Ημερομηνία εκτύπωσης', 'page.no' => 'Αριθμός σελίδας', 'page.count' => 'Σύνολο σελίδων', 'page.label' => 'Σελίδα Χ από Υ',
            ],
            'Σύνολα κτιρίου' => [
                'totals.general' => 'Σύνολο κοινοχρήστων', 'totals.elevator' => 'Σύνολο ανελκυστήρα', 'totals.heating' => 'Σύνολο θέρμανσης',
                'totals.special' => 'Σύνολο ειδικών', 'totals.owner' => 'Σύνολο ιδιοκτητών', 'totals.current' => 'Γενικό σύνολο μήνα',
                'totals.expenses' => 'Σύνολο δαπανών', 'totals.mills.general' => 'Σύνολο χιλ. γενικών', 'totals.mills.elevator' => 'Σύνολο χιλ. ανελκυστήρα',
                'totals.mills.heating' => 'Σύνολο χιλ. θέρμανσης',
                'totals.payable' => 'Σύνολο πληρωτέου (φύλλο διαχειριστή)', 'totals.paid' => 'Σύνολο εισπράξεων (φύλλο διαχειριστή)', 'totals.unpaid' => 'Ανεξόφλητα (φύλλο διαχειριστή)',
            ],
            'Θέρμανση' => [
                'heating.start' => 'Υπόλοιπο έναρξης (ποσότητα)', 'heating.start_value' => 'Αξία υπολοίπου έναρξης',
                'heating.bought' => 'Αγορές μήνα (ποσότητα)', 'heating.bought_value' => 'Αξία αγορών', 'heating.total' => 'Σύνολο διαθέσιμου',
                'heating.total_value' => 'Αξία διαθέσιμου', 'heating.end' => 'Υπόλοιπο λήξης (μέτρηση)', 'heating.end_value' => 'Αξία υπολοίπου λήξης',
                'heating.consumed' => 'Κατανάλωση', 'heating.price' => 'Μέση τιμή κατανάλωσης', 'heating.cost' => 'Κόστος κατανάλωσης',
                'heating.read_from' => 'Ημ/νία προηγ. μέτρησης', 'heating.read_to' => 'Ημ/νία τρέχουσας μέτρησης',
                'heating.fixed_pct' => 'Ποσοστό παγίου %', 'heating.unit' => 'Μονάδα (lt, m³…)',
            ],
        ];
    }

    /** Πεδία γραμμής ιδιοκτησίας (στήλες πίνακα κτιρίου ή πεδία ειδοποιητηρίου). */
    public static function unitFields(array $keys = []): array
    {
        $f = [
            'unit.row_no' => 'Α/Α', 'unit.code' => 'Κωδικός ιδιοκτησίας', 'unit.floor' => 'Όροφος', 'unit.label' => 'Ιδιοκτησία & όροφος',
            'unit.kind' => 'Είδος', 'unit.area' => 'Τ.μ.', 'unit.payer_name' => 'Ονοματεπώνυμο υπόχρεου', 'unit.owner_name' => 'Ιδιοκτήτης',
            'unit.tenant_name' => 'Ένοικος', 'unit.consumption' => 'Κατανάλωση θέρμανσης', 'unit.heat_coef' => 'Συντελεστής θέρμανσης',
            'unit.mills_line' => 'Χιλιοστά (μία γραμμή)',
            'unit.hours_prev' => 'Θέρμανση: προηγούμενη ένδειξη', 'unit.hours_curr' => 'Θέρμανση: τελευταία ένδειξη',
            'unit.heat_eps' => 'Θέρμανση: εi (μερίδιο)', 'unit.heat_fi' => 'Θέρμανση: fi % (απώλειες)', 'unit.heat_ei' => 'Θέρμανση: ei (θερμικός συντελεστής)',
            'amt.heating_fixed' => 'Θέρμανση: πάγιο (απώλειες)', 'amt.heating_var' => 'Θέρμανση: κατανάλωση', 'unit.heat_excluded' => 'Θέρμανση: εξαίρεση & λόγος',
            'amt.general' => 'Κοινόχρηστα', 'amt.elevator' => 'Ανελκυστήρας', 'amt.heating' => 'Θέρμανση', 'amt.special' => 'Ειδικές δαπάνες',
            'amt.owner' => 'Δαπάνες ιδιοκτήτη', 'amt.tenant' => 'Σύνολο ενοίκου', 'amt.current' => 'Σύνολο μήνα',
            'amt.previous' => 'Προηγούμενο υπόλοιπο', 'amt.total' => 'Πληρωτέο ποσό',
            'pay.date' => 'Είσπραξη: ημερομηνία', 'pay.amount' => 'Είσπραξη: ποσό', 'pay.method' => 'Είσπραξη: τρόπος', 'pay.note' => 'Είσπραξη: σημείωση',
        ];
        $keys = $keys ?: ['general' => 'Γενικά', 'elevator' => 'Ανελκυστήρας', 'heating' => 'Θέρμανση'];
        foreach ($keys as $code => $title) {
            $f['mills.' . $code] = 'Χιλιοστά: ' . $title;
        }
        return $f;
    }

    public static function expenseFields(): array
    {
        return [
            'exp.no' => 'Α/Α', 'exp.title' => 'Περιγραφή', 'exp.category' => 'Κατηγορία', 'exp.amount' => 'Ποσό',
            'exp.supplier' => 'Προμηθευτής', 'exp.doc' => 'Παραστατικό', 'exp.method' => 'Τρόπος κατανομής', 'exp.payer' => 'Βαρύνει',
        ];
    }

    public static function unitExpenseFields(): array
    {
        return ['uexp.title' => 'Περιγραφή δαπάνης', 'uexp.category' => 'Κατηγορία', 'uexp.amount' => 'Ποσό δαπάνης', 'uexp.share' => 'Μερίδιο ιδιοκτησίας'];
    }

    public static function images(): array
    {
        return ['account.logo' => 'Λογότυπο διαχειριστικής εταιρείας'];
    }

    // ------------------------------------------------------------------ έτοιμα πρότυπα

    private static function t(string $text, float $x, float $y, array $o = []): array
    {
        return array_merge(['type' => 'text', 'text' => $text, 'x' => $x, 'y' => $y, 'layer' => 'form', 'size' => 8], $o);
    }

    private static function f(string $field, float $x, float $y, float $w, array $o = []): array
    {
        return array_merge(['type' => 'field', 'field' => $field, 'x' => $x, 'y' => $y, 'w' => $w, 'layer' => 'data', 'size' => 9], $o);
    }

    private static function line(float $x, float $y, float $w, float $h = 0, array $o = []): array
    {
        return array_merge(['type' => 'line', 'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'layer' => 'form', 'stroke' => 0.3], $o);
    }

    private const NAVY = '#1e3a8a';
    private const INK = '#0f172a';
    private const MUTED = '#64748b';
    private const TINT = '#eef2fb';
    private const TINT2 = '#e3e9f8';
    private const SOFT = '#f5f7fa';
    private const LINE = '#b6c2d6';
    /** Έκδοση του σχεδίου των έτοιμων προτύπων (για αυτόματη ανανέωση στις αναβαθμίσεις). */
    public const DESIGN = 2;

    private static function label(string $text, float $x, float $y, array $o = []): array
    {
        return self::t($text, $x, $y, array_merge(['size' => 6.3, 'bold' => true, 'color' => self::MUTED], $o));
    }

    private static function panel(float $x, float $y, float $w, float $h, string $fill, array $o = []): array
    {
        return array_merge(['type' => 'box', 'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'layer' => 'form', 'stroke' => 0.01, 'color' => $fill, 'fill' => $fill, 'radius' => 3], $o);
    }

    private static function circle(float $cx, float $cy, float $d, string $fill): array
    {
        return self::panel($cx - $d / 2, $cy - $d / 2, $d, $d, $fill, ['radius' => $d / 2]);
    }

    /** Κεφαλίδα: λογότυπο (ή επωνυμία αν δεν υπάρχει) και από κάτω μία γραμμή επικοινωνίας· τίτλος δεξιά πάνω σε καμπύλες. */
    private static function header(string $title, float $titleSize = 15): array
    {
        return [
            self::circle(196, -6, 64, self::TINT),
            self::circle(158, 5, 14, self::TINT2),
            self::circle(118, 3, 6, self::TINT),
            ['type' => 'image', 'field' => 'account.logo', 'x' => 10, 'y' => 8, 'w' => 52, 'h' => 15, 'layer' => 'form'],
            self::f('account.name_nologo', 10, 11, 110, ['layer' => 'form', 'size' => 14, 'bold' => true, 'color' => self::INK]),
            self::f('account.line', 10, 24.6, 120, ['layer' => 'form', 'size' => 7.2, 'color' => '#475569']),
            self::t($title, 120, 10.5, ['w' => 76, 'align' => 'right', 'size' => 8.5, 'bold' => true, 'color' => self::NAVY]),
            self::f('period.title', 110, 15.4, 86, ['align' => 'right', 'size' => $titleSize, 'bold' => true, 'color' => self::INK]),
        ];
    }

    /** Υποσέλιδο σε σχήμα «χάπι». */
    private static function footer(float $y): array
    {
        return [
            self::panel(10, $y, 190, 9, self::SOFT, ['radius' => 4.5]),
            self::f('account.name', 15, $y + 2.6, 90, ['size' => 7, 'bold' => true, 'layer' => 'form', 'color' => '#334155']),
            self::f('account.website', 80, $y + 2.7, 60, ['size' => 6.8, 'align' => 'center', 'layer' => 'form', 'color' => self::MUTED]),
            self::panel(160, $y + 1.6, 37, 5.8, self::TINT2, ['radius' => 2.9]),
            self::f('page.label', 160, $y + 2.6, 37, ['size' => 6.8, 'bold' => true, 'align' => 'center', 'layer' => 'form', 'color' => self::NAVY]),
        ];
    }

    /** Κατάσταση κοινοχρήστων A4 κάθετη: μία σελίδα, πίνακας μόνο με τις ιδιοκτησίες του κτιρίου. */
    public static function sheet(): array
    {
        $cols = [
            ['field' => 'unit.code', 'title' => 'Διαμ.', 'w' => 12, 'align' => 'left', 'bold' => true],
            ['field' => 'unit.floor', 'title' => 'Όροφ.', 'w' => 12.5, 'align' => 'center'],
            ['field' => 'unit.payer_name', 'title' => 'Ονοματεπώνυμο', 'w' => 37.5, 'align' => 'left'],
            ['field' => 'mills.general', 'title' => "Χιλ.\nΚοιν.", 'w' => 13, 'align' => 'right'],
            ['field' => 'mills.elevator', 'title' => "Χιλ.\nΑνελκ.", 'w' => 13, 'align' => 'right'],
            ['field' => 'mills.heating', 'title' => "Χιλ.\nΘέρμ.", 'w' => 13, 'align' => 'right'],
            ['field' => 'amt.general', 'title' => 'Κοινόχρ.', 'w' => 15, 'align' => 'right'],
            ['field' => 'amt.elevator', 'title' => 'Ανελκ.', 'w' => 14, 'align' => 'right'],
            ['field' => 'amt.heating', 'title' => 'Θέρμανση', 'w' => 15, 'align' => 'right'],
            ['field' => 'amt.special', 'title' => 'Ειδικές', 'w' => 14, 'align' => 'right'],
            ['field' => 'amt.owner', 'title' => "Ιδιο-\nκτήτη", 'w' => 14, 'align' => 'right'],
            ['field' => 'amt.current', 'title' => 'Σύνολο', 'w' => 17, 'align' => 'right', 'bold' => true],
        ];
        $F = ['follow' => 'units', 'last_only' => true];
        $tot = ['size' => 8, 'align' => 'right', 'bold' => true] + $F;
        $el = array_merge(self::header('ΚΑΤΑΣΤΑΣΗ ΚΟΙΝΟΧΡΗΣΤΩΝ'), [
            // στοιχεία κτιρίου
            self::panel(10, 31, 190, 14, self::SOFT),
            self::label('ΚΤΙΡΙΟ', 15, 33), self::f('building.title', 15, 36.6, 72, ['size' => 9, 'bold' => true, 'color' => self::INK]),
            self::label('ΔΙΕΥΘΥΝΣΗ', 90, 33), self::f('building.location', 90, 36.8, 54, ['size' => 8.2, 'color' => self::INK]),
            self::label('ΕΚΔΟΣΗ', 147, 33), self::f('period.issue_date', 147, 36.6, 24, ['size' => 9, 'color' => self::INK]),
            self::panel(171, 32.6, 26, 10.8, '#ffffff', ['radius' => 2.4]),
            self::label('ΠΛΗΡΩΜΗ ΕΩΣ', 171, 34, ['w' => 26, 'align' => 'center']),
            self::f('period.due_date', 171, 37.4, 26, ['size' => 9, 'bold' => true, 'align' => 'center', 'color' => self::NAVY]),
            // πίνακας ιδιοκτησιών: μόνο όσες γραμμές χρειάζονται, ό,τι ακολουθεί ανεβαίνει
            [
                'type' => 'table', 'source' => 'units', 'x' => 10, 'y' => 58, 'row_h' => 6, 'rows' => 25, 'size' => 8, 'fit' => true,
                'header_h' => 8.5, 'header_size' => 6.8, 'header_fill' => self::TINT2, 'header_color' => self::NAVY, 'grid' => true, 'stroke' => 0.2,
                'grid_color' => self::LINE, 'radius' => 2.5, 'columns' => $cols,
            ],
            self::panel(10, 209.2, 190, 7, self::TINT, ['radius' => 2.5] + $F),
            self::t('ΣΥΝΟΛΑ', 14, 211.1, ['bold' => true, 'size' => 7.5, 'color' => self::NAVY] + $F),
            self::f('totals.mills.general', 72, 210.9, 13, $tot + ['bold' => false]),
            self::f('totals.mills.elevator', 85, 210.9, 13, $tot + ['bold' => false]),
            self::f('totals.mills.heating', 98, 210.9, 13, $tot + ['bold' => false]),
            self::f('totals.general', 111, 210.9, 15, $tot),
            self::f('totals.elevator', 126, 210.9, 14, $tot),
            self::f('totals.heating', 140, 210.9, 15, $tot),
            self::f('totals.special', 155, 210.9, 14, $tot),
            self::f('totals.owner', 169, 210.9, 14, $tot),
            self::f('totals.current', 183, 210.9, 17, $tot + ['color' => self::NAVY]),
            // ανάλυση δαπανών (αριστερά) — όλες στην ίδια σελίδα
            self::panel(10, 221.6, 2.2, 4, self::NAVY, ['radius' => 1.1] + $F),
            self::t('ΑΝΑΛΥΣΗ ΔΑΠΑΝΩΝ', 14, 221.8, ['bold' => true, 'size' => 8, 'color' => self::NAVY] + $F),
            [
                'type' => 'table', 'source' => 'expenses', 'x' => 10, 'y' => 233, 'row_h' => 5, 'rows' => 8, 'size' => 7.5, 'fit' => true,
                'max_y' => 278, 'min_row_h' => 3.6, 'follow' => 'units', 'last_only' => true,
                'header_h' => 5.5, 'header_size' => 6.8, 'header_fill' => self::TINT2, 'header_color' => self::NAVY, 'grid' => true, 'stroke' => 0.18,
                'grid_color' => self::LINE, 'radius' => 2.2,
                'columns' => [
                    ['field' => 'exp.no', 'title' => 'Α/Α', 'w' => 8, 'align' => 'center'],
                    ['field' => 'exp.title', 'title' => 'Περιγραφή', 'w' => 66, 'align' => 'left'],
                    ['field' => 'exp.category', 'title' => 'Κατηγορία', 'w' => 26, 'align' => 'left'],
                    ['field' => 'exp.amount', 'title' => 'Ποσό €', 'w' => 18, 'align' => 'right'],
                ],
            ],
            self::panel(84, 274.6, 44, 6, self::TINT, ['radius' => 3, 'follow' => 'expenses', 'last_only' => true]),
            self::t('Σύνολο δαπανών', 86, 276.1, ['w' => 22, 'size' => 7, 'bold' => true, 'color' => self::NAVY, 'follow' => 'expenses', 'last_only' => true]),
            self::f('totals.expenses', 108, 275.8, 18, ['align' => 'right', 'bold' => true, 'size' => 8.5, 'color' => self::INK, 'follow' => 'expenses', 'last_only' => true]),
            // ανακοινώσεις & διαχειριστής (δεξιά)
            self::panel(134, 221, 66, 30, self::SOFT, $F),
            self::label('ΑΝΑΚΟΙΝΩΣΕΙΣ', 138, 223.4, $F),
            self::f('period.notes', 138, 227.2, 58, ['h' => 22, 'wrap' => true, 'size' => 7.5, 'color' => self::INK] + $F),
            self::panel(134, 254, 66, 27, self::TINT, $F),
            self::label('ΔΙΑΧΕΙΡΙΣΤΗΣ', 138, 256.4, ['color' => self::NAVY] + $F),
            self::f('manager.name', 138, 260.2, 60, ['size' => 8.5, 'bold' => true, 'color' => self::INK] + $F),
            self::f('manager.phone', 138, 264.4, 60, ['size' => 7.5, 'prefix' => 'Τηλ. ', 'hide_empty' => true, 'color' => '#334155'] + $F),
            self::f('manager.email', 138, 268, 60, ['size' => 7.5, 'color' => '#334155'] + $F),
            self::f('building.iban', 138, 272.4, 60, ['size' => 7.3, 'bold' => true, 'prefix' => 'IBAN ', 'hide_empty' => true, 'color' => self::NAVY] + $F),
            self::f('manager.hours', 138, 276.2, 60, ['size' => 7, 'color' => self::MUTED] + $F),
        ], self::footer(284));
        return ['v' => self::DESIGN, 'orientation' => 'portrait', 'font' => 'Geom', 'offset_x' => 0, 'offset_y' => 0, 'per_page' => 1, 'elements' => self::ids($el)];
    }

    /** Ειδοποιητήρια: 2 ανά σελίδα A4, με γραμμή κοπής. */
    public static function notice(): array
    {
        $amounts = [
            ['Κοινόχρηστα', 'amt.general'], ['Ανελκυστήρας', 'amt.elevator'], ['Θέρμανση', 'amt.heating'],
            ['Ειδικές δαπάνες', 'amt.special'], ['Δαπάνες ιδιοκτήτη', 'amt.owner'], ['Σύνολο μήνα', 'amt.current'],
            ['Προηγούμενο υπόλοιπο', 'amt.previous'],
        ];
        $lbl = ['size' => 7.3, 'color' => self::MUTED, 'bold' => false];
        $el = array_merge(self::header('ΕΙΔΟΠΟΙΗΤΗΡΙΟ ΚΟΙΝΟΧΡΗΣΤΩΝ', 13), [
            self::panel(10, 30, 98, 30, self::SOFT),
            self::t('Κτίριο', 14, 32.6, $lbl), self::f('building.title', 34, 32.2, 72, ['size' => 9, 'bold' => true, 'color' => self::INK]),
            self::t('Διεύθυνση', 14, 37.8, $lbl), self::f('building.location', 34, 37.6, 72, ['size' => 7.8, 'color' => self::INK]),
            self::t('Ιδιοκτησία', 14, 43, $lbl), self::f('unit.label', 34, 42.6, 72, ['size' => 9, 'bold' => true, 'color' => self::INK]),
            self::t('Υπόχρεος', 14, 48.2, $lbl), self::f('unit.payer_name', 34, 47.8, 72, ['size' => 9, 'bold' => true, 'color' => self::INK]),
            self::t('Χιλιοστά', 14, 53.4, $lbl), self::f('unit.mills_line', 34, 53.4, 72, ['size' => 7, 'color' => '#334155']),
            [
                'type' => 'table', 'source' => 'unit_expenses', 'x' => 10, 'y' => 70, 'row_h' => 4.6, 'rows' => 8, 'size' => 7.3, 'fit' => true,
                'header_h' => 5.5, 'header_size' => 6.8, 'header_fill' => self::TINT2, 'header_color' => self::NAVY, 'grid' => true, 'stroke' => 0.18,
                'grid_color' => self::LINE, 'radius' => 2.2, 'paginate' => false,
                'columns' => [
                    ['field' => 'uexp.title', 'title' => 'Ανάλυση δαπανών', 'w' => 54, 'align' => 'left'],
                    ['field' => 'uexp.amount', 'title' => 'Δαπάνη €', 'w' => 21, 'align' => 'right'],
                    ['field' => 'uexp.share', 'title' => 'Αναλογεί €', 'w' => 23, 'align' => 'right', 'bold' => true],
                ],
            ],
            ['type' => 'box', 'x' => 114, 'y' => 30, 'w' => 86, 'h' => 42, 'layer' => 'form', 'stroke' => 0.25, 'color' => '#cbd5e1', 'radius' => 3],
        ]);
        $y = 32.4;
        foreach ($amounts as [$label, $field]) {
            $bold = $field === 'amt.current';
            if ($bold) $el[] = self::line(118, $y - 1.2, 78, 0, ['stroke' => 0.15, 'color' => '#cbd5e1']);
            $el[] = self::t($label, 118, $y + 0.3, ['size' => 8, 'bold' => $bold, 'color' => $bold ? self::INK : '#334155']);
            $el[] = self::f($field, 160, $y, 36, ['align' => 'right', 'size' => 8.5, 'bold' => $bold, 'color' => self::INK]);
            $y += 5.5;
        }
        $el[] = self::panel(114, 74, 86, 13, self::TINT2, ['radius' => 6.5]);
        $el[] = self::t('ΠΛΗΡΩΤΕΟ ΠΟΣΟ', 120, 78.6, ['bold' => true, 'size' => 8.5, 'color' => self::NAVY]);
        $el[] = self::f('amt.total', 145, 76.8, 50, ['align' => 'right', 'bold' => true, 'size' => 14.5, 'suffix' => ' €', 'color' => self::INK]);
        $el[] = self::t('Πληρωμή έως', 116, 91.2, ['size' => 7.3, 'color' => self::MUTED]);
        $el[] = self::f('period.due_date', 136, 90.8, 40, ['bold' => true, 'size' => 9, 'color' => self::NAVY]);
        $el[] = self::f('building.iban', 116, 95.8, 84, ['size' => 7.5, 'bold' => true, 'prefix' => 'IBAN ', 'hide_empty' => true, 'color' => self::INK]);
        $el[] = self::f('building.beneficiary', 116, 99.6, 84, ['size' => 7, 'prefix' => 'Δικαιούχος: ', 'hide_empty' => true, 'color' => '#334155']);
        $el[] = self::f('period.notes', 116, 104, 84, ['h' => 8, 'wrap' => true, 'size' => 7, 'color' => self::INK]);
        $el[] = self::panel(10, 115, 190, 8, self::SOFT, ['radius' => 4]);
        $el[] = self::f('manager.contact', 15, 117.2, 180, ['size' => 7, 'prefix' => 'Διαχειριστής: ', 'hide_empty' => true, 'color' => '#334155']);
        $el[] = self::line(0, 148.1, 210, 0, ['stroke' => 0.2, 'dashed' => true, 'color' => self::LINE]);
        return ['v' => self::DESIGN, 'orientation' => 'portrait', 'font' => 'Geom', 'offset_x' => 0, 'offset_y' => 0, 'per_page' => 2, 'pitch' => 148.5, 'elements' => self::ids($el)];
    }

    /** Φύλλο διαχειριστή: βεβαίωση εισπράξεων με ημερομηνία, για το αρχείο. */
    public static function ledger(): array
    {
        $F = ['follow' => 'units'];
        $el = array_merge(self::header('ΦΥΛΛΟ ΔΙΑΧΕΙΡΙΣΤΗ · ΕΙΣΠΡΑΞΕΙΣ'), [
            self::panel(10, 31, 190, 14, self::SOFT),
            self::label('ΚΤΙΡΙΟ', 15, 33), self::f('building.title', 15, 36.6, 72, ['size' => 9, 'bold' => true, 'color' => self::INK]),
            self::label('ΔΙΕΥΘΥΝΣΗ', 90, 33), self::f('building.location', 90, 36.8, 54, ['size' => 8.2, 'color' => self::INK]),
            self::label('ΕΚΔΟΣΗ', 147, 33), self::f('period.issue_date', 147, 36.6, 24, ['size' => 9, 'color' => self::INK]),
            self::label('ΠΛΗΡΩΜΗ ΕΩΣ', 172, 33, ['w' => 25, 'align' => 'right']), self::f('period.due_date', 172, 36.6, 25, ['size' => 9, 'bold' => true, 'align' => 'right', 'color' => self::NAVY]),
            [
                'type' => 'table', 'source' => 'units', 'x' => 10, 'y' => 58, 'row_h' => 9, 'rows' => 22, 'size' => 8.2, 'fit' => true,
                'header_h' => 8.5, 'header_size' => 6.8, 'header_fill' => self::TINT2, 'header_color' => self::NAVY, 'grid' => true, 'stroke' => 0.2,
                'grid_color' => self::LINE, 'radius' => 2.5,
                'columns' => [
                    ['field' => 'unit.code', 'title' => 'Διαμ.', 'w' => 12, 'align' => 'left', 'bold' => true],
                    ['field' => 'unit.floor', 'title' => 'Όροφ.', 'w' => 12.5, 'align' => 'center'],
                    ['field' => 'unit.payer_name', 'title' => 'Υπόχρεος', 'w' => 41.5, 'align' => 'left'],
                    ['field' => 'amt.total', 'title' => "Πληρωτέο\n€", 'w' => 20, 'align' => 'right', 'bold' => true],
                    ['field' => 'pay.date', 'title' => "Ημ/νία\nπληρωμής", 'w' => 22, 'align' => 'center'],
                    ['field' => 'pay.amount', 'title' => "Ποσό\n€", 'w' => 19, 'align' => 'right'],
                    ['field' => 'pay.method', 'title' => 'Τρόπος', 'w' => 21, 'align' => 'center'],
                    ['field' => 'pay.note', 'title' => "Υπογραφή /\nπαρατηρήσεις", 'w' => 42, 'align' => 'left'],
                ],
            ],
            self::panel(10, 257.8, 190, 7, self::TINT, ['radius' => 2.5] + $F),
            self::t('ΣΥΝΟΛΑ', 14, 259.7, ['bold' => true, 'size' => 7.5, 'color' => self::NAVY] + $F),
            self::f('totals.payable', 76, 259.5, 20, ['size' => 8, 'align' => 'right', 'bold' => true, 'color' => self::NAVY] + $F),
            self::f('totals.paid', 118, 259.5, 19, ['size' => 8, 'align' => 'right', 'bold' => true, 'color' => self::INK] + $F),
            self::f('totals.unpaid', 158, 259.6, 39, ['size' => 7.3, 'align' => 'right', 'bold' => true, 'prefix' => 'Ανεξόφλητα: ', 'hide_empty' => true, 'color' => '#b45309'] + $F),
            // βεβαίωση & υπογραφή
            self::t('Βεβαιώνεται ότι εισπράχθηκαν τα ανωτέρω ποσά κατά τις αναγραφόμενες ημερομηνίες.', 10, 270, ['size' => 7.5, 'color' => '#334155', 'w' => 120] + $F),
            self::t('Ημερομηνία', 10, 278.2, ['size' => 7, 'color' => self::MUTED] + $F),
            self::line(28, 281, 40, 0, ['stroke' => 0.2, 'color' => '#94a3b8'] + $F),
            self::panel(134, 266, 66, 16.5, self::SOFT, $F),
            self::label('Ο ΔΙΑΧΕΙΡΙΣΤΗΣ', 138, 268.2, $F),
            self::f('manager.name', 138, 271.6, 58, ['size' => 8, 'bold' => true, 'color' => self::INK] + $F),
            self::line(138, 279.8, 58, 0, ['stroke' => 0.2, 'color' => '#94a3b8'] + $F),
            self::t('υπογραφή', 138, 280.4, ['w' => 58, 'align' => 'center', 'size' => 6, 'color' => self::MUTED] + $F),
        ], self::footer(286));
        return ['v' => self::DESIGN, 'orientation' => 'portrait', 'font' => 'Geom', 'offset_x' => 0, 'offset_y' => 0, 'per_page' => 1, 'elements' => self::ids($el)];
    }

    public static function forType(string $type): array
    {
        return $type === 'notice' ? self::notice() : ($type === 'ledger' ? self::ledger() : self::sheet());
    }

    private static function ids(array $el): array
    {
        foreach ($el as $i => &$e) {
            $e['id'] = 'e' . ($i + 1);
        }
        return $el;
    }

    /** Καθαρίζει ένα layout που ήρθε από τον σχεδιαστή (τιμές, τύποι, όρια). */
    public static function sanitize($layout): array
    {
        if (!is_array($layout)) {
            return ['orientation' => 'portrait', 'elements' => []];
        }
        $num = static fn($v, $min, $max) => max($min, min($max, round((float) $v, 2)));
        $str = static fn($v, $len = 300) => mb_substr(trim((string) $v), 0, $len);
        $out = [
            'v' => (int) ($layout['v'] ?? 1),
            'orientation' => ($layout['orientation'] ?? '') === 'landscape' ? 'landscape' : 'portrait',
            'font' => preg_replace('~[^A-Za-z0-9 \'-]~', '', (string) ($layout['font'] ?? 'Arial')) ?: 'Arial',
            'offset_x' => $num($layout['offset_x'] ?? 0, -30, 30),
            'offset_y' => $num($layout['offset_y'] ?? 0, -30, 30),
            'per_page' => (int) $num($layout['per_page'] ?? 1, 1, 4),
            'pitch' => $num($layout['pitch'] ?? 148.5, 20, 297),
            'bg_opacity' => $num($layout['bg_opacity'] ?? 0.45, 0, 1),
            'elements' => [],
        ];
        $types = ['field', 'text', 'line', 'box', 'image', 'table'];
        foreach (array_slice((array) ($layout['elements'] ?? []), 0, 400) as $i => $e) {
            if (!is_array($e) || !in_array($e['type'] ?? '', $types, true)) continue;
            $c = [
                'id' => preg_replace('~[^a-z0-9_-]~i', '', (string) ($e['id'] ?? '')) ?: 'e' . ($i + 1),
                'type' => $e['type'],
                'x' => $num($e['x'] ?? 0, -20, 320), 'y' => $num($e['y'] ?? 0, -20, 320),
                'w' => $num($e['w'] ?? 0, 0, 320), 'h' => $num($e['h'] ?? 0, 0, 320),
                'layer' => ($e['layer'] ?? 'data') === 'form' ? 'form' : 'data',
                'size' => $num($e['size'] ?? 9, 4, 48),
            ];
            if (in_array($e['follow'] ?? '', ['units', 'expenses'], true)) $c['follow'] = $e['follow'];
            if (isset($e['max_y']) && (float) $e['max_y'] > 0) $c['max_y'] = $num($e['max_y'], 0, 320);
            if (isset($e['min_row_h'])) $c['min_row_h'] = $num($e['min_row_h'], 2.5, 20);
            foreach (['bold', 'italic', 'wrap', 'hide_empty', 'upper', 'dashed', 'grid', 'fit', 'last_only'] as $flag) {
                if (!empty($e[$flag])) $c[$flag] = true;
            }
            foreach (['color', 'fill', 'header_fill', 'grid_color', 'header_color'] as $col) {
                if (!empty($e[$col]) && preg_match('~^#[0-9a-fA-F]{3,8}$~', (string) $e[$col])) $c[$col] = $e[$col];
            }
            $c['align'] = in_array($e['align'] ?? '', ['left', 'center', 'right'], true) ? $e['align'] : 'left';
            if (isset($e['stroke'])) $c['stroke'] = $num($e['stroke'], 0.01, 3);
            if (isset($e['radius'])) $c['radius'] = $num($e['radius'], 0, 20);
            if ($c['type'] === 'text') $c['text'] = $str($e['text'] ?? '', 2000);
            if (in_array($c['type'], ['field', 'image'], true)) {
                $c['field'] = preg_replace('~[^a-z0-9_.]~i', '', (string) ($e['field'] ?? ''));
                $c['prefix'] = mb_substr((string) ($e['prefix'] ?? ''), 0, 80);
                $c['suffix'] = mb_substr((string) ($e['suffix'] ?? ''), 0, 80);
            }
            if ($c['type'] === 'table') {
                $c['source'] = in_array($e['source'] ?? '', ['units', 'expenses', 'unit_expenses'], true) ? $e['source'] : 'units';
                $c['row_h'] = $num($e['row_h'] ?? 6, 2, 50);
                $c['rows'] = (int) $num($e['rows'] ?? 20, 1, 200);
                $c['header_h'] = $num($e['header_h'] ?? 0, 0, 40);
                $c['header_size'] = $num($e['header_size'] ?? $c['size'], 4, 30);
                $c['paginate'] = !isset($e['paginate']) || !empty($e['paginate']);
                $c['columns'] = [];
                foreach (array_slice((array) ($e['columns'] ?? []), 0, 40) as $col) {
                    if (!is_array($col)) continue;
                    $c['columns'][] = [
                        'field' => preg_replace('~[^a-z0-9_.]~i', '', (string) ($col['field'] ?? '')),
                        'title' => $str($col['title'] ?? '', 80),
                        'w' => $num($col['w'] ?? 10, 1, 300),
                        'align' => in_array($col['align'] ?? '', ['left', 'center', 'right'], true) ? $col['align'] : 'left',
                        'bold' => !empty($col['bold']),
                    ];
                }
            }
            $out['elements'][] = $c;
        }
        return $out;
    }

    /** Δείγμα δεδομένων για τον σχεδιαστή και την προεπισκόπηση προτύπων. */
    public static function sample(): array
    {
        $names = ['Παπαδόπουλος Γεώργιος', 'Ιωάννου Μαρία', 'Κωνσταντίνου Νίκος', 'Γεωργίου Ελένη', 'Δημητρίου Αλέξανδρος', 'Νικολάου Σοφία', 'Αθανασίου Πέτρος', 'Βασιλείου Κατερίνα', 'Χριστοδούλου Άννα', 'Μιχαηλίδης Σπύρος'];
        $floors = ['Ισόγ.', 'Α΄', 'Α΄', 'Β΄', 'Β΄', 'Γ΄', 'Γ΄', 'Δ΄', 'Δ΄', 'Ε΄'];
        $units = [];
        $mills = [72, 95, 98, 100, 102, 104, 106, 108, 110, 105];
        foreach ($names as $i => $n) {
            $g = $mills[$i] * 4.2;
            $units[] = [
                'unit.row_no' => (string) ($i + 1), 'unit.code' => ($i ? chr(64 + (int) ceil($i / 2)) . (2 - $i % 2) : 'Ι1'), 'unit.floor' => $floors[$i],
                'unit.label' => ($i ? chr(64 + (int) ceil($i / 2)) . (2 - $i % 2) : 'Ι1') . ' · ' . $floors[$i] . ' όροφος',
                'unit.payer_name' => $n, 'unit.owner_name' => $n, 'unit.tenant_name' => '', 'unit.kind' => 'Διαμέρισμα', 'unit.area' => (string) (60 + $i * 5),
                'mills.general' => Fmt::mills($mills[$i]), 'mills.elevator' => Fmt::mills($i ? $mills[$i] + 3 : 0, true), 'mills.heating' => Fmt::mills($mills[$i] - 1),
                'unit.mills_line' => 'Κοιν. ' . Fmt::mills($mills[$i]) . ' · Ανελκ. ' . Fmt::mills($i ? $mills[$i] + 3 : 0) . ' · Θέρμ. ' . Fmt::mills($mills[$i] - 1),
                'unit.consumption' => (string) (40 + $i * 7),
                'amt.general' => Fmt::money($g / 10), 'amt.elevator' => Fmt::money($i ? $mills[$i] * 0.08 : 0, true), 'amt.heating' => Fmt::money($mills[$i] * 0.9),
                'amt.special' => $i === 3 ? Fmt::money(25) : '', 'amt.owner' => Fmt::money($mills[$i] * 0.1), 'amt.tenant' => Fmt::money($g / 10 + $mills[$i]),
                'amt.current' => Fmt::money($g / 10 + $mills[$i] * 1.08), 'amt.previous' => $i === 4 ? Fmt::money(56.4) : Fmt::money(0),
                'amt.total' => Fmt::money($g / 10 + $mills[$i] * 1.08 + ($i === 4 ? 56.4 : 0)),
            ];
        }
        $exp = [
            ['ΔΕΗ κοινοχρήστων χώρων', 'Κοινόχρηστα', 86.4], ['ΕΥΔΑΠ', 'Κοινόχρηστα', 42.1], ['Καθαρισμός κλιμακοστασίου', 'Κοινόχρηστα', 120],
            ['Συντήρηση ανελκυστήρα', 'Ανελκυστήρας', 80], ['Πετρέλαιο θέρμανσης', 'Θέρμανση', 910.5], ['Αμοιβή διαχείρισης', 'Κοινόχρηστα', 60],
        ];
        $expenses = [];
        foreach ($exp as $i => [$t, $c, $a]) {
            $expenses[] = ['exp.no' => (string) ($i + 1), 'exp.title' => $t, 'exp.category' => $c, 'exp.amount' => Fmt::money($a), 'exp.supplier' => '', 'exp.doc' => '', 'exp.method' => 'Με χιλιοστά', 'exp.payer' => 'Ένοικος'];
        }
        $uexp = [];
        foreach ($units as $i => $u) {
            $uexp[$i] = array_map(static fn($e) => ['uexp.title' => $e['exp.title'], 'uexp.category' => $e['exp.category'], 'uexp.amount' => $e['exp.amount'], 'uexp.share' => Fmt::money(Calc::parseNumber($e['exp.amount']) * ($mills[$i] / 1000))], $expenses);
        }
        $fields = [
            'account.name' => 'Διαχειρίσεις Αττικής', 'account.address' => 'Λεωφ. Κηφισίας 100, 11526 Αθήνα', 'account.phone' => '210 1234567',
            'account.email' => 'info@example.gr', 'account.website' => 'www.example.gr', 'account.afm' => '099999999', 'account.contact' => '210 1234567 · info@example.gr',
            'building.title' => 'Πολυκατοικία Αγίου Νικολάου 12', 'building.address' => 'Αγίου Νικολάου 12, Παγκράτι, 11635 Αθήνα', 'building.street' => 'Αγίου Νικολάου 12',
            'building.area' => 'Παγκράτι', 'building.city' => 'Αθήνα', 'building.postcode' => '11635', 'building.afm' => '', 'building.iban' => 'GR16 0110 1250 0000 0001 2300 695',
            'building.bank' => 'Εθνική Τράπεζα', 'building.beneficiary' => 'Πολυκατοικία Αγ. Νικολάου 12', 'building.units_count' => '10',
            'manager.name' => 'Κώστας Παπαδάκης', 'manager.phone' => '210 1234567', 'manager.mobile' => '690 0000000', 'manager.email' => 'diaxeirisi@example.gr',
            'manager.address' => 'Λεωφ. Κηφισίας 100', 'manager.hours' => 'Δευτ.–Παρ. 09:00–17:00', 'manager.contact' => 'Κώστας Παπαδάκης · 210 1234567 · diaxeirisi@example.gr',
            'rep.name' => 'Ιωάννου Μαρία', 'rep.phone' => '697 1111111',
            'period.title' => 'Οκτώβριος 2026', 'period.month' => 'Οκτώβριος', 'period.year' => '2026', 'period.issue_date' => '05/11/2026', 'period.due_date' => '15/11/2026',
            'period.notes' => "Η γενική συνέλευση θα γίνει την Πέμπτη 20/11 στις 19:00 στην είσοδο.\nΠαρακαλούμε για την έγκαιρη εξόφληση.",
            'print.date' => date('d/m/Y'),
            'totals.general' => '420,00', 'totals.elevator' => '80,00', 'totals.heating' => '910,50', 'totals.special' => '25,00', 'totals.owner' => '100,00',
            'totals.current' => '1.535,50', 'totals.expenses' => '1.299,00', 'totals.mills.general' => '1.000', 'totals.mills.elevator' => '1.000', 'totals.mills.heating' => '1.000',
            'heating.start' => '280', 'heating.start_value' => '308,00', 'heating.bought' => '700', 'heating.bought_value' => '924,00', 'heating.total' => '980', 'heating.total_value' => '1.232,00',
            'heating.end' => '560', 'heating.end_value' => '739,20', 'heating.consumed' => '420', 'heating.price' => '1,1733', 'heating.cost' => '492,80', 'heating.read_from' => '01/10/2026', 'heating.read_to' => '01/11/2026',
            'heating.fixed_pct' => '30', 'heating.unit' => 'lt',
        ];
        $logo = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="120" height="50"><rect x="1" y="1" width="118" height="48" rx="6" fill="none" stroke="#1e3a8a" stroke-width="2"/><text x="60" y="31" font-family="Arial" font-size="15" text-anchor="middle" fill="#1e3a8a">ΛΟΓΟΤΥΠΟ</text></svg>');
        return ['fields' => $fields, 'units' => $units, 'expenses' => $expenses, 'unit_expenses' => $uexp, 'images' => ['account.logo' => $logo]];
    }
}
