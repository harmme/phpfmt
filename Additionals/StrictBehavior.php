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

/**
 * From PHP-CS-Fixer
 */
final class StrictBehavior extends AdditionalPass {
	private static $functions = [
		'array_keys' => 3,
		'array_search' => 3,
		'base64_decode' => 2,
		'in_array' => 3,
		'mb_detect_encoding' => 3,
	];

	public function candidate($source, $foundTokens) {
		return true;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;

			if (T_STRING != $id) {
				$this->appendCode($text);
				continue;
			}

			$lcText = strtolower($text);
			$foundKeyword = &self::$functions[$lcText];
			if (!isset($foundKeyword)) {
				$this->appendCode($text);
				continue;
			}

			if ($this->leftUsefulTokenIs([T_DOUBLE_COLON, T_OBJECT_OPERATOR])) {
				$this->appendCode($text);
				continue;
			}

			if (!$this->rightUsefulTokenIs(ST_PARENTHESES_OPEN)) {
				$this->appendCode($text);
				continue;
			}

			$maxParams = $foundKeyword;

			$this->appendCode($text);
			$this->printUntil(ST_PARENTHESES_OPEN);
			$paramCount = $this->printAndStopAtEndOfParamBlock();

			if ($paramCount < $maxParams) {
				for (++$paramCount; $paramCount < $maxParams; ++$paramCount) {
					$this->appendCode(', null');
				}
				$this->appendCode(', true');
			}
		}

		return $this->code;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Activate strict option in array_search, base64_decode, in_array, array_keys, mb_detect_encoding. Danger! This pass leads to behavior change.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<?php
// From
array_search($needle, $haystack);
base64_decode($str);
in_array($needle, $haystack);

array_keys($arr);
mb_detect_encoding($arr);

array_keys($arr, [1]);
mb_detect_encoding($arr, 'UTF8');

// To
array_search($needle, $haystack, true);
base64_decode($str, true);
in_array($needle, $haystack, true);

array_keys($arr, null, true);
mb_detect_encoding($arr, null, true);

array_keys($arr, [1], true);
mb_detect_encoding($arr, 'UTF8', true);
?>
EOT;
	}
}