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

final class PSR2CurlyOpenNextLine extends FormatterPass {
	public function candidate($source, $foundTokens) {
		return true;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_START_HEREDOC:
				$this->appendCode($text);
				$this->printUntil(T_END_HEREDOC);
				break;
			case ST_QUOTE:
				$this->appendCode($text);
				$this->printUntilTheEndOfString();
				break;
			case T_INTERFACE:
			case T_TRAIT:
			case T_CLASS:
				$this->appendCode($text);
				if ($this->leftUsefulTokenIs(T_DOUBLE_COLON)) {
					break;
				}
				while (list($index, $token) = each($this->tkns)) {
					list($id, $text) = $this->getToken($token);
					$this->ptr = $index;
					if (ST_CURLY_OPEN === $id) {
						$this->appendCode($this->getCrlfIndent());
						prev($this->tkns);
						break;
					}
					$this->appendCode($text);
				}
				break;
			case T_FUNCTION:
				if (!$this->leftTokenIs([T_DOUBLE_ARROW, T_RETURN, ST_EQUAL, ST_PARENTHESES_OPEN, ST_COMMA]) && $this->rightUsefulTokenIs([T_STRING, ST_REFERENCE])) {
					$this->appendCode($text);
					$touchedLn = false;
					while (list($index, $token) = each($this->tkns)) {
						list($id, $text) = $this->getToken($token);
						$this->ptr = $index;
						if (T_WHITESPACE == $id && $this->hasLn($text)) {
							$touchedLn = true;
						}
						if (ST_CURLY_OPEN === $id && !$touchedLn) {
							$this->appendCode($this->getCrlfIndent());
							prev($this->tkns);
							break;
						} elseif (ST_CURLY_OPEN === $id) {
							prev($this->tkns);
							break;
						} elseif (ST_SEMI_COLON === $id) {
							$this->appendCode($text);
							break;
						}
						$this->appendCode($text);
					}
					break;
				}
				$this->appendCode($text);
				break;
			case ST_CURLY_OPEN:
				$this->appendCode($text);
				$this->setIndent(+1);
				break;
			case ST_CURLY_CLOSE:
				$this->setIndent(-1);
				$this->appendCode($text);
				break;
			default:
				$this->appendCode($text);
				break;
			}
		}
		return $this->code;
	}
}
