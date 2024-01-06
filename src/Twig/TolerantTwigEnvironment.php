<?php

namespace Timber\WpI18nTwig\Twig;

use Timber\WpI18nTwig\Twig\TokenParser\DummyTokenParser;
use Twig\Environment;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class TolerantTwigEnvironment extends Environment {

	public function getFilter( string $name ): ?TwigFilter {
		$twig_filter = parent::getFilter( $name );
		if ( $twig_filter instanceof TwigFilter ) {
			return $twig_filter;
		}

		return new TwigFilter(
			$name,
			function ( $value ) {
				return $value;
			}
		);
	}

	public function getFunction( string $name ): ?TwigFunction {
		$twig_function = parent::getFunction( $name );
		if ( $twig_function instanceof TwigFunction ) {
			return $twig_function;
		}

		return new TwigFunction(
			$name,
			function ( $value ) {
				return $value;
			}
		);
	}

	public function getTest( string $name ): ?TwigTest {
		$twig_test = parent::getFunction( $name );
		if ( $twig_test instanceof TwigTest ) {
			return $twig_test;
		}

		return new TwigTest(
			$name,
			function () {
				return true;
			}
		);
	}
}
