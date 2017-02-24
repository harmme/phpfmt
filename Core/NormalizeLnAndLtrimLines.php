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

final class NormalizeLnAndLtrimLines extends FormatterPass {
	public function candidate($source, $foundTokens) {
		return true;
	}

	public function format($source) {
		$source = str_replace(["\r\n", "\n\r", "\r", "\n"], $this->newLine, $source);
		$source = preg_replace('/\h+$/mu', '', $source);

		$this->tkns = token_get_all($source);
		$this->code = '';
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_INLINE_HTML:
				$this->appendCode($text);
				break;
			case ST_QUOTE:
				$this->appendCode($text);
				$this->printUntilTheEndOfString();
				break;
			case T_START_HEREDOC:
				$this->appendCode($text);
				$this->printUntil(T_END_HEREDOC);
				break;

			case T_COMMENT:
			case T_DOC_COMMENT:
				list($prevId, $prevText) = $this->inspectToken(-1);

				if (T_WHITESPACE === $prevId && ("\n" === $prevText || "\n\n" == substr($prevText, -2, 2))) {
					$this->appendCode(LeftAlignComment::NON_INDENTABLE_COMMENT);
				}

				$lines = explode($this->newLine, $text);
				$newText = '';
				foreach ($lines as $v) {
					$v = ltrim($v);
					if ('*' === substr($v, 0, 1)) {
						$v = ' ' . $v;
					}
					$newText .= $this->newLine . $v;
				}

				$this->appendCode(ltrim($newText));
				break;

			case T_CONSTANT_ENCAPSED_STRING:
				$this->appendCode($text);
				break;
			default:
				if ($this->hasLn($text)) {
					$trailingNewLine = $this->substrCountTrailing($text, $this->newLine);
					if ($trailingNewLine > 0) {
						$text = trim($text) . str_repeat($this->newLine, $trailingNewLine);
					}
				}
				$this->appendCode($text);
				break;
			}
		}

		return $this->code;
	}
}
