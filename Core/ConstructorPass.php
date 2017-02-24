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

final class ConstructorPass extends FormatterPass {
	const TYPE_CAMEL_CASE = 'camel';

	const TYPE_GOLANG = 'golang';

	const TYPE_SNAKE_CASE = 'snake';

	/**
	 * @var string
	 */
	private $type;

	public function __construct($type = self::TYPE_CAMEL_CASE) {
		$this->type = self::TYPE_CAMEL_CASE;
		if (self::TYPE_CAMEL_CASE == $type || self::TYPE_SNAKE_CASE == $type || self::TYPE_GOLANG == $type) {
			$this->type = $type;
		}
	}

	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[T_CLASS])) {
			return true;
		}
		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		// It scans for a class, and tracks the attributes, methods,
		// visibility modifiers and ensures that the constructor is
		// actually compliant with the behavior of PHP >= 5.
		$classAttributes = [];
		$functionList = [];
		$touchedVisibility = false;
		$touchedFunction = false;
		$curlyCount = null;

		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_CLASS:
				$classAttributes = [];
				$functionList = [];
				$touchedVisibility = false;
				$touchedFunction = false;
				$curlyCount = null;
				$this->appendCode($text);
				while (list($index, $token) = each($this->tkns)) {
					list($id, $text) = $this->getToken($token);
					$this->ptr = $index;
					if (ST_CURLY_OPEN == $id) {
						++$curlyCount;
					}
					if (ST_CURLY_CLOSE == $id) {
						--$curlyCount;
					}
					if (0 === $curlyCount) {
						break;
					}
					$this->appendCode($text);
					if (T_PUBLIC == $id) {
						$touchedVisibility = T_PUBLIC;
					} elseif (T_PRIVATE == $id) {
						$touchedVisibility = T_PRIVATE;
					} elseif (T_PROTECTED == $id) {
						$touchedVisibility = T_PROTECTED;
					}
					if (
						T_VARIABLE == $id &&
						(
							T_PUBLIC == $touchedVisibility ||
							T_PRIVATE == $touchedVisibility ||
							T_PROTECTED == $touchedVisibility
						)
					) {
						$classAttributes[] = $text;
						$touchedVisibility = null;
					} elseif (T_FUNCTION == $id) {
						$touchedFunction = true;
					} elseif ($touchedFunction && T_STRING == $id) {
						$functionList[] = $text;
						$touchedVisibility = null;
						$touchedFunction = false;
					}
				}
				$functionList = array_combine($functionList, $functionList);
				if (!isset($functionList['__construct'])) {
					$this->appendCode('function __construct(' . implode(', ', $classAttributes) . '){' . $this->newLine);
					foreach ($classAttributes as $var) {
						$this->appendCode($this->generate($var));
					}
					$this->appendCode('}' . $this->newLine);
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

	private function generate($var) {
		switch ($this->type) {
		case self::TYPE_SNAKE_CASE:
			$ret = $this->generateSnakeCase($var);
			break;
		case self::TYPE_GOLANG:
			$ret = $this->generateGolang($var);
			break;
		case self::TYPE_CAMEL_CASE:
		default:
			$ret = $this->generateCamelCase($var);
			break;
		}
		return $ret;
	}

	private function generateCamelCase($var) {
		$str = '$this->set' . ucfirst(str_replace('$', '', $var)) . '(' . $var . ');' . $this->newLine;
		return $str;
	}

	private function generateGolang($var) {
		$str = '$this->Set' . ucfirst(str_replace('$', '', $var)) . '(' . $var . ');' . $this->newLine;
		return $str;
	}

	private function generateSnakeCase($var) {
		$str = '$this->set_' . (str_replace('$', '', $var)) . '(' . $var . ');' . $this->newLine;
		return $str;
	}
}