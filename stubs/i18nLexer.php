<?php
/**
 * Stub for the lexer copy that `TwigFunctionsScanner` builds at runtime.
 *
 * The scanner reads Twig's own `Lexer` source, rewrites `private` to `public`, and
 * evaluates the result as `Twig\i18nLexer` so that `lexComment()` can be overridden.
 * Static analysis cannot see a class that only exists after `eval()`, so this file
 * declares it instead. It is referenced from `phpstan.neon.dist` under `scanFiles`.
 *
 * Only the members the anonymous subclass touches are declared. Keeping it minimal is
 * deliberate: a full copy of `Lexer` would have to be re-synced with every Twig release,
 * and would be wrong in exactly the way that broke
 * {@link https://github.com/timber/wp-i18n-twig/issues/10}.
 */

namespace Twig;

class i18nLexer extends Lexer {

	/**
	 * The template source being lexed, with newlines normalized.
	 *
	 * @var string
	 */
	public $code;

	/**
	 * Offset in {@see self::$code} of the next character to lex.
	 *
	 * @var int
	 */
	public $cursor;

	/**
	 * Line number the cursor currently sits on.
	 *
	 * @var int
	 */
	public $lineno;

	/**
	 * Lexer options. Only the keys this package reads are described; add to the shape
	 * rather than widening it, so that an undeclared key is a static analysis error.
	 *
	 * @var array{tag_comment: array{0: string, 1: string}}
	 */
	public $options;

	/**
	 * Lex a comment, starting at the cursor.
	 *
	 * Declared variadic because the real signature moves: Twig 3.29 added a
	 * `bool $isDocumentation = false` argument for `{## ... ##}` comments.
	 *
	 * @param mixed ...$args
	 */
	public function lexComment( ...$args ): void {}
}
