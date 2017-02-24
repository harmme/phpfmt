<?php
# Copyright (c) 2015, phpfmt and its authors
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
#
# 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
#
# 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
#
# 3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
namespace contal\fmt;

final class ReindentEqual extends FormatterPass {
	public function candidate($source, $foundTokens) {
		return true;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		for ($index = sizeof($this->tkns) - 1; 0 <= $index; --$index) {
			$token = $this->tkns[$index];
			list($id) = $this->getToken($token);
			$this->ptr = $index;

			if (ST_SEMI_COLON == $id) {
				--$index;
				$this->scanUntilEqual($index);
			}
		}

		return $this->render($this->tkns);
	}

	private function scanUntilEqual($index) {
		for ($index; 0 <= $index; --$index) {
			$token = $this->tkns[$index];
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;

			switch ($id) {
			case ST_QUOTE:
				$this->refWalkUsefulUntilReverse($this->tkns, $index, ST_QUOTE);
				break;

			case T_OPEN_TAG:
				$this->refWalkUsefulUntilReverse($this->tkns, $index, T_CLOSE_TAG);
				break;

			case T_END_HEREDOC:
				$this->refWalkUsefulUntilReverse($this->tkns, $index, T_START_HEREDOC);
				break;

			case ST_CURLY_CLOSE:
				$this->refWalkCurlyBlockReverse($this->tkns, $index);
				break;

			case ST_PARENTHESES_CLOSE:
				$this->refWalkBlockReverse($this->tkns, $index, ST_PARENTHESES_OPEN, ST_PARENTHESES_CLOSE);
				break;

			case ST_BRACKET_CLOSE:
				$this->refWalkBlockReverse($this->tkns, $index, ST_BRACKET_OPEN, ST_BRACKET_CLOSE);
				break;

			case T_STRING:
				if ($this->rightUsefulTokenIs(ST_PARENTHESES_OPEN) && !$this->leftUsefulTokenIs(ST_EQUAL)) {
					return;
				}

			case ST_CONCAT:
			case ST_DIVIDE:
			case ST_MINUS:
			case ST_PLUS:
			case ST_TIMES:
			case T_BOOLEAN_AND:
			case T_BOOLEAN_OR:
			case T_CONSTANT_ENCAPSED_STRING:
			case T_DNUMBER:
			case T_IS_EQUAL:
			case T_IS_GREATER_OR_EQUAL:
			case T_IS_IDENTICAL:
			case T_IS_NOT_EQUAL:
			case T_IS_NOT_IDENTICAL:
			case T_IS_SMALLER_OR_EQUAL:
			case T_IS_SMALLER_OR_EQUAL:
			case T_LNUMBER:
			case T_LOGICAL_AND:
			case T_LOGICAL_OR:
			case T_LOGICAL_XOR:
			case T_POW:
			case T_SPACESHIP:
			case T_VARIABLE:
				break;

			case T_WHITESPACE:
				if (
					$this->hasLn($text)
					&&
					!
					(
						$this->rightUsefulTokenIs([ST_SEMI_COLON])
						||
						$this->leftUsefulTokenIs([
							ST_BRACKET_OPEN,
							ST_COLON,
							ST_CURLY_CLOSE,
							ST_CURLY_OPEN,
							ST_PARENTHESES_OPEN,
							ST_SEMI_COLON,
							T_END_HEREDOC,
							T_OBJECT_OPERATOR,
							T_OPEN_TAG,
						])
						||
						$this->leftTokenIs([
							T_COMMENT,
							T_DOC_COMMENT,
						])
					)
				) {
					$text .= $this->indentChar;
					$this->tkns[$index] = [$id, $text];
				}
				break;

			default:
				return;
			}
		}
	}
}
