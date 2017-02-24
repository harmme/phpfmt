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

final class WordWrap extends AdditionalPass {
	const ALIGNABLE_WORDWRAP = "\x2 WORDWRAP \x3";

	private static $length = 80;

	private static $tabSizeInSpace = 8;

	public function candidate($source, $foundTokens) {
		return true;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		$currentLineLength = 0;
		$detectedTab = false;
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;

			$originalText = $text;
			if (T_WHITESPACE == $id) {
				if (!$detectedTab && false !== strpos($text, "\t")) {
					$detectedTab = true;
				}
				$text = str_replace(
					$this->indentChar,
					str_repeat(' ', self::$tabSizeInSpace),
					$text
				);
			}
			$textLen = strlen($text);

			$currentLineLength += $textLen;
			if ($this->hasLn($text)) {
				$currentLineLength = $textLen - strrpos($text, $this->newLine);
			}

			if ($currentLineLength > self::$length) {
				$currentLineLength = $textLen - strrpos($text, $this->newLine);
				$this->appendCode($this->newLine . self::ALIGNABLE_WORDWRAP);
			}

			$this->appendCode($originalText);
		}

		if (false === strpos($this->code, self::ALIGNABLE_WORDWRAP)) {
			return $this->code;
		}

		$lines = explode($this->newLine, $this->code);
		foreach ($lines as $idx => $line) {
			if (false !== strpos($line, self::ALIGNABLE_WORDWRAP)) {
				$line = str_replace(self::ALIGNABLE_WORDWRAP, '', $line);
				$line = str_pad($line, self::$length, ' ', STR_PAD_LEFT);
				if ($detectedTab) {
					$line = preg_replace('/\G {' . self::$tabSizeInSpace . '}/', "\t", $line);
				}
				$lines[$idx] = $line;
			}
		}

		return implode($this->newLine, $lines);
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Word wrap at 80 columns.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return '';
	}
}