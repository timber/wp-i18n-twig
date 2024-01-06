<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

$wp_i18n_twig_autoloader = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $wp_i18n_twig_autoloader ) ) {
	require_once $wp_i18n_twig_autoloader;
}
if ( ! function_exists( 'wp_i18n_twig_get_longdesc' ) ) {
	/**
	 * Dirty hack to add --skip-twig parameter to the parent command.
	 *
	 * @return string|false
	 */
	function wp_i18n_twig_get_longdesc() {
		$make_pot_ref = new ReflectionClass( WP_CLI\I18n\MakePotCommand::class );
		try {
			$doc_comment = $make_pot_ref->getMethod( '__invoke' )->getDocComment();
			$needle      = "\t * [--skip-blade]\n";
			$replace     = "\t * [--skip-twig]\n\t * : Skips Twig string extraction.\n\t *\n" . $needle;
			return str_replace( $needle, $replace, $doc_comment );
		} catch ( ReflectionException $e ) {
			return false;
		}
	}
}
$wp_i18n_twig_longdesc = wp_i18n_twig_get_longdesc();
WP_CLI::add_command(
	'i18n make-pot',
	'\Timber\WpI18nTwig\MakePotCommand',
	array_filter(
		array(
			'before_invoke' => static function () {
				if ( ! function_exists( 'mb_ereg' ) ) {
					WP_CLI::error( 'The mbstring extension is required for string extraction to work reliably.' );
				}
			},
			'longdesc'      => $wp_i18n_twig_longdesc,
		)
	)
);
