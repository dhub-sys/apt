<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Fmt;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Ui;
use Koinoxrista\Component\Koinoxrista\Site\Helper\Url;

$session = Factory::getApplication()->getSession();
if (!(int) $session->get('kx.pending_user', 0)) {
    Factory::getApplication()->redirect(Url::to('view=app'));
    return;
}
$masked = (string) $session->get('kx.otp_email', '');
echo Ui::bare('<h2>Κωδικός επιβεβαίωσης</h2><p class="kx-muted">Στείλαμε έναν 6ψήφιο κωδικό στο email' . ($masked !== '' ? ' <strong>' . Fmt::e($masked) . '</strong>' : ' σας') . '. Ισχύει για 5 λεπτά.</p>'
    . Ui::form('auth.verify', 'class="kx-auth-form"')
    . '<label class="kx-field"><span>Κωδικός</span><input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="\d{6}" required autofocus class="kx-otp"></label>'
    . '<button type="submit" class="kx-btn kx-btn-primary kx-btn-block">Επιβεβαίωση</button></form>'
    . '<div class="kx-auth-alt">' . Ui::postButton('auth.resend', [], 'Αποστολή νέου κωδικού', 'link') . ' · <a href="' . Url::e('task=auth.logout') . '">Ακύρωση</a></div>');
