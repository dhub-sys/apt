<?php
namespace Koinoxrista\Component\Koinoxrista\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;

/**
 * Προστασία των δημόσιων φορμών (σύνδεση, εγγραφή) από bots:
 * - Honeypot: κρυφό πεδίο που ο άνθρωπος δεν βλέπει + χρονικό όριο (υποβολή σε < 1 δευτ. = bot).
 * - Cloudflare Turnstile: προαιρετικό, με site key / secret key από τις ρυθμίσεις πλατφόρμας.
 */
final class Guard
{
    private const TRAP = 'kx_website';
    private const MIN_SECONDS = 1;

    public static function turnstileOn(): bool
    {
        return (int) Settings::get('turnstile_enabled') === 1 && trim((string) Settings::get('turnstile_site_key')) !== '' && trim((string) Settings::get('turnstile_secret')) !== '';
    }

    /** Πεδία μέσα στη φόρμα. */
    public static function fields(): string
    {
        $h = '';
        if ((int) Settings::get('honeypot_enabled')) {
            $t = time();
            $h .= '<div class="kx-hp" aria-hidden="true"><label>Website<input type="text" name="' . self::TRAP . '" tabindex="-1" autocomplete="off"></label></div>'
                . '<input type="hidden" name="kx_ts" value="' . $t . '.' . self::sign((string) $t) . '">';
        }
        if (self::turnstileOn()) {
            $h .= '<div class="cf-turnstile kx-turnstile" data-sitekey="' . Fmt::e(trim((string) Settings::get('turnstile_site_key'))) . '" data-language="el" data-theme="light"></div>'
                . '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
        }
        return $h;
    }

    /** null = εντάξει, αλλιώς μήνυμα σφάλματος για τον χρήστη. */
    public static function check(): ?string
    {
        $in = Factory::getApplication()->getInput();
        if ((int) Settings::get('honeypot_enabled')) {
            if (trim((string) $in->post->getString(self::TRAP, '')) !== '') {
                return 'Η φόρμα δεν υποβλήθηκε. Δοκιμάστε ξανά.';
            }
            [$t, $sig] = array_pad(explode('.', (string) $in->post->getString('kx_ts', ''), 2), 2, '');
            if (!ctype_digit($t) || !hash_equals(self::sign($t), $sig) || time() - (int) $t < self::MIN_SECONDS || time() - (int) $t > 86400) {
                return 'Η φόρμα έληξε ή υποβλήθηκε πολύ γρήγορα. Δοκιμάστε ξανά.';
            }
        }
        if (self::turnstileOn()) {
            $token = (string) $in->post->getString('cf-turnstile-response', '');
            if ($token === '' || !self::verifyTurnstile($token, (string) $in->server->getString('REMOTE_ADDR', ''))) {
                return 'Η επαλήθευση ασφαλείας (Turnstile) απέτυχε. Δοκιμάστε ξανά.';
            }
        }
        return null;
    }

    private static function verifyTurnstile(string $token, string $ip): bool
    {
        try {
            $res = HttpFactory::getHttp()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => trim((string) Settings::get('turnstile_secret')), 'response' => $token, 'remoteip' => $ip,
            ], [], 10);
            $data = json_decode((string) $res->getBody(), true);
            return !empty($data['success']);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function sign(string $v): string
    {
        return substr(hash_hmac('sha256', 'kxform:' . $v, (string) Factory::getApplication()->get('secret')), 0, 16);
    }
}
