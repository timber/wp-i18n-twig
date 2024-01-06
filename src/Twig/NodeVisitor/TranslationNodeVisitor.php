<?php

namespace Timber\WpI18nTwig\Twig\NodeVisitor;

use Timber\WpI18nTwig\TwigCodeExtractor;
use Twig\Environment;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;

final class TranslationNodeVisitor extends AbstractNodeVisitor {

	private $functions = [];
	private $comments  = [];

	public function setComments( array $comments ) {
		$this->comments = $comments;
	}

	public function getFunctions(): array {
		return $this->functions;
	}

	protected function doEnterNode( Node $node, Environment $env ): Node {
		if (
			$node instanceof FunctionExpression
			&& in_array( $node->getAttribute( 'name' ), array_keys( TwigCodeExtractor::$options['functions'] ), true )
		) {
			$node_arguments = $node->getNode( 'arguments' );

			if ( $node_arguments->getIterator()->current() instanceof ConstantExpression ) {
				$this->functions[] = [
					$node->getAttribute( 'name' ), // function name
					$node->getTemplateLine(), // line
					[
						$this->getReadMessageFromArguments( $node_arguments, 0 ),
						$this->getReadDomainFromArguments( $node_arguments, 1 ),
						$this->getReadDomainFromArguments( $node_arguments, 2 ),
						$this->getReadDomainFromArguments( $node_arguments, 3 ),
						$this->getReadDomainFromArguments( $node_arguments, 4 ),
					],
					$this->getComment( $node ),
				];
			}
		}

		return $node;
	}

	protected function doLeaveNode( Node $node, Environment $env ): ?Node {
		return $node;
	}

	public function getPriority(): int {
		return 0;
	}

	private function getReadMessageFromArguments( Node $arguments, int $index ): ?string {
		if ( $arguments->hasNode( $index ) ) {
			$argument = $arguments->getNode( $index );
		} else {
			return null;
		}

		return $this->getReadMessageFromNode( $argument );
	}

	private function getReadMessageFromNode( Node $node ): ?string {
		if ( $node instanceof ConstantExpression ) {
			return $node->getAttribute( 'value' );
		}

		return null;
	}

	private function getReadDomainFromArguments( Node $arguments, int $index ): ?string {
		if ( $arguments->hasNode( $index ) ) {
			$argument = $arguments->getNode( $index );
		} else {
			return null;
		}

		return $this->getReadDomainFromNode( $argument );
	}

	private function getReadDomainFromNode( Node $node ): ?string {
		if ( $node instanceof ConstantExpression ) {
			return $node->getAttribute( 'value' );
		}

		return '';
	}

	/**
	 * Get the comment for a node.
	 *
	 * Will either return the comment on the same line as the function call, or the line before.
	 *
	 * @param Node $node
	 * @return void
	 */
	private function getComment( Node $node ) {
		$lineno = $node->getTemplateLine();
		// Look for a comment on the line before the function call.
		$comments = [];
		foreach ( $this->comments as $comment ) {
			if (
				isset( $comment['lineno'] )
				// Line before or same line
				&& in_array( $comment['lineno'], [ $lineno, $lineno - 1 ], true )
			) {
				$comments[] = $comment;
			}
		}
		return count( $comments ) ? $comments : null;
	}
}
