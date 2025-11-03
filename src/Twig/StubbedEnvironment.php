<?php

namespace Timber\WpI18nTwig\Twig;

use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;
use Symfony\Bridge\Twig\TokenParser\FormThemeTokenParser;
use Symfony\Bridge\Twig\TokenParser\StopwatchTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Symfony\UX\TwigComponent\Twig\ComponentLexer;
use Symfony\UX\TwigComponent\Twig\ComponentTokenParser as TwigComponentTokenParser;
use Symfony\UX\TwigComponent\Twig\PropsTokenParser;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Extra\Cache\TokenParser\CacheTokenParser;
use Twig\Loader\ArrayLoader;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use TwigCsFixer\Environment\Parser\ComponentTokenParser;

/**
 * All credits goes to Vincent Langlet for this class.
 *
 * Provide stubs for all filters, functions, tests and tags that are not defined in twig's core.
 *
 * @see https://github.com/VincentLanglet/Twig-CS-Fixer/blob/6b236c344260d199c3f52fbc8fc9f4b1b4a19bb3/src/Environment/StubbedEnvironment.php
 */
final class StubbedEnvironment extends Environment {

	/**
	 * @var array<string, TwigFilter|null>
	 */
	private $stub_filters = [];

	/**
	 * @var array<string, TwigFunction|null>
	 */
	private $stub_functions = [];

	/**
	 * @var array<string, TwigTest|null>
	 */
	private $stub_tests = [
		'divisible' => null, // Allow 'divisible by'
		'same'      => null, // Allow 'same as'
	];

	/**
	 * @param ExtensionInterface[]   $custom_twig_extensions
	 * @param TokenParserInterface[] $custom_token_parsers
	 * @param NodeVisitorInterface[] $custom_node_visitors
	 */
	public function __construct(
		array $custom_twig_extensions = [],
		array $custom_token_parsers = [],
		array $custom_node_visitors = []
	) {
		parent::__construct( new ArrayLoader() );

		$this->handleOptionalDependencies();

		foreach ( $custom_twig_extensions as $custom_twig_extension ) {
			$this->addExtension( $custom_twig_extension );
		}

		foreach ( $custom_token_parsers as $custom_token_parser ) {
			$this->addTokenParser( $custom_token_parser );
		}

		foreach ( $custom_node_visitors as $custom_node_visitor ) {
			$this->addNodeVisitor( $custom_node_visitor );
		}
	}

	/**
	 * Avoid dependency to composer/semver for twig version comparison.
	 */
	public static function satisfiesTwigVersion( int $major, int $minor = 0, int $patch = 0 ): bool {
		$version = explode( '.', self::VERSION );

		if ( $major < $version[0] ) {
			return true;
		}
		if ( $major > $version[0] ) {
			return false;
		}
		if ( $minor < $version[1] ) {
			return true;
		}
		if ( $minor > $version[1] ) {
			return false;
		}

		return $version[2] >= $patch;
	}

	/**
	 * @param string $name
	 */
	public function getFilter( $name ): ?TwigFilter {
		if ( ! \array_key_exists( $name, $this->stub_filters ) ) {
			$existing_filter             = parent::getFilter( $name );
			$this->stub_filters[ $name ] = $existing_filter instanceof TwigFilter
				? $existing_filter
				: new TwigFilter( $name );
		}

		return $this->stub_filters[ $name ];
	}

	/**
	 * @param string $name
	 */
	public function getFunction( $name ): ?TwigFunction {
		if ( ! \array_key_exists( $name, $this->stub_functions ) ) {
			$existing_function             = parent::getFunction( $name );
			$this->stub_functions[ $name ] = $existing_function instanceof TwigFunction
				? $existing_function
				: new TwigFunction( $name );
		}

		return $this->stub_functions[ $name ];
	}

	/**
	 * @param string $name
	 */
	public function getTest( $name ): ?TwigTest {
		if ( ! \array_key_exists( $name, $this->stub_tests ) ) {
			$existing_test             = parent::getTest( $name );
			$this->stub_tests[ $name ] = $existing_test instanceof TwigTest
				? $existing_test
				: new TwigTest( $name );
		}

		return $this->stub_tests[ $name ];
	}

	private function handleOptionalDependencies(): void {
		if ( class_exists( DumpTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new DumpTokenParser() );
		}
		if ( class_exists( FormThemeTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new FormThemeTokenParser() );
		}
		if ( class_exists( StopwatchTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new StopwatchTokenParser( true ) );
		}
		if ( class_exists( TransDefaultDomainTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new TransDefaultDomainTokenParser() );
		}
		if ( class_exists( TransTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new TransTokenParser() );
		}
		if ( class_exists( CacheTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new CacheTokenParser() );
		}
		if ( class_exists( TwigComponentTokenParser::class ) && class_exists( ComponentTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new ComponentTokenParser() );
		}
		if ( class_exists( PropsTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new PropsTokenParser() );
		}
		if ( class_exists( ComponentLexer::class ) ) {
			// @phpstan-ignore argument.type
			$this->setLexer( new ComponentLexer( $this ) );
		}
	}
}
