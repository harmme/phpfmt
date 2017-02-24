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

final class EliminateDuplicatedEmptyLines extends FormatterPass {
	const EMPTY_LINE = "\x2 EMPTYLINE \x3";

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
			case T_WHITESPACE:
			case T_COMMENT:
			case T_OPEN_TAG:
				if ($this->hasLn($text) || (T_COMMENT == $id && '//' == substr($text, 0, 2))) {
					$text = str_replace($this->newLine, self::EMPTY_LINE . $this->newLine, $text);
				}

				$this->appendCode($text);
				break;
			default:
				$this->appendCode($text);
				break;
			}
		}

		$ret = $this->code;
		$count = 0;
		do {
			$ret = str_replace(
				self::EMPTY_LINE . $this->newLine . self::EMPTY_LINE . $this->newLine . self::EMPTY_LINE . $this->newLine,
				self::EMPTY_LINE . $this->newLine . self::EMPTY_LINE . $this->newLine,
				$ret,
				$count
			);
		} while ($count > 0);
		$ret = str_replace(self::EMPTY_LINE, '', $ret);

		list($id) = $this->getToken(end($this->tkns));
		if (T_WHITESPACE === $id) {
			$ret = rtrim($ret) . $this->newLine;
		}

		return $ret;
	}
}