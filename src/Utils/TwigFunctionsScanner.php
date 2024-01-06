<?php

namespace Timber\WpI18nTwig\Utils;

use ReflectionClass;
use Timber\WpI18nTwig\Twig\NodeVisitor\TranslationNodeVisitor;
use Timber\WpI18nTwig\Twig\TolerantTwigEnvironment;
use Twig\Error\SyntaxError;
use Twig\Lexer;
use Twig\Loader\ArrayLoader;
use Twig\Source;
use WP_CLI\I18n\PhpFunctionsScanner;

class TwigFunctionsScanner extends PhpFunctionsScanner {

	protected $functions = [];

	public function __construct( $code ) {
		$twig = new TolerantTwigEnvironment(
			new ArrayLoader( [] ),
			[
				'cache'         => false,
				'autoescape'    => false,
				'optimizations' => 0,
			]
		);

		if ( ! class_exists( 'Twig\i18nLexer' ) ) {
			// Dirty hack to make all lexer methods/properties public and grab Twig comments.
			$lexer_ref  = new ReflectionClass( Lexer::class );
			$lexer_path = $lexer_ref->getFileName();
			$lexer_code = file_get_contents( $lexer_path );
			$lexer_code = str_replace( '<?php', '', $lexer_code );
			$lexer_code = preg_replace( '/(private) (\$[a-zA-Z]+|function)/m', 'public $2', $lexer_code );
			$lexer_code = str_replace( 'class Lexer', 'class i18nLexer extends Lexer', $lexer_code );

			// @codingStandardsIgnoreStart
			eval( $lexer_code );
		}

		$lexer = new class($twig) extends \Twig\i18nLexer {
			private $comments = [];
			public function getComments(): array {
				return $this->comments;
			}
			public function lexComment(): void {
				if ( ! preg_match( $this->regexes['lex_comment'], $this->code, $match, \PREG_OFFSET_CAPTURE, $this->cursor ) ) {
					throw new SyntaxError( 'Unclosed comment.', $this->lineno, $this->source );
				}

				$comment          = substr( $this->code, $this->cursor, $match[0][1] - $this->cursor );
				$this->comments[] = [
					'comment' => trim( $comment ),
					'lineno'  => $this->lineno,
				];

				$this->moveCursor( $comment . $match[0][0] );
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
