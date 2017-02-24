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

final class LongArray extends AdditionalPass {
	const EMPTY_ARRAY = 'ST_EMPTY_ARRAY';

	const ST_SHORT_ARRAY_OPEN = 'SHORT_ARRAY_OPEN';

	public function candidate($source, $foundTokens) {
		return true;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);

		$contextStack = [];
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case ST_BRACKET_OPEN:
				$found = ST_BRACKET_OPEN;
				if ($this->isShortArray()) {
					$found = self::ST_SHORT_ARRAY_OPEN;
					$id = self::ST_SHORT_ARRAY_OPEN;
					$text = 'array(';
				}
				$contextStack[] = $found;
				break;
			case ST_BRACKET_CLOSE:
				if (isset($contextStack[0]) && !$this->leftTokenIs(ST_BRACKET_OPEN)) {
					if (self::ST_SHORT_ARRAY_OPEN == end($contextStack)) {
						$id = ')';
						$text = ')';
					}
					array_pop($contextStack);
				}
				break;
			case T_STRING:
				if ($this->rightTokenIs(ST_PARENTHESES_OPEN)) {
					$contextStack[] = T_STRING;
				}
				break;
			case T_ARRAY:
				if ($this->rightTokenIs(ST_PARENTHESES_OPEN)) {
					$contextStack[] = T_ARRAY;
				}
				break;
			case ST_PARENTHESES_OPEN:
				if (isset($contextStack[0]) && T_ARRAY == end($contextStack) && $this->rightTokenIs(ST_PARENTHESES_CLOSE)) {
					$contextStack[sizeof($contextStack) - 1] = self::EMPTY_ARRAY;
				} elseif (!$this->leftTokenIs([T_ARRAY, T_STRING])) {
					$contextStack[] = ST_PARENTHESES_OPEN;
				}
				break;
			case ST_PARENTHESES_CLOSE:
				if (isset($contextStack[0])) {
					array_pop($contextStack);
				}
				break;
			}
			$this->tkns[$this->ptr] = [$id, $text];
		}

		return $this->renderLight();
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Convert short to long arrays.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<?php
// From
$a = [$a, $b];

// To
$b = array($b, $c);
?>
EOT;
	}
}