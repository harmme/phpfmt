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

class ClassToSelf extends AdditionalPass {
	const PLACEHOLDER = 'self';

	public function candidate($source, $foundTokens) {
		if (
			isset($foundTokens[T_CLASS]) ||
			isset($foundTokens[T_INTERFACE]) ||
			isset($foundTokens[T_TRAIT])
		) {
			return true;
		}

		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';
		$tknsLen = sizeof($this->tkns);

		$touchedDoubleColon = false;
		for ($ptr = 0; $ptr < $tknsLen; ++$ptr) {
			$token = $this->tkns[$ptr];
			list($id) = $this->getToken($token);

			if (T_DOUBLE_COLON == $id) {
				$touchedDoubleColon = true;
			}
			if ($touchedDoubleColon && T_CLASS == $id) {
				$touchedDoubleColon = false;
				break;
			}
			if (
				T_CLASS == $id ||
				T_INTERFACE == $id ||
				T_TRAIT == $id
			) {
				$this->refWalkUsefulUntil($this->tkns, $ptr, T_STRING);
				list(, $name) = $this->getToken($this->tkns[$ptr]);

				$this->refWalkUsefulUntil($this->tkns, $ptr, ST_CURLY_OPEN);
				$start = $ptr;
				$this->refWalkCurlyBlock($this->tkns, $ptr);
				$end = ++$ptr;

				$this->convertToPlaceholder($name, $start, $end);
				break;
			}
		}

		return $this->render();
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return '"self" is preferred within class, trait or interface.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<?php
// From
class A {
	const constant = 1;
	function b(){
		A::constant;
	}
}

// To
class A {
	const constant = 1;
	function b(){
		self::constant;
	}
}
?>
EOT;
	}

	private function convertToPlaceholder($name, $start, $end) {
		for ($i = $start; $i < $end; ++$i) {
			list($id, $text) = $this->getToken($this->tkns[$i]);

			if (T_FUNCTION == $id && $this->rightTokenSubsetIsAtIdx($this->tkns, $i, [ST_REFERENCE, ST_PARENTHESES_OPEN])) {
				$this->refWalkUsefulUntil($this->tkns, $i, ST_CURLY_OPEN);
				$this->refWalkCurlyBlock($this->tkns, $i);
				continue;
			}

			if (
				!(T_STRING == $id && strtolower($text) == strtolower($name)) ||
				$this->leftTokenSubsetIsAtIdx($this->tkns, $i, T_NS_SEPARATOR) ||
				$this->rightTokenSubsetIsAtIdx($this->tkns, $i, T_NS_SEPARATOR)
			) {
				continue;
			}

			if (
				$this->leftTokenSubsetIsAtIdx($this->tkns, $i, [T_INSTANCEOF, T_NEW]) ||
				$this->rightTokenSubsetIsAtIdx($this->tkns, $i, T_DOUBLE_COLON)
			) {
				$this->tkns[$i] = [T_STRING, self::PLACEHOLDER];
			}
		}
	}
}
