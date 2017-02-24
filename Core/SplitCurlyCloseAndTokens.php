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

class SplitCurlyCloseAndTokens extends FormatterPass {
	public function candidate($source, $foundTokens) {
		if (!isset($foundTokens[ST_CURLY_CLOSE])) {
			return false;
		}

		$this->tkns = token_get_all($source);
		while (list($index, $token) = each($this->tkns)) {
			list($id) = $this->getToken($token);
			$this->ptr = $index;

			if (ST_CURLY_CLOSE == $id && !$this->hasLnAfter()) {
				return true;
			}
		}

		return false;
	}

	public function format($source) {
		reset($this->tkns);
		$sizeofTkns = sizeof($this->tkns);

		$this->code = '';
		$blockStack = [];
		$touchedBlock = null;

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;

			switch ($id) {
			case T_DO:
			case T_ELSE:
			case T_ELSEIF:
			case T_FOR:
			case T_FOREACH:
			case T_FUNCTION:
			case T_IF:
			case T_SWITCH:
			case T_WHILE:
			case T_TRY:
			case T_CATCH:
				$touchedBlock = $id;
				$this->appendCode($text);
				break;

			case ST_SEMI_COLON:
			case ST_COLON:
				$touchedBlock = null;
				$this->appendCode($text);
				break;

			case T_CURLY_OPEN:
			case T_DOLLAR_OPEN_CURLY_BRACES:
				$this->appendCode($text);
				$this->printCurlyBlock();
				break;

			case ST_BRACKET_OPEN:
				$this->appendCode($text);
				$this->printBlock(ST_BRACKET_OPEN, ST_BRACKET_CLOSE);
				break;

			case ST_PARENTHESES_OPEN:
				$this->appendCode($text);
				$this->printBlock(ST_PARENTHESES_OPEN, ST_PARENTHESES_CLOSE);
				break;

			case ST_CURLY_OPEN:
				$this->appendCode($text);
				if (null !== $touchedBlock) {
					$blockStack[] = $touchedBlock;
					$touchedBlock = null;
					break;
				}
				$this->printCurlyBlock();
				break;

			case ST_CURLY_CLOSE:
				$this->appendCode($text);
				$poppedBlock = array_pop($blockStack);
				if (
					($this->ptr + 1) < $sizeofTkns &&
					(
						T_ELSE == $poppedBlock ||
						T_ELSEIF == $poppedBlock ||
						T_FOR == $poppedBlock ||
						T_FOREACH == $poppedBlock ||
						T_IF == $poppedBlock ||
						T_WHILE == $poppedBlock
					) &&
					!$this->hasLnAfter() &&
					!$this->rightTokenIs([
						ST_BRACKET_OPEN,
						ST_CURLY_CLOSE,
						ST_PARENTHESES_CLOSE,
						ST_PARENTHESES_OPEN,
						T_COMMENT,
						T_DOC_COMMENT,
						T_ELSE,
						T_ELSEIF,
						T_IF,
						T_OBJECT_OPERATOR,
						T_CLOSE_TAG,
					])
				) {
					$this->appendCode($this->newLine);
				}
				break;

			default:
				$this->appendCode($text);
				break;
			}
		}

		return $this->code;
	}
}
