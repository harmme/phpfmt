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

final class AddMissingCurlyBraces extends FormatterPass {
	public function candidate($source, $foundTokens) {
		if (
			isset($foundTokens[T_ELSE]) ||
			isset($foundTokens[T_WHILE]) ||
			isset($foundTokens[T_FOR]) ||
			isset($foundTokens[T_FOREACH]) ||
			isset($foundTokens[T_ELSEIF]) ||
			isset($foundTokens[T_IF])
		) {
			return true;
		}

		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';
		// Scans from the end to the beginning looking for close curly
		// braces, whenever one is found ($touchedCurlyClose) skips to
		// the beginning of the block, otherwise adds the missing curly
		// braces.
		$touchedCurlyClose = false;
		$hasCurlyOnLeft = false; // Deals with do{}while blocks;

		for ($index = sizeof($this->tkns) - 1; 0 <= $index; --$index) {
			$token = $this->tkns[$index];
			list($id) = $this->getToken($token);
			$this->ptr = $index;

			$hasCurlyOnLeft = false;

			switch ($id) {
			case T_ELSE:
				if ($this->rightTokenSubsetIsAtIdx($this->tkns, $this->ptr, [ST_CURLY_OPEN, ST_COLON, T_IF], $this->ignoreFutileTokens)) {
					break;
				}
				$this->insertCurlyBraces();
				break;

			case ST_CURLY_CLOSE:
				$touchedCurlyClose = true;
				break;

			case T_WHILE:
				if ($touchedCurlyClose) {
					$touchedCurlyClose = false;
					$hasCurlyOnLeft = true;
				}

			case T_FOR:
			case T_FOREACH:
			case T_ELSEIF:
			case T_IF:
				$this->refWalkUsefulUntil($this->tkns, $this->ptr, ST_PARENTHESES_OPEN);
				$this->refWalkBlock($this->tkns, $this->ptr, ST_PARENTHESES_OPEN, ST_PARENTHESES_CLOSE);
				if (
					($hasCurlyOnLeft && $this->rightTokenSubsetIsAtIdx($this->tkns, $this->ptr, [ST_SEMI_COLON], $this->ignoreFutileTokens)) ||
					$this->rightTokenSubsetIsAtIdx($this->tkns, $this->ptr, [ST_CURLY_OPEN, ST_COLON, ST_SEMI_COLON], $this->ignoreFutileTokens)
				) {
					break;
				}
				$this->insertCurlyBraces();
				break;
			}
		}
		return $this->render($this->tkns);
	}

	private function addSemicolon() {
		if (T_CLOSE_TAG == $this->tkns[$this->ptr][0]) {
			return $this->refInsert($this->tkns, $this->ptr, [ST_SEMI_COLON, ST_SEMI_COLON]);
		}
		++$this->ptr;
	}

	private function insertCurlyBraces() {
		$this->refSkipIfTokenIsAny($this->tkns, $this->ptr, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT]);
		$this->refInsert($this->tkns, $this->ptr, [ST_CURLY_OPEN, ST_CURLY_OPEN]);
		$this->refInsert($this->tkns, $this->ptr, [T_WHITESPACE, $this->newLine]);
		$this->refSkipBlocks($this->tkns, $this->ptr);
		$this->addSemicolon();
		$this->refInsert($this->tkns, $this->ptr, [T_WHITESPACE, $this->newLine]);
		$this->refInsert($this->tkns, $this->ptr, [ST_CURLY_CLOSE, ST_CURLY_CLOSE]);
		$this->refInsert($this->tkns, $this->ptr, [T_WHITESPACE, $this->newLine]);
	}
}
