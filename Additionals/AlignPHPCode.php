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

final class AlignPHPCode extends AdditionalPass {
	const PLACEHOLDER_STRING = "\x2 CONSTANT_STRING_%d \x3";

	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[T_INLINE_HTML])) {
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
			switch ($id) {
			case T_OPEN_TAG:
				list(, $prevText) = $this->getToken($this->leftToken());

				$prevSpace = substr(strrchr($prevText, $this->newLine), 1);
				$skipPadLeft = false;
				if (rtrim($prevSpace) == $prevSpace) {
					$skipPadLeft = true;
				}
				$prevSpace = preg_replace('/[^\s\t]/', ' ', $prevSpace);

				$placeholders = [];
				$strings = [];
				$stack = $text;
				while (list($index, $token) = each($this->tkns)) {
					list($id, $text) = $this->getToken($token);
					$this->ptr = $index;

					if (T_CONSTANT_ENCAPSED_STRING == $id || T_ENCAPSED_AND_WHITESPACE == $id) {
						$strings[] = $text;
						$text = sprintf(self::PLACEHOLDER_STRING, $this->ptr);
						$placeholders[] = $text;
					}
					$stack .= $text;

					if (T_CLOSE_TAG == $id) {
						break;
					}
				}

				$tmp = explode($this->newLine, $stack);
				$lastLine = sizeof($tmp) - 2;
				foreach ($tmp as $idx => $line) {
					$before = $prevSpace;
					if ('' === trim($line)) {
						continue;
					}
					$indent = '';
					if (0 != $idx && $idx < $lastLine) {
						$indent = $this->indentChar;
					}
					if ($skipPadLeft) {
						$before = '';
						$skipPadLeft = false;
					}
					$tmp[$idx] = $before . $indent . $line;
				}

				$stack = implode($this->newLine, $tmp);
				$stack = str_replace($placeholders, $strings, $stack);

				$this->code = rtrim($this->code, " \t");
				$this->appendCode($stack);
				break;

			default:
				$this->appendCode($text);
				break;
			}
		}

		return $this->code;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Align PHP code within HTML block.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<div>
	<?php
		echo $a;
	?>
</div>
EOT;
	}
}
