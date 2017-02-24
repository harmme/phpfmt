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

class AliasToMaster extends AdditionalPass {
	protected static $aliasList = [
		'chop' => 'rtrim',
		'close' => 'closedir',
		'die' => 'exit',
		'doubleval' => 'floatval',
		'fputs' => 'fwrite',
		'ini_alter' => 'ini_set',
		'is_double' => 'is_float',
		'is_integer' => 'is_int',
		'is_long' => 'is_int',
		'is_real' => 'is_float',
		'is_writeable' => 'is_writable',
		'join' => 'implode',
		'key_exists' => 'array_key_exists',
		'magic_quotes_runtime' => 'set_magic_quotes_runtime',
		'pos' => 'current',
		'rewind' => 'rewinddir',
		'show_source' => 'highlight_file',
		'sizeof' => 'count',
		'strchr' => 'strstr',
	];

	private $touchedEmptyNs = false;

	public function candidate($source, $foundTokens) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			$this->checkIfEmptyNS($id);
			switch ($id) {
			case T_STRING:
			case T_EXIT:
				if (isset(static::$aliasList[strtolower($text)])) {
					prev($this->tkns);
					return true;
				}
			}
			$this->appendCode($text);
		}
		return false;
	}

	public function format($source) {
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			$this->checkIfEmptyNS($id);
			if (
				(T_STRING == $id || T_EXIT == $id) &&
				isset(static::$aliasList[strtolower($text)]) &&
				(
					!(
						$this->leftUsefulTokenIs([
							T_NEW,
							T_NS_SEPARATOR,
							T_STRING,
							T_DOUBLE_COLON,
							T_OBJECT_OPERATOR,
							T_FUNCTION,
						]) ||
						$this->rightUsefulTokenIs([
							T_NS_SEPARATOR,
							T_DOUBLE_COLON,
						])
					)
					||
					(
						$this->leftUsefulTokenIs([
							T_NS_SEPARATOR,
						]) &&
						$this->touchedEmptyNs
					)
				)
			) {
				$this->appendCode(static::$aliasList[strtolower($text)]);
				continue;
			}
			$this->appendCode($text);
		}

		return $this->code;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Replace function aliases to their masters - only basic syntax alias.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<?php
$a = join(',', $arr);
die("done");

$a = implode(',', $arr);
exit("done");
?>
EOT;
	}

	private function checkIfEmptyNS($id) {
		if (T_NS_SEPARATOR != $id) {
			return;
		}

		$this->touchedEmptyNs = !$this->leftUsefulTokenIs(T_STRING);
	}
}
