<?php
/**
 * ITFlow - i18n minimalista (semântico zero)
 * - 1 arquivo por idioma em plugins/i18n (ex.: pt_BR.php) que retorna:
 *     return ["Sign in" => "Login", "Email" => "E-mail", ...];
 * - Tradução por texto literal: __('Sign in')
 * - Placeholders simples: __('Welcome, {name}!', ['name' => 'Ana'])
 *
 * Dependências:
 * - (Opcional) config.php define:
 *     $config['i18n']['default_locale']  = 'en';
 *     $config['i18n']['allowed_locales'] = ['en','en_GB','pt_BR','es_ES'];
 *     $config['i18n']['plugin_dir']      = __DIR__;
 *
 * Integração:
 * - Em um include central (logo após functions.php/get_settings.php), use apenas:
 *     require_once __DIR__ . '/../plugins/i18n/i18n.php';
 */

if (session_status() === PHP_SESSION_NONE) { @session_start(); }

// --- 1) Configs (preferir as do config.php se existirem)
global $config;
$__I18N_DIR__     = $config['i18n']['plugin_dir']      ?? __DIR__;
$__ALLOWED__      = $config['i18n']['allowed_locales'] ?? ['en','en_GB','pt_BR','es_ES'];
$__DEFAULT__      = $config['i18n']['default_locale']  ?? 'en';

// --- 2) Descobrir locale (ordem: sessão → settings → ?lang → Accept-Language → default)
global $settings; // ITFlow já carrega config/settings amplamente
$userLang = $_SESSION['user_language']           ?? null;             // se você popular
$compLang = $settings['config_language']         ?? null;             // se existir na tabela settings
$urlLang  = $_GET['lang']                        ?? null;
$httpLang = function_exists('locale_accept_from_http')
  ? (locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '') ?: null)
  : null;

$__LOCALE__ = $userLang ?: $compLang ?: $urlLang ?: $httpLang ?: $__DEFAULT__;
if (!in_array($__LOCALE__, $__ALLOWED__, true)) $__LOCALE__ = $__DEFAULT__;

// --- 3) Carregar catálogo do idioma (um arquivo por idioma)
$__CATALOG__ = [];
$__FILE__ = rtrim($__I18N_DIR__, '/\\') . DIRECTORY_SEPARATOR . $__LOCALE__ . '.php';
if (is_file($__FILE__)) {
    $arr = include $__FILE__;
    if (is_array($arr)) $__CATALOG__ = $arr;
}

// --- 4) Helpers globais

/**
 * Traduz literal. Se não existir tradução, devolve o original.
 * Suporta {placeholders}.
 * Ex.: __('Welcome, {name}!', ['name' => 'Ana'])
 */
function __(string $text, array $params = []): string {
    global $__CATALOG__;
    $translated = $__CATALOG__[$text] ?? $text;
    foreach ($params as $k => $v) {
        $translated = str_replace('{'.$k.'}', (string)$v, $translated);
    }
    return $translated;
}

/** Obtém locale corrente (ex.: 'pt_BR') */
function i18n_locale(): string {
    global $__LOCALE__;
    return $__LOCALE__;
}

/** Troca o locale em tempo de execução e recarrega o catálogo */
function i18n_set_locale(string $locale): void {
    global $__ALLOWED__, $__DEFAULT__, $__I18N_DIR__, $__LOCALE__, $__CATALOG__;
    if (!in_array($locale, $__ALLOWED__, true)) $locale = $__DEFAULT__;
    $__LOCALE__ = $locale;
    $file = rtrim($__I18N_DIR__, '/\\') . DIRECTORY_SEPARATOR . $locale . '.php';
