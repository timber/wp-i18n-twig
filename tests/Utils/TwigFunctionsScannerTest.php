<?php

namespace Timber\WpI18nTwig\Tests\Utils;

use Gettext\Utils\ParsedComment;
use PHPUnit\Framework\TestCase;
use Timber\WpI18nTwig\TwigCodeExtractor;
use Timber\WpI18nTwig\Utils\TwigFunctionsScanner;

/**
 * These run without WordPress or WP-CLI bootstrapped, so they are cheap enough to run on
 * every PHP version in CI, against whatever Twig Composer resolves at the time. The lexer
 * hack in the scanner is the part that breaks on a Twig upgrade, so that is what is
 * covered here.
 *
 * @see https://github.com/timber/wp-i18n-twig/issues/10
 */
class TwigFunctionsScannerTest extends TestCase {

	public function testExtractsTranslationFunctions(): void {
		$functions = $this->scan(
			implode(
				"\n",
				[
					"{{ __( 'hello', 'foo' ) }}",
					"{{ _x( 'Post', 'noun', 'foo' ) }}",
					"{{ _n( 'one', 'many', count, 'foo' ) }}",
				]
			)
		);

		$this->assertCount( 3, $functions );
		$this->assertSame( '__', $functions[0][0] );
		$this->assertSame( 'hello', $functions[0][2][0] );
		$this->assertSame( '_x', $functions[1][0] );
		$this->assertSame( 'noun', $functions[1][2][1] );
		$this->assertSame( '_n', $functions[2][0] );
	}

	public function testExtractsCommentOnThePrecedingLine(): void {
		$this->assertSame(
			'translators: a greeting',
			$this->commentFor( "{# translators: a greeting #}\n{{ __( 'hello', 'foo' ) }}" )
		);
	}

	public function testExtractsCommentOnTheSameLine(): void {
		$this->assertSame(
			'translators: a greeting',
			$this->commentFor( "{# translators: a greeting #} {{ __( 'hello', 'foo' ) }}" )
		);
	}

	/**
	 * `{#-` and `-#}` strip surrounding whitespace from the rendered output. The markers
	 * are not part of the comment.
	 */
	public function testExtractsWhitespaceControlComment(): void {
		$this->assertSame(
			'translators: a greeting',
			$this->commentFor( "{#- translators: a greeting -#}\n{{ __( 'hello', 'foo' ) }}" )
		);

		$this->assertSame(
			'translators: a greeting',
			$this->commentFor( "{#~ translators: a greeting ~#}\n{{ __( 'hello', 'foo' ) }}" )
		);
	}

	/**
	 * Twig 3.29 added `{## ... ##}` documentation comments, lexed through a separate
	 * regex. Older versions read the same input as an ordinary comment whose body
	 * carries a stray `#` at each end, so the extracted text matches either way.
	 */
	public function testExtractsDocumentationComment(): void {
		$this->assertSame(
			'translators: a greeting',
			$this->commentFor( "{## translators: a greeting ##}\n{{ __( 'hello', 'foo' ) }}" )
		);
	}

	public function testIgnoresCommentsWithoutTheTranslatorsPrefix(): void {
		$this->assertNull(
			$this->commentFor( "{# just a note #}\n{{ __( 'hello', 'foo' ) }}" )
		);
	}

	public function testIgnoresCommentsThatAreTooFarAway(): void {
		$this->assertNull(
			$this->commentFor( "{# translators: a greeting #}\n\n{{ __( 'hello', 'foo' ) }}" )
		);
	}

	public function testReportsTheLineOfTheComment(): void {
		$functions = $this->scan(
			implode(
				"\n",
				[
					'<p>filler</p>',
					'<p>filler</p>',
					'{# translators: a greeting #}',
					"{{ __( 'hello', 'foo' ) }}",
				]
			)
		);

		$this->assertSame( 4, $functions[0][1] );
		$this->assertInstanceOf( ParsedComment::class, $functions[0][3][0] );
		$this->assertSame( 3, $functions[0][3][0]->getFirstLine() );
	}

	/**
	 * Comments are collected while lexing, so a template that never closes one has to
	 * fail the way Twig would on its own.
	 */
	public function testAnUnclosedCommentIsASyntaxError(): void {
		$this->expectException( \Twig\Error\SyntaxError::class );

		$this->scan( "{# translators: never closed\n{{ __( 'hello', 'foo' ) }}" );
	}

	/**
	 * @return array<int, array<int, mixed>>
	 */
	private function scan( string $code ): array {
		$scanner = new TwigFunctionsScanner( $code );
		$scanner->enableCommentsExtraction( TwigCodeExtractor::$options['extractComments'] );

		return array_values( $scanner->getFunctions() );
	}

	/**
	 * The extracted translator comment for the first function call, if any.
	 */
	private function commentFor( string $code ): ?string {
		$functions = $this->scan( $code );

		if ( ! isset( $functions[0][3][0] ) ) {
			return null;
		}

		return $functions[0][3][0]->getComment();
	}
}
