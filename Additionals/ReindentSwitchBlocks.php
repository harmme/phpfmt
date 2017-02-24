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

final class ReindentSwitchBlocks extends AdditionalPass {
	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[T_SWITCH])) {
			return true;
		}

		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		$touchedSwitch = false;
		$foundStack = [];

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;

			switch ($id) {
			case ST_QUOTE:
				$this->appendCode($text);
				$this->printUntilTheEndOfString();
				break;
			case T_CLOSE_TAG:
				$this->appendCode($text);
				$this->printUntil(T_OPEN_TAG);
				break;
			case T_START_HEREDOC:
				$this->appendCode($text);
				$this->printUntil(T_END_HEREDOC);
				break;
			case T_CONSTANT_ENCAPSED_STRING:
				$this->appendCode($text);
				break;

			case T_SWITCH:
				$touchedSwitch = true;
				$this->appendCode($text);
				break;

			case T_DOLLAR_OPEN_CURLY_BRACES:
			case T_CURLY_OPEN:
			case ST_CURLY_OPEN:
				$indentToken = $id;
				$this->appendCode($text);
				if ($touchedSwitch) {
					$touchedSwitch = false;
					$indentToken = T_SWITCH;
					$this->setIndent(+1);
				}
				$foundStack[] = $indentToken;
				break;

			case ST_CURLY_CLOSE:
				$poppedID = array_pop($foundStack);
				if (T_SWITCH === $poppedID) {
					$this->setIndent(-1);
				}
				$this->appendCode($text);
				break;

			default:
				$hasLn = $this->hasLn($text);
				if ($hasLn) {
					$poppedID = end($foundStack);
					if (
						T_SWITCH == $poppedID &&
						$this->rightTokenIs(ST_CURLY_CLOSE)
					) {
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

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Reindent one level deeper the content of switch blocks.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<EOT
<?php
// From
switch ($a) {
case 1:
	echo 'a';
}

// To
switch ($a) {
	case 1:
		echo 'a';
}
EOT;
	}
}