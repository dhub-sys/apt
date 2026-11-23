<?php
\defined('_JEXEC') or die;

use Koinoxrista\Component\Koinoxrista\Site\Helper\Auth;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Calc;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Fmt;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Repo;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Ui;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Url;

Auth::requireAccount();
[$p, $b] = Repo::period($this->in->getInt('id', 0));
$d = Repo::compute($p, $b);
$issued = $p->status === 'issued';
$tab = $this->in->getCmd('tab', 'expenses');
$hasHeating = $b->heating_type !== 'none' || $b->heating_method !== 'mills';
$tabs = ['expenses' => 'Δαπάνες'] + ($hasHeating ? ['heating' => 'Θέρμανση'] : []) + ['split' => 'Κατανομή', 'details' => 'Στοιχεία & ανακοινώσεις', 'print' => 'Έκδοση & εκτύπωση'];
if (!isset($tabs[$tab])) $tab = 'expenses';
$keysById = [];
foreach ($d['keys'] as $k) $keysById[(int) $k->id] = $k;
$calc = $d['calc'];
$firstMonth = !$issued && Repo::isFirstPeriod($p) && !Repo::hasIssued((int) $b->id);
$expTotal = array_sum(array_map(static fn($e) => (float) $e->amount, $d['expenses']));

$actions = $issued
    ? Ui::badge('Εκδόθηκε ' . Fmt::date(substr((string) $p->issued_at, 0, 10)), 'success') . ' ' . Ui::btn('Εκτύπωση', Url::to('view=period&id=' . (int) $p->id . '&tab=print'), 'primary')
    : Ui::badge('Πρόχειρο', 'warning') . ' ' . Ui::btn('Προεπισκόπηση κατανομής', Url::to('view=period&id=' . (int) $p->id . '&tab=split'), 'secondary');
echo Ui::open('buildings', $p->title, $actions, '<a href="' . Url::e('view=building&id=' . (int) $b->id) . '">' . Fmt::e($b->title) . '</a> · έκδοση ' . Fmt::date($p->issue_date) . ($p->due_date ? ' · πληρωμή έως ' . Fmt::date($p->due_date) : ''));
if ($issued) {
    echo '<div class="kx-alert kx-alert-notice">Ο μήνας είναι κλειδωμένος. Τα ποσά και τα υπόλοιπα έχουν «παγώσει». Για διόρθωση πατήστε «Αναίρεση έκδοσης» στην καρτέλα «Έκδοση & εκτύπωση».</div>';
}
if ($firstMonth && $tab !== 'split') {
    echo '<div class="kx-alert kx-alert-notice">Πρώτος μήνας στην εφαρμογή: αν κάποιες ιδιοκτησίες έχουν ήδη οφειλή (ή πίστωση), συμπληρώστε τα <a href="' . Url::e('view=period&id=' . (int) $p->id . '&tab=split#kx-opening') . '"><strong>προηγούμενα υπόλοιπα</strong></a> πριν την έκδοση.</div>';
}
foreach ($calc['warnings'] as $w) echo '<div class="kx-alert kx-alert-warning">' . Fmt::e($w) . '</div>';
?>
<nav class="kx-tabs">
  <?php foreach ($tabs as $k => $label): ?><a class="<?= $tab === $k ? 'is-active' : '' ?>" href="<?= Url::e('view=period&id=' . (int) $p->id . '&tab=' . $k) ?>"><?= Fmt::e($label) ?></a><?php endforeach; ?>
</nav>

<?php if ($tab === 'expenses'): ?>
<section class="kx-kpis kx-kpis-4">
  <div class="kx-kpi"><span>Σύνολο δαπανών</span><strong><?= Fmt::money($expTotal) ?> €</strong><small><?= count($d['expenses']) ?> δαπάνες</small></div>
  <?php foreach (Calc::CATEGORIES as $c => $label): if ($c === 'special' && !$calc['totals']['special']) continue; ?>
    <div class="kx-kpi"><span><?= Fmt::e($label) ?></span><strong><?= Fmt::moneyCents($calc['totals'][$c]) ?> €</strong></div>
  <?php endforeach; ?>
  <?php if ($calc['totals']['owner']): ?><div class="kx-kpi"><span>Βαρύνουν ιδιοκτήτες</span><strong><?= Fmt::moneyCents($calc['totals']['owner']) ?> €</strong></div><?php endif; ?>
</section>

<section class="kx-card" id="expenses">
  <div class="kx-card-head"><h2>Δαπάνες μήνα</h2></div>
  <?php if (!$d['expenses']): ?>
    <?= Ui::empty('Καμία δαπάνη', 'Προσθέστε τους λογαριασμούς του μήνα με τη φόρμα παρακάτω (ΔΕΗ, νερό, καθαριότητα, ανελκυστήρας, θέρμανση…).') ?>
  <?php else: ?>
  <div class="kx-table-wrap"><table class="kx-table">
    <thead><tr><th>#</th><th>Περιγραφή</th><th>Κατηγορία</th><th>Κατανομή</th><th>Βαρύνει</th><th class="kx-num">Ποσό €</th><th class="kx-num">Κατανεμήθηκε</th><th></th></tr></thead><tbody>
    <?php foreach (array_values($d['expenses']) as $n => $e):
        $sub = json_decode((string) $e->unit_ids, true) ?: [];
        $grp = (int) ($e->group_id ?? 0) > 0 ? (Repo::groups((int) $b->id)[(int) $e->group_id] ?? null) : null;
        $alloc = $calc['expenses'][(int) $e->id]['allocated'] ?? 0;
        $method = Calc::METHODS[$e->method] ?? $e->method;
        if ($e->method === 'key' || $e->method === 'heating') $method .= isset($keysById[(int) $e->key_id]) ? ' · ' . $keysById[(int) $e->key_id]->title : '';
        $json = ['id' => (int) $e->id, 'title' => $e->title, 'amount' => Fmt::money($e->amount), 'category' => $e->category, 'method' => $e->method, 'key_id' => (int) $e->key_id,
                 'payer' => $e->payer, 'supplier' => $e->supplier, 'doc_no' => $e->doc_no, 'doc_date' => Fmt::date($e->doc_date), 'recurring' => (int) $e->recurring,
                 'unit_ids' => $sub, 'group_id' => (int) ($e->group_id ?? 0), 'custom' => array_map(static fn($v) => Fmt::money($v), json_decode((string) $e->custom, true) ?: [])]; ?>
      <tr>
        <td><?= $n + 1 ?></td>
        <td><strong><?= Fmt::e($e->title) ?></strong><?= (int) $e->recurring ? ' <span class="kx-tag" title="Αντιγράφεται στον επόμενο μήνα">↻</span>' : '' ?>
          <?php if ($e->supplier || $e->doc_no): ?><div class="kx-muted"><?= Fmt::e(trim($e->supplier . ($e->doc_no ? ' · αρ. ' . $e->doc_no : '') . ($e->doc_date ? ' · ' . Fmt::date($e->doc_date) : ''), ' ·')) ?></div><?php endif; ?>
          <?php if ($grp): ?><div class="kx-muted">Ομάδα «<?= Fmt::e($grp->title) ?>»: <?= Fmt::e(implode(', ', array_map(static fn($id) => $d['units'][$id]->code ?? '?', $grp->unit_ids))) ?></div>
          <?php elseif ((int) ($e->group_id ?? 0) > 0): ?><div class="kx-warn">Η ομάδα διαγράφηκε</div>
          <?php elseif ($sub): ?><div class="kx-muted">Μόνο: <?= Fmt::e(implode(', ', array_map(static fn($id) => $d['units'][$id]->code ?? '?', $sub))) ?></div><?php endif; ?></td>
        <td><?= Fmt::e(Calc::CATEGORIES[$e->category] ?? '') ?></td>
        <td><?= Fmt::e($method) ?></td>
        <td><?= $e->payer === 'owner' ? Ui::badge('Ιδιοκτήτης', 'info') : 'Ένοικος' ?></td>
        <td class="kx-num"><?= Fmt::money($e->amount) ?></td>
        <td class="kx-num <?= $alloc !== Calc::cents($e->amount) ? 'kx-warn' : 'kx-ok' ?>"><?= Fmt::moneyCents($alloc) ?></td>
        <td class="kx-right kx-nowrap">
          <?php if (!$issued): ?>
            <button type="button" class="kx-btn kx-btn-small" data-edit-expense='<?= Fmt::e(json_encode($json, JSON_UNESCAPED_UNICODE)) ?>'>Επεξεργασία</button>
            <?= Ui::postButton('period.moveexpense', ['period_id' => $p->id, 'expense_id' => $e->id, 'dir' => 'up'], '↑', 'small') ?>
            <?= Ui::postButton('period.deleteexpense', ['period_id' => $p->id, 'expense_id' => $e->id], '✕', 'small', 'Διαγραφή της δαπάνης «' . $e->title . '»;') ?>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td></td><td colspan="4"><strong>Σύνολο</strong></td><td class="kx-num"><strong><?= Fmt::money($expTotal) ?></strong></td><td class="kx-num"><strong><?= Fmt::moneyCents($calc['totals']['total']) ?></strong></td><td></td></tr></tfoot>
  </table></div>
  <?php endif; ?>
</section>

<?php if (!$issued): ?>
<section class="kx-card" id="expense-form">
  <div class="kx-card-head"><h2 data-expense-title>Νέα δαπάνη</h2><button type="button" class="kx-btn kx-btn-small" data-expense-reset hidden>Ακύρωση επεξεργασίας</button></div>
  <?php $keymap = ['general' => (int) (Repo::keyOf($d['keys'], 'general')->id ?? 0), 'elevator' => (int) (Repo::keyOf($d['keys'], 'elevator')->id ?? 0), 'heating' => (int) (Repo::keyOf($d['keys'], 'heating')->id ?? 0)]; $keymap['special'] = $keymap['general']; ?>
  <?= Ui::form('period.saveexpense', 'class="kx-expense-form" data-expense-form data-keymap="' . Fmt::e(json_encode($keymap)) . '"') ?>
    <input type="hidden" name="period_id" value="<?= (int) $p->id ?>"><input type="hidden" name="expense_id" value="0">
    <div class="kx-form-grid">
      <label class="kx-field kx-span2"><span>Περιγραφή</span><input type="text" name="title" list="kx-exp-titles" required maxlength="255" placeholder="π.χ. ΔΕΗ κοινοχρήστων χώρων"></label>
      <?= Ui::input('amount', '', 'Ποσό €', ['inputmode' => 'decimal', 'placeholder' => '0,00']) ?>
      <?= Ui::select('category', Calc::CATEGORIES, 'general', 'Κατηγορία (στήλη εκτύπωσης)', ['attrs' => 'data-category']) ?>
      <?php $methods = Calc::METHODS; if ($b->heating_method === 'mills') { unset($methods['consumption']); $methods['heating'] = 'Θέρμανση (με χιλιοστά θέρμανσης)'; } ?>
      <?= Ui::select('method', $methods, 'key', 'Τρόπος κατανομής', ['attrs' => 'data-method']) ?>
      <?php $keyOpts = []; foreach ($d['keys'] as $k) $keyOpts[$k->id] = $k->title; ?>
      <?= Ui::select('key_id', $keyOpts, (int) (Repo::keyOf($d['keys'], 'general')->id ?? 0), 'Στήλη χιλιοστών', ['attrs' => 'data-key']) ?>
      <?= Ui::select('payer', Calc::PAYERS, 'tenant', 'Βαρύνει') ?>
      <?= Ui::input('supplier', '', 'Προμηθευτής') ?>
      <?= Ui::input('doc_no', '', 'Αρ. παραστατικού') ?>
      <?= Ui::input('doc_date', '', 'Ημ/νία παραστατικού', ['placeholder' => 'ηη/μμ/εεεε']) ?>
    </div>
    <datalist id="kx-exp-titles">
      <?php foreach (['ΔΕΗ κοινοχρήστων χώρων', 'ΕΥΔΑΠ / Ύδρευση', 'Καθαρισμός κλιμακοστασίου', 'Αμοιβή διαχείρισης', 'Συντήρηση ανελκυστήρα', 'Ασφάλιση ανελκυστήρα', 'ΔΕΗ ανελκυστήρα', 'Πετρέλαιο θέρμανσης', 'Συντήρηση καυστήρα', 'ΔΕΗ καυστήρα', 'Φυσικό αέριο', 'Αποθεματικό', 'Υλικά καθαριότητας', 'Λαμπτήρες', 'Κηπουρός', 'Απολύμανση / μυοκτονία', 'Επισκευή', 'Τραπεζικά έξοδα'] as $t): ?><option value="<?= Fmt::e($t) ?>"><?php endforeach; ?>
    </datalist>
    <?php $groups = Repo::groups((int) $b->id); $scopeOpts = ['all' => 'Όλες οι ιδιοκτησίες'];
          foreach ($groups as $g) $scopeOpts['g:' . $g->id] = 'Ομάδα: ' . $g->title . ' (' . count($g->unit_ids) . ')';
          $scopeOpts['custom'] = 'Επιλογή μία-μία…'; ?>
    <div class="kx-form-grid kx-mt" data-scope-wrap>
      <?= Ui::select('unit_scope', $scopeOpts, 'all', 'Ιδιοκτησίες που αφορά', ['attrs' => 'data-scope', 'help' => $groups ? '' : 'Ομάδες όπως «Υπόγειο» ορίζετε στο Κτίριο → Ιδιοκτησίες.']) ?>
    </div>
    <details class="kx-details" data-subset hidden open>
      <summary>Επιλογή ιδιοκτησιών <span class="kx-muted" data-subset-count></span></summary>
      <div class="kx-chips">
        <?php foreach ($d['units'] as $u): ?><label class="kx-chip"><input type="checkbox" name="unit_ids[]" value="<?= (int) $u->id ?>"><span><?= Fmt::e($u->code) ?></span></label><?php endforeach; ?>
      </div>
    </details>
    <div class="kx-custom" data-custom hidden>
      <p class="kx-muted">Ποσό ανά ιδιοκτησία. Το σύνολο πρέπει να ισούται με το ποσό της δαπάνης (αν το ποσό μείνει κενό, συμπληρώνεται αυτόματα).</p>
      <div class="kx-custom-grid">
        <?php foreach ($d['units'] as $u): ?><label><span><?= Fmt::e($u->code) ?></span><input type="text" name="custom[<?= (int) $u->id ?>]" inputmode="decimal" size="7"></label><?php endforeach; ?>
      </div>
    </div>
    <div class="kx-row kx-mt">
      <?= Ui::check('recurring', false, 'Επαναλαμβανόμενη (αντιγράφεται στον επόμενο μήνα)') ?>
      <span class="kx-flex"></span>
      <button type="submit" class="kx-btn kx-btn-primary" data-expense-submit>Προσθήκη δαπάνης</button>
    </div>
  </form>
</section>
<?php endif; ?>

<?php elseif ($tab === 'heating'): ?>
<?php
$h = $d['heating'];
$f = $d['fuel'];
$fu = Fmt::e($b->fuel_unit);
$ro = $issued ? ' readonly' : '';
$stockRows = $f['stock'] ?: [['qty' => '', 'price' => '']];
$buyRows = $f['purchases'] ?: [['date' => '', 'qty' => '', 'price' => '']];
$fuelRow = static function (string $kind, $r) use ($ro, $issued): string {
    $q = $r['qty'] === '' ? '' : Fmt::qty($r['qty'], 3);
    $pr = $r['price'] === '' ? '' : Fmt::price($r['price']);
    $h = '<tr data-fuel-row>';
    if ($kind === 'buy') $h .= '<td><input name="buy_date[]" value="' . Fmt::e(Fmt::date($r['date'] ?? '')) . '" placeholder="ηη/μμ/εεεε"' . $ro . '></td>';
    $h .= '<td class="kx-num"><input name="' . $kind . '_qty[]" value="' . Fmt::e($q) . '" inputmode="decimal" placeholder="0" data-q' . $ro . '></td>'
        . '<td class="kx-num"><input name="' . $kind . '_price[]" value="' . Fmt::e($pr) . '" inputmode="decimal" placeholder="0,00" data-p' . $ro . '></td>'
        . '<td class="kx-num" data-v></td>'
        . '<td>' . ($issued ? '' : '<button type="button" class="kx-btn kx-btn-small" data-remove-row title="Αφαίρεση">✕</button>') . '</td></tr>';
    return $h;
};
$meters = $b->heating_method !== 'mills';
?>
<?= Ui::form('period.saveheating', 'class="kx-stack" data-fuel data-unit="' . $fu . '"') ?>
  <input type="hidden" name="id" value="<?= (int) $p->id ?>">
  <section class="kx-card">
    <div class="kx-card-head"><h2>Κόστος καυσίμου μήνα</h2><span class="kx-muted">Κοστολόγηση FIFO: εξοφλούνται πρώτα τα παλαιότερα λίτρα</span></div>
    <div class="kx-fuel-grid kx-fuel-inputs">
      <div>
        <h3>1. Υπόλοιπο έναρξης</h3>
        <p class="kx-muted">Το καύσιμο που υπήρχε στη δεξαμενή στην αρχή του μήνα, με την τιμή που είχε αγοραστεί. Μεταφέρεται αυτόματα από τον προηγούμενο μήνα.</p>
        <table class="kx-table kx-fuel-table"><colgroup><col><col><col class="kx-col-val"><col class="kx-col-x"></colgroup><thead><tr><th class="kx-num"><?= $fu ?></th><th class="kx-num">€ / <?= $fu ?></th><th class="kx-num">Αξία €</th><th></th></tr></thead>
          <tbody data-rows="stock"><?php foreach ($stockRows as $r) echo $fuelRow('stock', $r); ?></tbody></table>
        <div><?php if (!$issued): ?><button type="button" class="kx-btn kx-btn-small" data-add-fuel="stock">+ Γραμμή υπολοίπου</button><?php endif; ?></div>
      </div>
      <div>
        <h3>2. Αγορές μέσα στον μήνα</h3>
        <p class="kx-muted">Κάθε παραλαβή καυσίμου ξεχωριστά, με την τιμή του τιμολογίου ανά <?= $fu ?>.</p>
        <table class="kx-table kx-fuel-table"><colgroup><col class="kx-col-date"><col><col><col class="kx-col-val"><col class="kx-col-x"></colgroup><thead><tr><th>Ημ/νία</th><th class="kx-num"><?= $fu ?></th><th class="kx-num">€ / <?= $fu ?></th><th class="kx-num">Αξία €</th><th></th></tr></thead>
          <tbody data-rows="buy"><?php foreach ($buyRows as $r) echo $fuelRow('buy', $r); ?></tbody></table>
        <div><?php if (!$issued): ?><button type="button" class="kx-btn kx-btn-small" data-add-fuel="buy">+ Αγορά</button><?php endif; ?></div>
      </div>
    </div>
    <template data-tpl="stock"><?= $fuelRow('stock', ['qty' => '', 'price' => '']) ?></template>
    <template data-tpl="buy"><?= $fuelRow('buy', ['date' => '', 'qty' => '', 'price' => '']) ?></template>

    <div class="kx-fuel-total">Σύνολο διαθέσιμου: <strong data-total-q><?= Fmt::qty($f['total']) ?> <?= $fu ?></strong> αξίας <strong data-total-v><?= Fmt::money($f['total_value']) ?> €</strong></div>

    <h3 class="kx-mt">3. Μέτρηση στο τέλος του μήνα</h3>
    <div class="kx-fuel-grid">
      <div class="kx-form-grid">
        <?= Ui::input('end_qty', $f['measured'] ? Fmt::qty($f['end'], 3) : '', 'Υπόλοιπο ' . $b->fuel_unit . ' (μέτρηση δεξαμενής)', ['inputmode' => 'decimal', 'placeholder' => 'π.χ. 560']) ?>
        <label class="kx-field"><span>Αξία υπολοίπου €</span><input type="text" readonly data-end-v value="<?= $f['measured'] ? Fmt::money($f['end_value']) : '' ?>"></label>
      </div>
      <div class="kx-fuel-result">
        <span>Κατανάλωση μήνα</span>
        <strong data-cons><?= $f['measured'] ? Fmt::qty($f['consumed']) . ' ' . $fu . ' — ' . Fmt::euro($f['cost']) : '—' ?></strong>
        <ul data-breakdown><?php foreach ($f['breakdown'] as $x): ?><li><?= Fmt::qty($x['qty']) ?> <?= $fu ?> × <?= Fmt::price($x['price']) ?> € = <?= Fmt::money($x['amount']) ?> €</li><?php endforeach; ?></ul>
        <small data-remain><?php if ($f['measured'] && $f['remaining']): ?>Μένει για τον επόμενο μήνα: <?= Fmt::e(implode(' + ', array_map(static fn($x) => Fmt::qty($x['qty']) . ' ' . $b->fuel_unit . ' × ' . Fmt::price($x['price']) . ' €', $f['remaining']))) ?><?php endif; ?></small>
      </div>
    </div>
    <?php if (!$issued): ?>
    <div class="kx-row kx-mt">
      <button type="submit" class="kx-btn">Αποθήκευση</button>
      <button type="submit" name="make_expense" value="1" class="kx-btn kx-btn-primary">Αποθήκευση & καταχώριση ως δαπάνη θέρμανσης</button>
      <span class="kx-muted"><?= $meters ? 'Αυτονομία: Ei = [εi·fi + Ωi·ei / Σ(Ωi·ei) · (1 − Σεi·fi)] · E' . (($b->heating_ei ?? 'coef') === 'mills' ? ', με ei = εi (χιλιοστά θέρμανσης)' : '') : 'Κεντρική θέρμανση: μοιράζεται με τα χιλιοστά θέρμανσης.' ?></span>
    </div>
    <?php endif; ?>
  </section>
</form>

<?php if ($meters):
    $last = Repo::lastReadings($p);
    $hist = Repo::readingHistory((int) $b->id, 12);
    $heatUnits = array_filter($d['units'], static fn($u) => !(int) $u->heat_excluded);
    $excluded = array_filter($d['units'], static fn($u) => (int) $u->heat_excluded);
    $showExcl = $this->in->getInt('excl', 0) === 1;
    $mu = $b->heating_method === 'hours' ? 'ώρες' : 'μονάδες';
?>
<section class="kx-card">
  <div class="kx-card-head"><h2>Ενδείξεις <?= $b->heating_method === 'hours' ? 'ωρομετρητών' : 'θερμιδομετρητών' ?></h2>
    <span class="kx-flex"></span>
    <button type="button" class="kx-btn kx-btn-small" data-toggle-panel="#kx-excl-panel" aria-expanded="<?= $showExcl ? 'true' : 'false' ?>">Προβολή εξαιρέσεων (<?= count($excluded) ?>)</button>
    <a class="kx-btn kx-btn-small" href="<?= Url::e('view=building&id=' . (int) $b->id . '&tab=units') ?>">Συντελεστές</a></div>
  <div class="kx-excl-panel" id="kx-excl-panel"<?= $showExcl ? '' : ' hidden' ?>>
    <h3>Ιδιοκτησίες εκτός θέρμανσης</h3>
    <?php if (!$excluded): ?>
      <p class="kx-muted">Καμία ιδιοκτησία δεν εξαιρείται. Για εξαίρεση πατήστε «Εξαίρεση» στη γραμμή της ιδιοκτησίας παρακάτω.</p>
    <?php else: ?>
      <p class="kx-muted">Δεν συμμετέχουν σε καμία δαπάνη θέρμανσης (ούτε απώλειες ούτε κατανάλωση) και δεν εμφανίζονται στις ενδείξεις.</p>
      <table class="kx-table kx-table-compact"><thead><tr><th>Ιδιοκτησία</th><th>Υπόχρεος</th><th>Λόγος εξαίρεσης</th><th></th></tr></thead><tbody>
      <?php foreach ($excluded as $u): ?>
        <tr><td><strong><?= Fmt::e($u->code) ?></strong></td><td><?= Fmt::e(Repo::payerName($u)) ?></td><td><?= $u->heat_excluded_reason !== '' ? Fmt::e($u->heat_excluded_reason) : '<span class="kx-muted">—</span>' ?></td>
          <td class="kx-right"><?= $issued ? '' : Ui::postButton('building.heatexclude', ['unit_id' => $u->id, 'exclude' => 0, 'period_id' => $p->id], 'Αναίρεση εξαίρεσης', 'small', 'Η ιδιοκτησία ' . $u->code . ' θα συμμετέχει ξανά στη θέρμανση. Συνέχεια;') ?></td></tr>
      <?php endforeach; ?>
      </tbody></table>
    <?php endif; ?>
  </div>
  <?= Ui::form('period.savereadings') ?>
    <input type="hidden" name="id" value="<?= (int) $p->id ?>">
    <div class="kx-row kx-mb">
      <?= Ui::input('read_from', Fmt::date($h['read_from'] ?? ''), 'Προηγούμενη μέτρηση (ημ/νία)', ['placeholder' => 'π.χ. 01/10/2026']) ?>
      <?= Ui::input('read_to', Fmt::date($h['read_to'] ?? ''), 'Τρέχουσα μέτρηση (ημ/νία)', ['placeholder' => 'π.χ. 01/11/2026']) ?>
    </div>
    <div class="kx-table-wrap"><table class="kx-table kx-sheet" data-readings>
      <thead><tr><th>Ιδιοκτησία</th><th>Υπόχρεος</th><th class="kx-num" title="θερμικός συντελεστής">ei</th><th class="kx-num" title="συντελεστής απωλειών %">fi %</th><th class="kx-num">Προηγ. ένδειξη</th><th class="kx-num">Τρέχουσα ένδειξη</th><th class="kx-num">Κατανάλωση (<?= $mu ?>)</th><th class="kx-num">Προηγ. μήνας</th><th class="kx-num" title="πάγιο (απώλειες)">Πάγιο €</th><th class="kx-num">Κατανάλ. €</th><th class="kx-num">Θέρμανση €</th><th></th></tr></thead><tbody>
      <?php foreach ($heatUnits as $u):
          $uid = (int) $u->id;
          $r = $d['readings'][$uid] ?? null;
          $prevVal = $r ? (float) $r->prev_value : ($last[$uid]['value'] ?? null);
          $currVal = $r ? (float) $r->curr_value : null;
          $cons = $r && (float) $r->curr_value != (float) $r->prev_value ? max(0, (float) $r->curr_value - (float) $r->prev_value) : null; ?>
        <tr>
          <td><strong><?= Fmt::e($u->code) ?></strong></td>
          <td><?= Fmt::e(Repo::payerName($u)) ?></td>
          <td class="kx-num"><?= ($b->heating_ei ?? 'coef') === 'mills' ? Fmt::mills($u->mills[(int) (Repo::keyOf($d['keys'], 'heating')->id ?? 0)] ?? 0) . '‰' : Fmt::qty($u->heat_coef, 4) ?></td>
          <td class="kx-num<?= $u->heat_fi === null ? ' kx-muted' : '' ?>"><?= Fmt::qty($u->heat_fi !== null ? $u->heat_fi : $b->heating_fixed_pct, 3) ?></td>
          <td class="kx-num"><input name="prev[<?= $uid ?>]" value="<?= $prevVal === null ? '' : Fmt::qty($prevVal, 3) ?>" size="8" inputmode="decimal"<?= $ro ?>></td>
          <td class="kx-num"><input name="curr[<?= $uid ?>]" value="<?= $currVal === null || ($r && (float) $r->curr_value == (float) $r->prev_value) ? '' : Fmt::qty($currVal, 3) ?>" size="8" inputmode="decimal" placeholder="<?= $prevVal === null ? '' : Fmt::qty($prevVal, 3) ?>"<?= $ro ?>></td>
          <td class="kx-num" data-diff><?= $cons === null ? '' : Fmt::qty($cons) ?></td>
          <td class="kx-num kx-muted" title="<?= Fmt::e($last[$uid]['period'] ?? '') ?>"><?= isset($last[$uid]) ? Fmt::qty($last[$uid]['consumption']) : '' ?></td>
          <td class="kx-num"><?= Fmt::moneyCents($calc['units'][$uid]['heat_fixed'] ?? 0, true) ?></td>
          <td class="kx-num"><?= Fmt::moneyCents($calc['units'][$uid]['heat_var'] ?? 0, true) ?></td>
          <td class="kx-num"><strong><?= Fmt::moneyCents(($calc['units'][$uid]['heat_fixed'] ?? 0) + ($calc['units'][$uid]['heat_var'] ?? 0), true) ?></strong></td>
          <td class="kx-right"><?php if (!$issued): ?><button type="button" class="kx-btn kx-btn-small" data-heat-exclude="<?= $uid ?>" data-code="<?= Fmt::e($u->code) ?>" title="Εξαίρεση από τη θέρμανση">Εξαίρεση</button><?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody></table></div>
    <p class="kx-muted kx-mt">Κενή «τρέχουσα ένδειξη» = καμία κατανάλωση (ίδια με την προηγούμενη). Η προηγούμενη ένδειξη έρχεται από την τελευταία καταγραφή κάθε ιδιοκτησίας.</p>
    <?php if (!$issued): ?><div class="kx-row"><span class="kx-flex"></span><button type="submit" class="kx-btn kx-btn-primary">Αποθήκευση ενδείξεων</button></div><?php endif; ?>
  </form>
</section>

<?php if (!$issued): ?>
<?= Ui::form('building.heatexclude', 'id="kx-heat-exclude-form" hidden') ?>
  <input type="hidden" name="unit_id" value=""><input type="hidden" name="exclude" value="1"><input type="hidden" name="period_id" value="<?= (int) $p->id ?>"><input type="hidden" name="reason" value="">
</form>
<?php endif; ?>
<?php if ($hist['periods']): ?>
<section class="kx-card">
  <h2>Αρχείο ενδείξεων</h2>
  <p class="kx-muted">Ένδειξη στο τέλος κάθε μήνα και, σε παρένθεση, η κατανάλωση του μήνα (<?= $mu ?>).</p>
  <div class="kx-table-wrap"><table class="kx-table kx-table-compact">
    <thead><tr><th>Ιδιοκτησία</th><?php foreach ($hist['periods'] as $hp): ?><th class="kx-num"><?= Fmt::e(mb_substr(Fmt::MONTHS[(int) $hp->month] ?? '', 0, 3) . ' ' . substr((string) $hp->year, 2)) ?></th><?php endforeach; ?><th class="kx-num">Σύνολο</th></tr></thead><tbody>
    <?php foreach ($heatUnits as $u): $tot = 0; ?>
      <tr><td><strong><?= Fmt::e($u->code) ?></strong></td>
      <?php foreach ($hist['periods'] as $hp): $x = $hist['rows'][(int) $u->id][(int) $hp->id] ?? null; $c = $x ? max(0, (float) $x->curr_value - (float) $x->prev_value) : 0; $tot += $c; ?>
        <td class="kx-num<?= (int) $hp->id === (int) $p->id ? ' kx-current' : '' ?>"><?= $x ? Fmt::qty($x->curr_value) . ' <span class="kx-muted">(' . Fmt::qty($c) . ')</span>' : '—' ?></td>
      <?php endforeach; ?>
        <td class="kx-num"><strong><?= Fmt::qty($tot) ?></strong></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<?php endif; ?>
<?php endif; ?>

<?php elseif ($tab === 'split'): ?>
<?php if ($firstMonth): ?>
<section class="kx-card" id="kx-opening">
  <div class="kx-card-head"><h2>Προηγούμενα υπόλοιπα (πρώτος μήνας)</h2></div>
  <p class="kx-muted">Οφειλές πριν ξεκινήσετε στην εφαρμογή: θετικό ποσό = οφειλή, αρνητικό = πίστωση. Ορίζονται μόνο τώρα· από τον επόμενο μήνα το προηγούμενο υπόλοιπο προκύπτει αυτόματα από την καρτέλα είσπραξης (χρεώσεις − εισπράξεις).</p>
  <?= Ui::form('period.saveopening') ?>
    <input type="hidden" name="id" value="<?= (int) $p->id ?>">
    <div class="kx-table-wrap"><table class="kx-table">
      <thead><tr><th>Ιδιοκτησία</th><th>Υπόχρεος</th><th class="kx-num">Προηγ. υπόλοιπο €</th></tr></thead>
      <tbody>
      <?php foreach ($d['units'] as $u): ?>
        <tr><td><strong><?= Fmt::e($u->code) ?></strong> <span class="kx-muted"><?= Fmt::e($u->floor) ?></span></td><td><?= Fmt::e(Repo::payerName($u)) ?></td>
          <td class="kx-num"><input name="opening[<?= (int) $u->id ?>]" value="<?= (float) $u->opening_balance != 0 ? Fmt::e(Fmt::money($u->opening_balance)) : '' ?>" size="9" inputmode="decimal" placeholder="0,00" class="kx-num" style="max-width:120px"></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <div class="kx-row kx-mt"><button class="kx-btn kx-btn-primary" type="submit">Αποθήκευση υπολοίπων</button></div>
  </form>
</section>
<?php endif; ?>
<section class="kx-card">
  <div class="kx-card-head"><h2>Κατανομή ανά ιδιοκτησία</h2><span class="kx-flex"></span><label class="kx-check kx-check-inline"><input type="checkbox" data-toggle-class="kx-show-mills" data-target="#kx-split"><span>Χιλιοστά</span></label><?php if (Auth::feature('export')): ?><a class="kx-btn kx-btn-small" href="<?= Url::e('task=period.export&id=' . (int) $p->id) ?>">Εξαγωγή Excel (CSV)</a><?php endif; ?></div>
  <div class="kx-table-wrap"><table class="kx-table kx-split" id="kx-split">
    <thead><tr><th>Ιδιοκτησία</th><th>Υπόχρεος</th>
      <?php foreach ($d['keys'] as $k): ?><th class="kx-num kx-dim">Χιλ. <?= Fmt::e($k->title) ?></th><?php endforeach; ?>
      <?php foreach (Calc::CATEGORIES as $label): ?><th class="kx-num"><?= Fmt::e($label) ?></th><?php endforeach; ?>
      <th class="kx-num">Ιδιοκτήτη</th><th class="kx-num">Σύνολο μήνα</th><th class="kx-num">Προηγ. υπόλ.</th><th class="kx-num">Πληρωτέο</th></tr></thead>
    <tbody>
    <?php $tp = 0; foreach ($d['units'] as $u): $c = $calc['units'][(int) $u->id]; $prev = (int) ($d['previous'][(int) $u->id] ?? 0); $tp += $prev; ?>
      <tr>
        <td><strong><?= Fmt::e($u->code) ?></strong> <span class="kx-muted"><?= Fmt::e($u->floor) ?></span></td><td><?= Fmt::e(Repo::payerName($u)) ?></td>
        <?php foreach ($d['keys'] as $k): ?><td class="kx-num kx-dim"><?= Fmt::mills($u->mills[(int) $k->id] ?? 0, true) ?></td><?php endforeach; ?>
        <?php foreach (array_keys(Calc::CATEGORIES) as $cat): ?><td class="kx-num"><?= Fmt::moneyCents($c[$cat], true) ?></td><?php endforeach; ?>
        <td class="kx-num"><?= Fmt::moneyCents($c['owner'], true) ?></td>
        <td class="kx-num"><strong><?= Fmt::moneyCents($c['total']) ?></strong></td>
        <td class="kx-num <?= $prev > 0 ? 'kx-warn' : '' ?>"><?= Fmt::moneyCents($prev) ?></td>
        <td class="kx-num"><strong><?= Fmt::moneyCents($c['total'] + $prev) ?></strong></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td colspan="2"><strong>Σύνολα</strong></td>
      <?php foreach ($d['keys'] as $k): ?><td class="kx-num kx-dim"><?= Fmt::mills(array_sum(array_map(static fn($u) => (float) ($u->mills[(int) $k->id] ?? 0), $d['units']))) ?></td><?php endforeach; ?>
      <?php foreach (array_keys(Calc::CATEGORIES) as $cat): ?><td class="kx-num"><strong><?= Fmt::moneyCents($calc['totals'][$cat]) ?></strong></td><?php endforeach; ?>
      <td class="kx-num"><strong><?= Fmt::moneyCents($calc['totals']['owner']) ?></strong></td><td class="kx-num"><strong><?= Fmt::moneyCents($calc['totals']['total']) ?></strong></td>
      <td class="kx-num"><?= Fmt::moneyCents($tp) ?></td><td class="kx-num"><strong><?= Fmt::moneyCents($calc['totals']['total'] + $tp) ?></strong></td></tr></tfoot>
  </table></div>
  <p class="kx-muted kx-mt">Κάθε δαπάνη μοιράζεται με ακρίβεια λεπτού: οι διαφορές στρογγυλοποίησης αποδίδονται στις ιδιοκτησίες με το μεγαλύτερο υπόλοιπο, ώστε το σύνολο να ισούται πάντα με το ποσό της δαπάνης.</p>
</section>

<?php elseif ($tab === 'details'): ?>
<section class="kx-card">
  <?= Ui::form('period.save', 'class="kx-stack-sm"') ?>
    <input type="hidden" name="id" value="<?= (int) $p->id ?>"><input type="hidden" name="tab" value="details">
    <div class="kx-form-grid">
      <?= Ui::select('month', Fmt::MONTHS, (int) $p->month, 'Μήνας') ?>
      <?= Ui::input('year', $p->year, 'Έτος', ['type' => 'number', 'min' => 2000, 'max' => 2100]) ?>
      <?= Ui::input('title', $p->title, 'Τίτλος στην εκτύπωση', ['help' => 'π.χ. «Οκτώβριος 2026» ή «Έκτακτη έκδοση – Στέγη»']) ?>
      <?= Ui::input('issue_date', Fmt::date($p->issue_date), 'Ημερομηνία έκδοσης', ['placeholder' => 'ηη/μμ/εεεε']) ?>
      <?= Ui::input('due_date', Fmt::date($p->due_date), 'Πληρωμή έως', ['placeholder' => 'ηη/μμ/εεεε']) ?>
      <?= Ui::input('notes', $p->notes, 'Ανακοινώσεις (τυπώνονται στην κατάσταση και στα ειδοποιητήρια)', ['type' => 'textarea', 'rows' => 5, 'wide' => true]) ?>
    </div>
    <?php if (!$issued): ?><div class="kx-row"><button type="submit" class="kx-btn kx-btn-primary">Αποθήκευση</button></div><?php endif; ?>
  </form>
  <?php if (!$issued): ?><div class="kx-row kx-mt kx-end"><?= Ui::postButton('period.delete', ['id' => $p->id], 'Διαγραφή μήνα', 'danger', 'Διαγραφή του μήνα με όλες τις δαπάνες του;') ?></div><?php endif; ?>
</section>

<?php else: /* print */ ?>
<?php
$sheets = Repo::templates('sheet');
$notices = Repo::templates('notice');
$opt = static function (array $list) { $o = []; foreach ($list as $t) $o[$t->id] = $t->title . ((int) $t->account_id === 0 ? ' (συστήματος)' : ''); return $o; };
$defSheet = Repo::defaultTemplate('sheet');
$defNotice = Repo::defaultTemplate('notice');
$unitOpts = ['' => 'Όλες οι ιδιοκτησίες'];
foreach ($d['units'] as $u) $unitOpts[$u->id] = $u->code . ' — ' . Repo::payerName($u);
$modes = ['full' => 'Λευκό χαρτί (έντυπο + δεδομένα)', 'data' => 'Προτυπωμένο χαρτί (μόνο δεδομένα)', 'form' => 'Κενό έντυπο (χωρίς δεδομένα)'];
?>
<div class="kx-cols">
  <div class="kx-col-main">
    <section class="kx-card">
      <h2>Εκτύπωση</h2>
      <form method="get" action="<?= Url::e() ?>" target="_blank" class="kx-print-form">
        <input type="hidden" name="option" value="com_koinoxrista"><input type="hidden" name="tmpl" value="component"><input type="hidden" name="view" value="print"><input type="hidden" name="period_id" value="<?= (int) $p->id ?>">
        <div class="kx-print-choice">
          <label class="kx-radio-card"><input type="radio" name="type" value="sheet" checked data-print-type><span><strong>Κατάσταση κοινοχρήστων</strong><small>Ο πίνακας του κτιρίου για την είσοδο</small></span></label>
          <label class="kx-radio-card"><input type="radio" name="type" value="notice" data-print-type><span><strong>Ειδοποιητήρια</strong><small>Ένα ανά ιδιοκτησία</small></span></label>
          <label class="kx-radio-card"><input type="radio" name="type" value="ledger" data-print-type><span><strong>Φύλλο διαχειριστή</strong><small>Βεβαίωση εισπράξεων για το αρχείο</small></span></label>
        </div>
        <div class="kx-form-grid">
          <span data-for-type="sheet"><?= Ui::select('sheet_template', $opt($sheets), (int) ($defSheet->id ?? 0), 'Πρότυπο κατάστασης') ?></span>
          <span data-for-type="notice" hidden><?= Ui::select('notice_template', $opt($notices), (int) ($defNotice->id ?? 0), 'Πρότυπο ειδοποιητηρίου') ?></span>
          <span data-for-type="ledger" hidden><?= Ui::select('ledger_template', $opt(Repo::templates('ledger')), (int) (Repo::defaultTemplate('ledger')->id ?? 0), 'Πρότυπο φύλλου') ?></span>
          <span data-for-type="notice" hidden><?= Ui::select('unit_id', $unitOpts, '', 'Ιδιοκτησίες') ?></span>
          <span data-for-type="notice" hidden><?= Ui::select('grouping', ['detailed' => 'Αναλυτικά', 'grouped' => 'Ανά κατηγορία'], \Koinoxrista\Component\Koinoxrista\Site\Helper\Accounts::settings(Auth::account())['notice_grouping'], 'Ανάλυση δαπανών') ?></span>
          <?= Ui::select('mode', $modes, 'full', 'Χαρτί') ?>
        </div>
        <div class="kx-row kx-mt"><button type="submit" class="kx-btn kx-btn-primary">Άνοιγμα για εκτύπωση</button><span class="kx-muted">Ανοίγει σε νέα καρτέλα με προεπισκόπηση A4, ρύθμιση μετατόπισης και κουμπί εκτύπωσης.</span></div>
      </form>
    </section>
    <?php if (Auth::feature('email')): ?>
    <section class="kx-card">
      <h2>Αποστολή με email</h2>
      <p class="kx-muted">Κάθε υπόχρεος με email λαμβάνει το ποσό του και σύνδεσμο προς το προσωπικό του ειδοποιητήριο.</p>
      <?php if ($issued): ?><?= Ui::postButton('period.sendnotices', ['id' => $p->id], 'Αποστολή ειδοποιητηρίων', 'secondary', 'Να σταλούν email σε όλους τους υπόχρεους με διεύθυνση email;') ?>
      <?php else: ?><p>Διαθέσιμο μετά την έκδοση.</p><?php endif; ?>
    </section>
    <?php endif; ?>
  </div>
  <aside class="kx-col-side">
    <section class="kx-card">
      <h2>Έκδοση</h2>
      <?php if ($issued): ?>
        <p>Εκδόθηκε στις <?= Fmt::e(Fmt::date(substr((string) $p->issued_at, 0, 10))) ?>. Τα υπόλοιπα των ιδιοκτησιών περιλαμβάνουν αυτόν τον μήνα.</p>
        <?= Ui::postButton('period.reopen', ['id' => $p->id], 'Αναίρεση έκδοσης', 'secondary', 'Ο μήνας θα ανοίξει ξανά για αλλαγές και θα αφαιρεθεί από τα υπόλοιπα μέχρι να εκδοθεί ξανά. Συνέχεια;') ?>
      <?php else: ?>
        <p>Με την έκδοση ο μήνας κλειδώνει, τα ποσά «παγώνουν» και χρεώνονται στις ιδιοκτησίες.</p>
        <p><strong><?= Fmt::moneyCents($calc['totals']['total']) ?> €</strong> σε <?= count($d['units']) ?> ιδιοκτησίες.</p>
        <?= Ui::postButton('period.issue', ['id' => $p->id], 'Έκδοση & κλείδωμα', 'primary', 'Έκδοση του μήνα ' . $p->title . ';') ?>
        <p class="kx-muted kx-mt">Μπορείτε να εκτυπώσετε και πριν την έκδοση· το πρόχειρο τυπώνεται με την ένδειξη «ΠΡΟΣΧΕΔΙΟ».</p>
      <?php endif; ?>
    </section>
    <?php if (Auth::feature('export')): ?>
    <section class="kx-card"><h2>Excel</h2><a class="kx-btn" href="<?= Url::e('task=period.export&id=' . (int) $p->id) ?>">Λήψη CSV</a></section>
    <?php endif; ?>
  </aside>
</div>
<?php endif;
echo Ui::close();
