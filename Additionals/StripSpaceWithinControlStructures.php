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

final class StripSpaceWithinControlStructures extends AdditionalPass {
	public function candidate($source, $foundTokens) {
		if (
			isset($foundTokens[T_CASE]) ||
			isset($foundTokens[T_DO]) ||
			isset($foundTokens[T_FOR]) ||
			isset($foundTokens[T_FOREACH]) ||
			isset($foundTokens[T_IF]) ||
			isset($foundTokens[T_SWITCH]) ||
			isset($foundTokens[T_WHILE])
		) {
			return true;
		}

		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';
		$touchedDo = false;

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;

			switch ($id) {
			case T_IF:
			case T_DO:
			case T_FOR:
			case T_FOREACH:
			case T_SWITCH:
				if (T_DO == $id) {
					$touchedDo = true;
				}
				$this->appendCode($text);
				$this->printUntil(ST_PARENTHESES_OPEN);
				$this->printBlock(ST_PARENTHESES_OPEN, ST_PARENTHESES_CLOSE);
				$this->printUntil(ST_CURLY_OPEN);

				if ($this->hasLnAfter()) {
					each($this->tkns);
					$this->appendCode($this->newLine);
					continue;
				}

				break;

			case T_WHILE:
				if (!$touchedDo && $this->leftUsefulTokenIs(ST_CURLY_CLOSE)) {
					$this->rtrimAndAppendCode($this->newLine);
				}
				$touchedDo = false;
				$this->appendCode($text);
				$this->printUntil(ST_PARENTHESES_OPEN);
				$this->printBlock(ST_PARENTHESES_OPEN, ST_PARENTHESES_CLOSE);

				if ($this->rightUsefulTokenIs(ST_CURLY_OPEN)) {
					$this->printUntil(ST_CURLY_OPEN);

					if ($this->hasLnAfter()) {
						each($this->tkns);
						$this->appendCode($this->newLine);
						continue;
					}
				}

				break;

			case T_CASE:
				$this->appendCode($text);
				$this->printUntil(ST_COLON);

				while (list($index, $token) = each($this->tkns)) {
					list($id, $text) = $this->getToken($token);
					$this->ptr = $index;
					if (T_WHITESPACE != $id) {
						break;
					}
					$this->appendCode($text);
				}
				$this->rtrimAndAppendCode($this->newLine . $text);
				break;

			case ST_CURLY_CLOSE:
				if ($this->hasLnBefore()) {
					$this->rtrimAndAppendCode($this->newLine . $text);
					continue;
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

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Strip empty lines within control structures.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<?php
// From
for ($a = 0; $a < 10; $a++){

	if($a){

		// do something
	}

}
// To
for ($a = 0; $a < 10; $a++){
	if($a){
		// do something
	}
}
?>
EOT;
	}
}
