<?php

namespace Timber\WpI18nTwig\Utils;

use ReflectionClass;
use Timber\WpI18nTwig\Twig\NodeVisitor\TranslationNodeVisitor;
use Timber\WpI18nTwig\Twig\StubbedEnvironment;
use Twig\Lexer;
use Twig\Source;
use WP_CLI\I18n\PhpFunctionsScanner;

class TwigFunctionsScanner extends PhpFunctionsScanner {

	protected $functions = [];

	public function __construct( $code ) {
		$twig = new StubbedEnvironment();

		if ( ! class_exists( 'Twig\i18nLexer' ) ) {
			// Dirty hack to make all lexer methods/properties public and grab Twig comments.
			$lexer_ref  = new ReflectionClass( Lexer::class );
			$lexer_path = $lexer_ref->getFileName();
			$lexer_code = file_get_contents( (string) $lexer_path );
			if ( false === $lexer_code ) {
				throw new \RuntimeException( 'Could not read Twig Lexer source code.' );
			}
			$lexer_code = str_replace( '<?php', '', $lexer_code );
			$lexer_code = preg_replace( '/(private) (\$[a-zA-Z]+|function)/m', 'public $2', $lexer_code );
			$lexer_code = str_replace( 'class Lexer', 'class i18nLexer extends Lexer', (string) $lexer_code );

			// @codingStandardsIgnoreStart
			eval( $lexer_code );
		}

		$lexer = new class($twig) extends \Twig\i18nLexer {
			/**
			 * @var list<array{
			 *   'comment': string,
			 *   'lineno': int
			 * }>
			 */
			private $comments = [];

			/**
			 * @return list<array{
			 *   'comment': string,
			 *   'lineno': int
			 * }>
			 */
			public function getComments(): array {
				return $this->comments;
			}

			/**
			 * Collect the comment Twig just lexed.
			 *
			 * Variadic on purpose: Twig 3.29 added an `$isDocumentation` argument to
			 * `lexComment()`, and a declaration mismatch here is a fatal error rather
			 * than something that can be caught and worked around. Letting Twig do the
			 * lexing also keeps the comment syntax its business, not ours.
			 *
			 * @param mixed ...$args
			 */
			public function lexComment( ...$args ): void {
				$lineno = $this->lineno;
				$start  = $this->cursor;

				parent::lexComment( ...$args );

				// Twig moved the cursor past the closing tag, so drop it back off.
				$raw = substr( $this->code, $start, $this->cursor - $start );
				$end = strrpos( $raw, $this->options['tag_comment'][1] );

				$this->comments[] = [
					// Whatever is left of `-#}`, `~#}` or `##}` goes with the whitespace.
					'comment' => trim( false === $end ? $raw : substr( $raw, 0, $end ), " \t\n\r\0\x0B-~#" ),
					'lineno'  => $lineno,
				];
			}
		};
		// @codingStandardsIgnoreEnd

		$twig->setLexer( $lexer );

		$visitor = new TranslationNodeVisitor();
		$twig->addNodeVisitor( $visitor );
		$token_stream = $twig->tokenize( new Source( $code, '' ) );
		// Comments are available only after tokenization
		$visitor->setComments( $lexer->getComments() );
		$twig->parse( $token_stream );
		$this->functions = $visitor->getFunctions();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFunctions( array $constants = [] ) {
		return array_map( [ $this, 'convertComments' ], $this->functions );
	}

	/**
	 * Convert comments to ParsedComment objects.
	 *
	 * @param array $func
	 * @return array
	 */
	private function convertComments( array $func ) {
		if ( ! isset( $func[3] ) ) {
			return $func;
		}

		foreach ( $func[3] as $k => $extracted_comment ) {
			$comment = $this->parsePhpComment( $extracted_comment['comment'], $extracted_comment['lineno'] );
			unset( $func[3][ $k ] );
			if ( $comment ) {
				// Get the last valid comment.
				$func[3][0] = $comment;
			}
		}

		return $func;
	}
}
