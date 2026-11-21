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
    ];

    /** Πεδία κεφαλίδας: διαθέσιμα σε όλα τα πρότυπα. */
    public static function headerFields(): array
    {
        return [
            'Διαχειριστική εταιρεία' => [
                'account.name' => 'Επωνυμία', 'account.address' => 'Διεύθυνση', 'account.phone' => 'Τηλέφωνο',
                'account.email' => 'Email', 'account.website' => 'Website', 'account.afm' => 'ΑΦΜ', 'account.contact' => 'Τηλέφωνο · email (μία γραμμή)',
            ],
            'Κτίριο' => [
                'building.title' => 'Ονομασία', 'building.address' => 'Πλήρης διεύθυνση', 'building.street' => 'Οδός & αριθμός',
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
    private const SOFT = '#f5f7fa';
    private const LINE = '#94a3b8';

    private static function label(string $text, float $x, float $y, array $o = []): array
    {
        return self::t($text, $x, $y, array_merge(['size' => 6.5, 'bold' => true, 'color' => self::MUTED], $o));
    }

    private static function panel(float $x, float $y, float $w, float $h, string $fill, array $o = []): array
    {
        return array_merge(['type' => 'box', 'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'layer' => 'form', 'stroke' => 0.01, 'color' => $fill, 'fill' => $fill, 'radius' => 1.6], $o);
    }

    /** Κατάσταση κοινοχρήστων A4 κάθετη: 25 ιδιοκτησίες ανά σελίδα. */
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
        $tot = ['size' => 8, 'align' => 'right', 'bold' => true];
        $el = [
            // κεφαλίδα: εταιρεία αριστερά, τίτλος σε απαλό πλαίσιο δεξιά
            ['type' => 'image', 'field' => 'account.logo', 'x' => 10, 'y' => 9, 'w' => 36, 'h' => 17, 'layer' => 'form'],
            self::f('account.name', 50, 10, 74, ['layer' => 'form', 'size' => 10.5, 'bold' => true, 'color' => self::INK]),
            self::f('account.contact', 50, 15.6, 74, ['layer' => 'form', 'size' => 7.5, 'color' => '#475569']),
            self::f('account.address', 50, 19.4, 74, ['layer' => 'form', 'size' => 7.5, 'color' => '#475569']),
            self::panel(128, 9, 72, 18, self::TINT, ['radius' => 2.2]),
            self::t('ΚΑΤΑΣΤΑΣΗ ΚΟΙΝΟΧΡΗΣΤΩΝ', 131, 11.2, ['w' => 66, 'align' => 'right', 'size' => 8.5, 'bold' => true, 'color' => self::NAVY]),
            self::f('period.title', 131, 16.2, 66, ['align' => 'right', 'size' => 14, 'bold' => true, 'color' => self::INK]),
            // στοιχεία κτιρίου σε λωρίδα
            self::panel(10, 31, 190, 14, self::SOFT),
            self::label('ΚΤΙΡΙΟ', 14, 33), self::f('building.title', 14, 36.6, 63, ['size' => 9, 'bold' => true, 'color' => self::INK]),
            self::label('ΔΙΕΥΘΥΝΣΗ', 80, 33), self::f('building.address', 80, 36.8, 64, ['size' => 8, 'color' => self::INK]),
            self::label('ΕΚΔΟΣΗ', 148, 33), self::f('period.issue_date', 148, 36.6, 24, ['size' => 9, 'color' => self::INK]),
            self::label('ΠΛΗΡΩΜΗ ΕΩΣ', 172, 33, ['w' => 26, 'align' => 'right']), self::f('period.due_date', 172, 36.6, 26, ['size' => 9, 'bold' => true, 'align' => 'right', 'color' => self::NAVY]),
            // πίνακας ιδιοκτησιών
            [
                'type' => 'table', 'source' => 'units', 'x' => 10, 'y' => 57, 'row_h' => 6, 'rows' => 25, 'size' => 8,
                'header_h' => 8, 'header_size' => 6.8, 'header_fill' => '#e8edf7', 'header_color' => self::NAVY, 'grid' => true, 'stroke' => 0.2,
                'grid_color' => self::LINE, 'columns' => $cols,
            ],
            self::panel(10, 207.6, 190, 6.6, '#e8edf7', ['radius' => 0]),
            self::t('ΣΥΝΟΛΑ', 12, 209.2, ['bold' => true, 'size' => 7.5, 'color' => self::NAVY]),
            self::f('totals.mills.general', 72, 209, 13, $tot + ['bold' => false]),
            self::f('totals.mills.elevator', 85, 209, 13, $tot + ['bold' => false]),
            self::f('totals.mills.heating', 98, 209, 13, $tot + ['bold' => false]),
            self::f('totals.general', 111, 209, 15, $tot),
            self::f('totals.elevator', 126, 209, 14, $tot),
            self::f('totals.heating', 140, 209, 15, $tot),
            self::f('totals.special', 155, 209, 14, $tot),
            self::f('totals.owner', 169, 209, 14, $tot),
            self::f('totals.current', 183, 209, 17, $tot + ['color' => self::NAVY]),
            // ανάλυση δαπανών
            self::t('ΑΝΑΛΥΣΗ ΔΑΠΑΝΩΝ', 10, 218.5, ['bold' => true, 'size' => 8, 'color' => self::NAVY]),
            [
                'type' => 'table', 'source' => 'expenses', 'x' => 10, 'y' => 228, 'row_h' => 5, 'rows' => 9, 'size' => 7.5,
                'header_h' => 5, 'header_size' => 6.8, 'header_fill' => '#e8edf7', 'header_color' => self::NAVY, 'grid' => true, 'stroke' => 0.18,
                'grid_color' => self::LINE,
                'columns' => [
                    ['field' => 'exp.no', 'title' => 'Α/Α', 'w' => 8, 'align' => 'center'],
                    ['field' => 'exp.title', 'title' => 'Περιγραφή', 'w' => 66, 'align' => 'left'],
                    ['field' => 'exp.category', 'title' => 'Κατηγορία', 'w' => 26, 'align' => 'left'],
                    ['field' => 'exp.amount', 'title' => 'Ποσό €', 'w' => 18, 'align' => 'right'],
                ],
            ],
            self::t('Σύνολο δαπανών', 72, 274.6, ['w' => 36, 'align' => 'right', 'bold' => true, 'size' => 7.5, 'color' => self::MUTED]),
            self::f('totals.expenses', 110, 274.3, 18, ['align' => 'right', 'bold' => true, 'size' => 8.5, 'color' => self::INK]),
            // ανακοινώσεις & διαχειριστής
            self::panel(134, 217, 66, 31, self::SOFT),
            self::label('ΑΝΑΚΟΙΝΩΣΕΙΣ', 137, 219.2),
            self::f('period.notes', 137, 223, 60, ['h' => 24, 'wrap' => true, 'size' => 7.5, 'color' => self::INK]),
            self::label('ΔΙΑΧΕΙΡΙΣΤΗΣ', 134, 252.5),
            self::f('manager.name', 134, 256.2, 66, ['size' => 8.5, 'bold' => true, 'color' => self::INK]),
            self::f('manager.phone', 134, 260.4, 66, ['size' => 7.5, 'prefix' => 'Τηλ. ', 'hide_empty' => true, 'color' => '#334155']),
            self::f('manager.email', 134, 264, 66, ['size' => 7.5, 'color' => '#334155']),
            self::f('manager.hours', 134, 267.6, 66, ['size' => 7.5, 'color' => '#334155']),
            self::f('building.iban', 134, 271.5, 66, ['size' => 7.5, 'bold' => true, 'prefix' => 'IBAN ', 'hide_empty' => true, 'color' => self::NAVY]),
            // υποσέλιδο
            self::line(10, 281.5, 190, 0, ['stroke' => 0.35, 'color' => self::NAVY]),
            self::f('account.name', 10, 283.6, 100, ['size' => 7, 'bold' => true, 'layer' => 'form', 'color' => '#334155']),
            self::f('account.contact', 10, 287.2, 100, ['size' => 6.5, 'layer' => 'form', 'color' => self::MUTED]),
            self::f('page.label', 130, 283.6, 70, ['size' => 7, 'bold' => true, 'align' => 'right', 'layer' => 'form', 'color' => '#334155']),
            self::f('print.date', 130, 287.2, 70, ['size' => 6.5, 'align' => 'right', 'prefix' => 'Εκτύπωση ', 'layer' => 'form', 'color' => self::MUTED]),
        ];
        return ['orientation' => 'portrait', 'font' => 'Geom', 'offset_x' => 0, 'offset_y' => 0, 'per_page' => 1, 'elements' => self::ids($el)];
    }

    /** Ειδοποιητήρια: 2 ανά σελίδα A4, με γραμμή κοπής. */
    public static function notice(): array
    {
        $amounts = [
            ['Κοινόχρηστα', 'amt.general'], ['Ανελκυστήρας', 'amt.elevator'], ['Θέρμανση', 'amt.heating'],
            ['Ειδικές δαπάνες', 'amt.special'], ['Δαπάνες ιδιοκτήτη', 'amt.owner'], ['Σύνολο μήνα', 'amt.current'],
            ['Προηγούμενο υπόλοιπο', 'amt.previous'],
        ];
        $lbl = ['size' => 7.5, 'color' => self::MUTED, 'bold' => false];
        $el = [
            ['type' => 'image', 'field' => 'account.logo', 'x' => 10, 'y' => 8, 'w' => 32, 'h' => 14, 'layer' => 'form'],
            self::f('account.name', 46, 8.6, 70, ['layer' => 'form', 'size' => 10, 'bold' => true, 'color' => self::INK]),
            self::f('account.contact', 46, 13.8, 70, ['layer' => 'form', 'size' => 7, 'color' => '#475569']),
            self::f('account.address', 46, 17.2, 70, ['layer' => 'form', 'size' => 7, 'color' => '#475569']),
            self::panel(122, 7.5, 78, 16, self::TINT, ['radius' => 2.2]),
            self::t('ΕΙΔΟΠΟΙΗΤΗΡΙΟ ΚΟΙΝΟΧΡΗΣΤΩΝ', 125, 9.6, ['w' => 72, 'align' => 'right', 'size' => 8, 'bold' => true, 'color' => self::NAVY]),
            self::f('period.title', 125, 14.2, 72, ['align' => 'right', 'size' => 13, 'bold' => true, 'color' => self::INK]),
            // στοιχεία ιδιοκτησίας
            self::panel(10, 27, 98, 30, self::SOFT),
            self::t('Κτίριο', 13, 29.4, $lbl), self::f('building.title', 34, 29, 72, ['size' => 9, 'bold' => true, 'color' => self::INK]),
            self::t('Διεύθυνση', 13, 34.6, $lbl), self::f('building.address', 34, 34.4, 72, ['size' => 7.5, 'color' => self::INK]),
            self::t('Ιδιοκτησία', 13, 39.8, $lbl), self::f('unit.label', 34, 39.4, 72, ['size' => 9, 'bold' => true, 'color' => self::INK]),
            self::t('Υπόχρεος', 13, 45, $lbl), self::f('unit.payer_name', 34, 44.6, 72, ['size' => 9, 'bold' => true, 'color' => self::INK]),
            self::t('Χιλιοστά', 13, 50.2, $lbl), self::f('unit.mills_line', 34, 50.2, 72, ['size' => 7, 'color' => '#334155']),
            [
                'type' => 'table', 'source' => 'unit_expenses', 'x' => 10, 'y' => 66, 'row_h' => 4.6, 'rows' => 9, 'size' => 7.3,
                'header_h' => 5.5, 'header_size' => 6.8, 'header_fill' => '#e8edf7', 'header_color' => self::NAVY, 'grid' => true, 'stroke' => 0.18,
                'grid_color' => self::LINE, 'paginate' => false,
                'columns' => [
                    ['field' => 'uexp.title', 'title' => 'Ανάλυση δαπανών', 'w' => 54, 'align' => 'left'],
                    ['field' => 'uexp.amount', 'title' => 'Δαπάνη €', 'w' => 21, 'align' => 'right'],
                    ['field' => 'uexp.share', 'title' => 'Αναλογεί €', 'w' => 23, 'align' => 'right', 'bold' => true],
                ],
            ],
            ['type' => 'box', 'x' => 114, 'y' => 27, 'w' => 86, 'h' => 42, 'layer' => 'form', 'stroke' => 0.25, 'color' => '#cbd5e1', 'radius' => 1.6],
        ];
        $y = 29.4;
        foreach ($amounts as [$label, $field]) {
            $bold = $field === 'amt.current';
            if ($bold) $el[] = self::line(117, $y - 1.2, 80, 0, ['stroke' => 0.15, 'color' => '#cbd5e1']);
            $el[] = self::t($label, 117, $y + 0.3, ['size' => 8, 'bold' => $bold, 'color' => $bold ? self::INK : '#334155']);
            $el[] = self::f($field, 160, $y, 37, ['align' => 'right', 'size' => 8.5, 'bold' => $bold, 'color' => self::INK]);
            $y += 5.5;
        }
        $el[] = self::panel(114, 71, 86, 12, '#e8edf7', ['radius' => 1.6]);
        $el[] = self::t('ΠΛΗΡΩΤΕΟ ΠΟΣΟ', 118, 75, ['bold' => true, 'size' => 8.5, 'color' => self::NAVY]);
        $el[] = self::f('amt.total', 145, 73.4, 52, ['align' => 'right', 'bold' => true, 'size' => 14, 'suffix' => ' €', 'color' => self::INK]);
        $el[] = self::t('Πληρωμή έως', 114, 87.5, ['size' => 7.5, 'color' => self::MUTED]);
        $el[] = self::f('period.due_date', 135, 87.1, 40, ['bold' => true, 'size' => 9, 'color' => self::NAVY]);
        $el[] = self::f('building.iban', 114, 92.4, 86, ['size' => 7.5, 'bold' => true, 'prefix' => 'IBAN ', 'hide_empty' => true, 'color' => self::INK]);
        $el[] = self::f('building.beneficiary', 114, 96.4, 86, ['size' => 7, 'prefix' => 'Δικαιούχος: ', 'hide_empty' => true, 'color' => '#334155']);
        $el[] = self::f('period.notes', 114, 101, 86, ['h' => 9, 'wrap' => true, 'size' => 7, 'color' => self::INK]);
        // υποσέλιδο & γραμμή κοπής
        $el[] = self::line(10, 113.5, 190, 0, ['stroke' => 0.3, 'color' => self::NAVY]);
        $el[] = self::f('manager.contact', 10, 115.4, 140, ['size' => 7, 'prefix' => 'Διαχειριστής: ', 'hide_empty' => true, 'color' => '#334155']);
        $el[] = self::f('account.website', 150, 115.4, 50, ['size' => 7, 'align' => 'right', 'layer' => 'form', 'color' => self::MUTED]);
        $el[] = self::line(0, 148.5, 210, 0, ['stroke' => 0.2, 'dashed' => true, 'color' => self::LINE]);
        return ['orientation' => 'portrait', 'font' => 'Geom', 'offset_x' => 0, 'offset_y' => 0, 'per_page' => 2, 'pitch' => 148.5, 'elements' => self::ids($el)];
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
            foreach (['bold', 'italic', 'wrap', 'hide_empty', 'upper', 'dashed', 'grid'] as $flag) {
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
