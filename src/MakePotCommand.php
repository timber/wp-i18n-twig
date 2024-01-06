<?php

namespace Timber\WpI18nTwig;

use Gettext\Translation;
use WP_CLI;
use WP_CLI\I18n\MakePotCommand as BaseMakePotCommand;
use WP_CLI\I18n\PotGenerator;
use WP_CLI\Utils;

class MakePotCommand extends BaseMakePotCommand {

	/**
	 * @var bool
	 */
	protected $skip_twig = false;

	// Dummy override so longdesc is preferred to parent Docblock
	// @codingStandardsIgnoreLine
	public function __invoke( $args, $assoc_args ) {
		return parent::__invoke( $args, $assoc_args );
	}

	public function handle_arguments( $args, $assoc_args ) {
		$this->skip_twig = Utils\get_flag_value( $assoc_args, 'skip-twig', $this->skip_twig );
		parent::handle_arguments( $args, $assoc_args );
	}

	protected function extract_strings() {
		$translations = parent::extract_strings();

		try {
			if ( ! $this->skip_twig ) {
				$options = [
					'include'       => $this->include,
					'exclude'       => $this->exclude,
					'extensions'    => [ 'twig' ],
					'addReferences' => $this->location,
				];
				TwigCodeExtractor::fromDirectory( $this->source, $translations, $options );
			}
		} catch ( \Exception $e ) {
			WP_CLI::error( $e->getMessage() );
		}

		foreach ( $this->exceptions as $file => $exception_translations ) {
			/** @var Translation $exception_translation */
			foreach ( $exception_translations as $exception_translation ) {
				if ( ! $translations->find( $exception_translation ) ) {
					continue;
				}

				if ( $this->subtract_and_merge ) {
					$translation = $translations[ $exception_translation->getId() ];
					$exception_translation->mergeWith( $translation );
				}

				unset( $translations[ $exception_translation->getId() ] );
			}

			if ( $this->subtract_and_merge ) {
				PotGenerator::toFile( $exception_translations, $file );
			}
		}

		if ( ! $this->skip_audit ) {
			$this->audit_strings( $translations );
		}

		return $translations;
	}
}
