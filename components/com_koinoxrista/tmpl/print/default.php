<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Accounts;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Auth;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Calc;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Fmt;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Layouts;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Render;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Repo;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Settings;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Url;

/*
 * Σελίδες A4 για εκτύπωση. Παράμετροι: period_id, type (sheet|notice), sheet_template / notice_template,
 * mode (full|data|form), unit_id, dx/dy (πρόσθετη μετατόπιση mm), grid, draft. Με sample=1&template_id=Χ
 * τυπώνεται το πρότυπο με δείγμα δεδομένων.
 */
$account = Auth::isPlatformAdmin() ? Auth::account() : Auth::requireAccount();
$in = $this->in;
$sample = $in->getInt('sample', 0) === 1;
$mode = in_array($in->getCmd('mode', 'full'), ['full', 'data', 'form'], true) ? $in->getCmd('mode', 'full') : 'full';
$issued = false;
$p = $b = null;

if ($sample) {
    $tpl = Repo::template($in->getInt('template_id', 0));
    if (!$tpl) Auth::deny('Το πρότυπο δεν βρέθηκε.', 'view=templates');
    $type = $tpl->type;
    $ctx = Layouts::sample();
    if ($type === 'ledger') {
        foreach ($ctx['units'] as $i => $u) {
            $ctx['units'][$i] += $i % 3 === 2 ? [] : ['pay.date' => '0' . (3 + $i % 6) . '/11/2026', 'pay.amount' => $u['amt.total'], 'pay.method' => $i % 2 ? 'Κατάθεση' : 'Μετρητά', 'pay.note' => 'απόδ. ' . (100 + $i)];
        }
        $ctx['fields'] += ['totals.payable' => '1.535,50', 'totals.paid' => '1.020,40', 'totals.unpaid' => '3 ιδιοκτησίες'];
    }
    $title = 'Δοκιμή: ' . $tpl->title;
    $back = Url::to('view=template&id=' . (int) $tpl->id);
} else {
    [$p, $b] = Repo::period($in->getInt('period_id', 0));
    $type = in_array($in->getCmd('type', 'sheet'), ['notice', 'ledger'], true) ? $in->getCmd('type', 'sheet') : 'sheet';
    $tpl = Repo::template($in->getInt($type . '_template', $in->getInt('template_id', 0))) ?: Repo::defaultTemplate($type);
    $issued = $p->status === 'issued';
    $snap = $issued ? json_decode((string) $p->snapshot, true) : null;
    $ctx = !empty($snap['ctx']) ? $snap['ctx'] : Repo::printContext(Repo::compute($p, $b), $account);
    $title = ($type === 'notice' ? 'Ειδοποιητήρια' : ($type === 'ledger' ? 'Φύλλο διαχειριστή' : 'Κοινόχρηστα')) . ' ' . ($ctx['fields']['period.title'] ?? '') . ' - ' . $b->title;
    $back = Url::to('view=period&id=' . (int) $p->id . '&tab=print');
    $logo = Settings::asset((string) ($account->logo ?? ""));
    $ctx['images'] = $logo !== '' ? ['account.logo' => $logo] : [];
    $only = $in->getInt('unit_id', 0);
    if ($type === 'notice' && $only > 0) {
        $idx = array_search($only, array_map('intval', $ctx['unit_ids'] ?? []), true);
        $ctx['units'] = $idx === false ? [] : [$ctx['units'][$idx]];
        $ctx['unit_expenses'] = $idx === false ? [] : [$ctx['unit_expenses'][$idx] ?? []];
    }
    $ctx['fields']['print.date'] = date('d/m/Y');
    if ($type === 'ledger') {
        $ctx = Repo::withPayments($p, $ctx);
    }
}
// Ειδοποιητήρια: ανάλυση δαπανών αναλυτικά ή ανά κατηγορία (προεπιλογή από τον λογαριασμό).
$grouping = in_array($in->getCmd('grouping', ''), ['detailed', 'grouped'], true) ? $in->getCmd('grouping', '') : (string) Accounts::settings($account)['notice_grouping'];
if ($type === 'notice' && $grouping === 'grouped') {
    $ctx = Render::groupExpenses($ctx);
}
if (!$tpl) Auth::deny('Δεν υπάρχει πρότυπο εκτύπωσης.', 'view=templates');

$layout = Repo::layout($tpl);
$acc = Accounts::settings($account);
$dx = round((float) $acc['offset_x'] + Calc::parseNumber($in->getString('dx', '0')), 1);
$dy = round((float) $acc['offset_y'] + Calc::parseNumber($in->getString('dy', '0')), 1);
$grid = $in->getInt('grid', 0) === 1;
$draft = !$sample && !$issued && $in->getInt('draft', 1) === 1;
$bg = $tpl->background && $in->getInt('bg', $mode === 'data' ? 1 : 0) ? Settings::asset($tpl->background) : '';

Factory::getApplication()->getDocument()->setTitle($title);
$media = rtrim(Uri::root(true), '/') . '/media/com_koinoxrista/';
$templates = Repo::templates($type);
?>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geom:wght@300..900&display=swap">
<link rel="stylesheet" href="<?= $media ?>css/app.css?v=1.9.0">
<link rel="stylesheet" href="<?= $media ?>css/print.css?v=1.9.0">
<script src="<?= $media ?>js/app.js?v=1.9.0" defer></script>
<?php
$pagesHtml = ($type === 'notice' && empty($ctx['units'])) ? '' : Render::document($type, $layout, $ctx, ['mode' => $mode, 'offset_x' => $dx, 'offset_y' => $dy, 'grid' => $grid, 'background' => $bg, 'draft' => $draft]);
$pageCount = substr_count($pagesHtml, 'class="kx-page"');
$typeLabel = $sample ? 'Δοκιμαστική εκτύπωση προτύπου' : ($type === 'notice' ? 'Ειδοποιητήρια' . (($only ?? 0) > 0 ? ' · ' . ($ctx['units'][0]['unit.code'] ?? '') : ' · ' . count($ctx['units']) . ' ιδιοκτησίες') : ($type === 'ledger' ? 'Φύλλο διαχειριστή · βεβαίωση εισπράξεων' : 'Κατάσταση κοινοχρήστων'));
$modes = ['full' => ['Λευκό χαρτί', 'Έντυπο + δεδομένα'], 'data' => ['Προτυπωμένο', 'Μόνο δεδομένα'], 'form' => ['Κενό έντυπο', 'Χωρίς δεδομένα']];
?>
<header class="kx-pv-head kx-noprint">
  <form method="get" action="<?= Url::e() ?>" class="kx-pv-form" data-pv-form>
    <?php foreach ($in->getArray() as $k => $v): if (in_array($k, ['option', 'tmpl', 'mode', 'dx', 'dy', 'grid', 'draft', 'bg', 'grouping', $type . '_template', 'template_id', 'Itemid'], true) || is_array($v)) continue; ?>
      <input type="hidden" name="<?= Fmt::e($k) ?>" value="<?= Fmt::e($v) ?>">
    <?php endforeach; ?>
    <input type="hidden" name="option" value="com_koinoxrista"><input type="hidden" name="tmpl" value="component">
    <div class="kx-pv-top">
      <a class="kx-pv-back" href="<?= Fmt::e($back) ?>" title="Πίσω"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></a>
      <div class="kx-pv-title">
        <small><?= Fmt::e($typeLabel) ?></small>
        <strong><?= Fmt::e($sample ? $tpl->title : (($ctx['fields']['period.title'] ?? '') . ' · ' . ($b->title ?? ''))) ?></strong>
      </div>
      <?php if (!$sample && !$issued): ?><span class="kx-pv-badge">Πρόχειρο</span><?php endif; ?>
      <span class="kx-flex"></span>
      <button type="button" class="kx-btn kx-btn-primary kx-pv-print" onclick="window.print()"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/></svg>Εκτύπωση</button>
    </div>
    <div class="kx-pv-tools">
      <?php if (!$sample): ?>
      <label class="kx-pv-field"><span>Πρότυπο</span>
        <select name="<?= $type ?>_template" onchange="this.form.submit()">
          <?php foreach ($templates as $t): ?><option value="<?= (int) $t->id ?>"<?= (int) $t->id === (int) $tpl->id ? ' selected' : '' ?>><?= Fmt::e($t->title) ?></option><?php endforeach; ?>
        </select></label>
      <?php else: ?><input type="hidden" name="template_id" value="<?= (int) $tpl->id ?>"><?php endif; ?>
      <div class="kx-pv-field"><span>Χαρτί</span>
        <div class="kx-seg" role="radiogroup">
          <?php foreach ($modes as $mk => [$ml, $mh]): ?>
            <label title="<?= Fmt::e($mh) ?>"><input type="radio" name="mode" value="<?= $mk ?>"<?= $mode === $mk ? ' checked' : '' ?> onchange="this.form.submit()"><span><?= Fmt::e($ml) ?></span></label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php if ($type === 'notice'): ?>
      <div class="kx-pv-field"><span>Ανάλυση δαπανών</span>
        <div class="kx-seg" role="radiogroup">
          <label><input type="radio" name="grouping" value="detailed"<?= $grouping !== 'grouped' ? ' checked' : '' ?> onchange="this.form.submit()"><span>Αναλυτικά</span></label>
          <label><input type="radio" name="grouping" value="grouped"<?= $grouping === 'grouped' ? ' checked' : '' ?> onchange="this.form.submit()"><span>Ανά κατηγορία</span></label>
        </div>
      </div>
      <?php endif; ?>
      <div class="kx-pv-field"><span>Μετατόπιση (mm)</span>
        <div class="kx-pv-offset">
          <label title="Δεξιά (+) / αριστερά (−)">↔<input type="text" name="dx" value="<?= Fmt::e($in->getString('dx', '0')) ?>" inputmode="decimal" onchange="this.form.submit()"></label>
          <label title="Κάτω (+) / πάνω (−)">↕<input type="text" name="dy" value="<?= Fmt::e($in->getString('dy', '0')) ?>" inputmode="decimal" onchange="this.form.submit()"></label>
        </div>
      </div>
      <div class="kx-pv-field"><span>Εμφάνιση</span>
        <div class="kx-pv-toggles">
          <label class="kx-switch"><input type="checkbox" name="grid" value="1"<?= $grid ? ' checked' : '' ?> onchange="this.form.submit()"><i></i>Πλέγμα</label>
          <?php if ($tpl->background): ?><label class="kx-switch" title="Το σκαναρισμένο έντυπο φαίνεται μόνο στην οθόνη"><input type="hidden" name="bg" value="0"><input type="checkbox" name="bg" value="1"<?= $bg ? ' checked' : '' ?> onchange="this.form.submit()"><i></i>Φόντο εντύπου</label><?php endif; ?>
          <?php if (!$sample && !$issued): ?><label class="kx-switch"><input type="hidden" name="draft" value="0"><input type="checkbox" name="draft" value="1"<?= $draft ? ' checked' : '' ?> onchange="this.form.submit()"><i></i>«ΠΡΟΣΧΕΔΙΟ»</label><?php endif; ?>
        </div>
      </div>
    </div>
  </form>
</header>
<div class="kx-pages">
<?php if ($pagesHtml === ''): ?>
  <p class="kx-noprint kx-pv-empty">Δεν υπάρχουν ιδιοκτησίες για εκτύπωση.</p>
<?php else: ?>
  <?= $pagesHtml ?>
<?php endif; ?>
</div>
<footer class="kx-pv-foot kx-noprint">
  <span class="kx-pv-count"><strong><?= $pageCount ?></strong> <?= $pageCount === 1 ? 'σελίδα' : 'σελίδες' ?> A4 · <?= ($layout['orientation'] ?? 'portrait') === 'landscape' ? 'οριζόντιο' : 'κάθετο' ?></span>
  <span class="kx-pv-hint">Στο παράθυρο εκτύπωσης: <b>Περιθώρια: Κανένα</b> · <b>Κλίμακα: 100%</b> · χωρίς κεφαλίδες/υποσέλιδα<?= $acc['offset_x'] || $acc['offset_y'] ? ' · μόνιμη μετατόπιση εκτυπωτή ' . Fmt::e(Fmt::qty($acc['offset_x'], 1)) . ' / ' . Fmt::e(Fmt::qty($acc['offset_y'], 1)) . ' mm' : '' ?></span>
  <span class="kx-flex"></span>
  <button type="button" class="kx-btn kx-btn-primary" onclick="window.print()">Εκτύπωση</button>
</footer>
<style>@media screen{body{background:#e5e7eb!important;margin:0}}</style>
