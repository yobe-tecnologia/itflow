<?php
// include/i18n.php — compat 5.6+

if (!function_exists('__t_locale_normalize')) {
  function __t_locale_normalize($locale) {
    $l = strtolower(trim((string)$locale));
    if ($l === 'pt' || $l === 'pt-br' || $l === 'pt_br') return 'pt_BR';
    if ($l === 'es' || $l === 'es-es' || $l === 'es_es') return 'es';
    return 'en';
  }
}

if (!function_exists('__t_load')) {
  function __t_load($locale) {
    static $cache = array();
    $locale = __t_locale_normalize($locale);
    if (isset($cache[$locale])) return $cache[$locale];

    $base = dirname(__FILE__) . '/../plugins/i18n/lang/';
    $map = array(
      'en'    => $base . 'en.php',
      'pt_BR' => $base . 'pt_br.php',
      'es'    => $base . 'es.php',
    );

    $translations = array();
    if (isset($map[$locale]) && file_exists($map[$locale])) {
      $translations = require $map[$locale];
      if (!is_array($translations)) $translations = array();
    }

    if ($locale !== 'en' && isset($map['en']) && file_exists($map['en'])) {
      $en = require $map['en'];
      if (!is_array($en)) $en = array();
      $translations = array_replace($en, $translations);
    }

    $cache[$locale] = $translations;
    return $translations;
  }
}

if (!function_exists('__t_resolve_locale')) {
  function __t_resolve_locale($companyLocaleFromDb = null) {
    if (!empty($companyLocaleFromDb)) return __t_locale_normalize($companyLocaleFromDb);
    if (!empty($GLOBALS['app_locale']) && is_string($GLOBALS['app_locale'])) {
      return __t_locale_normalize($GLOBALS['app_locale']);
    }
    return 'en';
  }
}

if (!function_exists('__')) {
  function __($key) {
    $args = func_get_args();
    array_shift($args);

    $companyLocale = isset($GLOBALS['CURRENT_COMPANY_LOCALE']) ? $GLOBALS['CURRENT_COMPANY_LOCALE'] : null;
    $locale = __t_resolve_locale($companyLocale);
    $dict = __t_load($locale);

    $text = isset($dict[$key]) ? $dict[$key] : $key;

    if (!empty($args)) {
      $out = @vsprintf($text, $args);
      return $out !== false ? $out : $text;
    }
    return $text;
  }
}
