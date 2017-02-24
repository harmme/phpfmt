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

final class LeftAlignComment extends FormatterPass {
	const NON_INDENTABLE_COMMENT = "/*\x2 COMMENT \x3*/";

	public function candidate($source, $foundTokens) {
		if (
			isset($foundTokens[T_COMMENT]) ||
			isset($foundTokens[T_DOC_COMMENT])
		) {
			return true;
		}

		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';
		$touchedNonIndentableComment = false;

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			if (self::NON_INDENTABLE_COMMENT === $text) {
				$touchedNonIndentableComment = true;
				continue;
			}
			switch ($id) {
			case T_COMMENT:
			case T_DOC_COMMENT:
				if ($touchedNonIndentableComment) {
					$touchedNonIndentableComment = false;
					$lines = explode($this->newLine, $text);
					$lines = array_map(function ($v) {
						$v = ltrim($v);
						if ('*' === substr($v, 0, 1)) {
							$v = ' ' . $v;
						}
						return $v;
					}, $lines);
					$this->appendCode(implode($this->newLine, $lines));
					break;
				}
				$this->appendCode($text);
				break;

			case T_WHITESPACE:
				list(, $nextText) = $this->inspectToken(1);
				if (self::NON_INDENTABLE_COMMENT === $nextText && substr_count($text, "\n") >= 2) {
					$text = substr($text, 0, strrpos($text, "\n") + 1);
					$this->appendCode($text);
					break;
				} elseif (self::NON_INDENTABLE_COMMENT === $nextText && substr_count($text, "\n") === 1) {
					$text = substr($text, 0, strrpos($text, "\n") + 1);
					$this->appendCode($text);
					break;
				}
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
