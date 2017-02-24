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

final class AlignEquals extends AdditionalPass {
	const ALIGNABLE_EQUAL = "\x2 EQUAL%d \x3";

	const OPEN_TAG = "<?php /*\x2 EQUAL OPEN TAG\x3*/";

	public function candidate($source, $foundTokens) {
		return true;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		// It skips parentheses and bracket blocks, and aligns '='
		// everywhere else.
		$parenCount = 0;
		$bracketCount = 0;
		$contextCounter = 0;

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_FUNCTION:
				++$contextCounter;
				$this->appendCode($text);
				break;

			case ST_CURLY_OPEN:
				$this->appendCode($text);
				$block = $this->walkAndAccumulateCurlyBlock($this->tkns);
				$aligner = new self();
				$this->appendCode(
					str_replace(self::OPEN_TAG, '', $aligner->format(self::OPEN_TAG . $block))
				);
				break;

			case ST_PARENTHESES_OPEN:
				++$parenCount;
				$this->appendCode($text);
				break;
			case ST_PARENTHESES_CLOSE:
				--$parenCount;
				$this->appendCode($text);
				break;
			case ST_BRACKET_OPEN:
				++$bracketCount;
				$this->appendCode($text);
				break;
			case ST_BRACKET_CLOSE:
				--$bracketCount;
				$this->appendCode($text);
				break;
			case ST_EQUAL:
				if (!$parenCount && !$bracketCount) {
					$this->appendCode(sprintf(self::ALIGNABLE_EQUAL, $contextCounter) . $text);
					break;
				}

			default:
				$this->appendCode($text);
				break;
			}
		}

		$this->alignPlaceholders(self::ALIGNABLE_EQUAL, $contextCounter);

		return $this->code;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Vertically align "=".';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<?php
$a = 1;
$bb = 22;
$ccc = 333;

$a   = 1;
$bb  = 22;
$ccc = 333;

?>
EOT;
	}
}