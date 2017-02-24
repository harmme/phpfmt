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

final class AlignDoubleSlashComments extends AdditionalPass {
	const ALIGNABLE_COMMENT = "\x2 COMMENT%d \x3";

	/**
	 * @codeCoverageIgnore
	 */
	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[T_COMMENT])) {
			return true;
		}
		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		// It injects placeholders before single line comments, in order
		// to align chunks of them later.
		$contextCounter = 0;
		$touchedNonAlignableComment = false;

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_COMMENT:
				if (LeftAlignComment::NON_INDENTABLE_COMMENT == $text) {
					$touchedNonAlignableComment = true;
					$this->appendCode($text);
					continue;
				}

				$prefix = '';
				if (substr($text, 0, 2) == '//' && !$touchedNonAlignableComment) {
					$prefix = sprintf(self::ALIGNABLE_COMMENT, $contextCounter);
				}
				$this->appendCode($prefix . $text);

				break;

			case T_WHITESPACE:
				if ($this->hasLn($text)) {
					++$contextCounter;
				}
				$this->appendCode($text);
				break;

			default:
				$touchedNonAlignableComment = false;
				$this->appendCode($text);
				break;
			}
		}

		$this->alignPlaceholders(self::ALIGNABLE_COMMENT, $contextCounter);

		return $this->code;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Vertically align "//" comments.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<?php
//From:
$a = 1; // Comment 1
$bb = 22;  // Comment 2
$ccc = 333;  // Comment 3

//To:
$a = 1;      // Comment 1
$bb = 22;    // Comment 2
$ccc = 333;  // Comment 3

?>
EOT;
	}
}