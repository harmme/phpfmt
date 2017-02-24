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

final class AlignTypehint extends AdditionalPass {
	const ALIGNABLE_TYPEHINT = "\x2 TYPEHINT%d \x3";

	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[T_FUNCTION])) {
			return true;
		}
		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		$contextCounter = 0;

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_FUNCTION:
				$this->appendCode($text);
				$this->printUntil(ST_PARENTHESES_OPEN);
				do {
					list($id, $text) = $this->printAndStopAt([T_VARIABLE, ST_PARENTHESES_OPEN, ST_PARENTHESES_CLOSE]);
					if (ST_PARENTHESES_OPEN == $id) {
						$this->appendCode($text);
						$this->printBlock(ST_PARENTHESES_OPEN, ST_PARENTHESES_CLOSE);
						continue;
					}
					if (ST_PARENTHESES_CLOSE == $id) {
						$this->appendCode($text);
						break;
					}
					$this->appendCode(sprintf(self::ALIGNABLE_TYPEHINT, $contextCounter) . $text);
				} while (true);
				++$contextCounter;
				break;

			default:
				$this->appendCode($text);
				break;
			}
		}

		$this->alignPlaceholders(self::ALIGNABLE_TYPEHINT, $contextCounter);

		return $this->code;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Vertically align function type hints.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<?php
//From:
function a(
	TypeA $a,
	TypeBB $bb,
	TypeCCC $ccc = array(),
	TypeDDDD $dddd,
	TypeEEEEE $eeeee
){
	noop();
}


//To:
function a(
	TypeA     $a,
	TypeBB    $bb,
	TypeCCC   $ccc = array(),
	TypeDDDD  $dddd,
	TypeEEEEE $eeeee
){
	noop();
}


?>
EOT;
	}
}
