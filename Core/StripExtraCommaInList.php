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

final class StripExtraCommaInList extends FormatterPass {
	const EMPTY_LIST = 'ST_EMPTY_LIST';

	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[T_LIST])) {
			return true;
		}

		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);

		$contextStack = [];
		$touchedListArrayString = false;

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_STRING:
			case T_ARRAY:
			case T_LIST:
				$touchedListArrayString = true;
				if ($this->rightTokenIs(ST_PARENTHESES_OPEN)) {
					$contextStack[] = $id;
				}
				break;

			case ST_PARENTHESES_OPEN:
				if (isset($contextStack[0]) && T_LIST == end($contextStack) && $this->rightTokenIs(ST_PARENTHESES_CLOSE)) {
					$contextStack[sizeof($contextStack) - 1] = self::EMPTY_LIST;
				} elseif (!$touchedListArrayString) {
					$contextStack[] = ST_PARENTHESES_OPEN;
				}
				break;

			case ST_PARENTHESES_CLOSE:
				if (isset($contextStack[0])) {
					if (T_LIST == end($contextStack) && $this->leftUsefulTokenIs(ST_COMMA)) {
						$prevTokenIdx = $this->leftUsefulTokenIdx();
						$this->tkns[$prevTokenIdx] = null;
					}
					array_pop($contextStack);
				}
				break;

			default:
				$touchedListArrayString = false;
				break;
			}
			$this->tkns[$this->ptr] = [$id, $text];
		}
		return $this->renderLight();
	}
}