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

final class ReindentColonBlocks extends FormatterPass {
	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[T_ENDIF]) || isset($foundTokens[T_ENDWHILE]) || isset($foundTokens[T_ENDFOREACH]) || isset($foundTokens[T_ENDFOR])) {
			return true;
		}

		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;

			if (
				T_ENDIF == $id || T_ELSEIF == $id ||
				T_ENDFOR == $id || T_ENDFOREACH == $id || T_ENDWHILE == $id ||
				(T_ELSE == $id && !$this->rightUsefulTokenIs(ST_CURLY_OPEN))
			) {
				$this->setIndent(-1);
			}
			switch ($id) {
			case T_ENDFOR:
			case T_ENDFOREACH:
			case T_ENDWHILE:
			case T_ENDIF:
				$this->appendCode($text);
				break;

			case T_ELSE:
				$this->appendCode($text);
				$this->indentBlock();
				break;

			case T_FOR:
			case T_FOREACH:
			case T_WHILE:
			case T_ELSEIF:
			case T_IF:
				$this->appendCode($text);
				$this->printUntil(ST_PARENTHESES_OPEN);
				$this->printBlock(ST_PARENTHESES_OPEN, ST_PARENTHESES_CLOSE);
				$this->indentBlock();
				break;

			case T_START_HEREDOC:
				$this->appendCode($text);
				$this->printUntil(T_END_HEREDOC);
				break;

			default:
				$hasLn = $this->hasLn($text);
				if ($hasLn) {
					if ($this->rightTokenIs([T_ENDIF, T_ELSE, T_ELSEIF, T_ENDFOR, T_ENDFOREACH, T_ENDWHILE])) {
						$this->setIndent(-1);
						$text = str_replace($this->newLine, $this->newLine . $this->getIndent(), $text);
						$this->setIndent(+1);
					} else {
						$text = str_replace($this->newLine, $this->newLine . $this->getIndent(), $text);
					}
				}
				$this->appendCode($text);
				break;
			}
		}
		return $this->code;
	}

	private function indentBlock() {
		$foundId = $this->printUntilAny([ST_COLON, ST_SEMI_COLON, ST_CURLY_OPEN]);
		if (ST_COLON === $foundId && !$this->rightTokenIs([T_CLOSE_TAG])) {
			$this->setIndent(+1);
		}
	}
}