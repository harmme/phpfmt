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

final class SettersAndGettersPass extends FormatterPass {
	const PLACEHOLDER = "/*SETTERSANDGETTERSPLACEHOLDER%s\x3*/";

	const PLACEHOLDER_REGEX = '/(;\n\/\*SETTERSANDGETTERSPLACEHOLDER).*(\*\/)/';

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
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_CLASS:
				$attributes = [
					'private' => [],
					'public' => [],
					'protected' => [],
				];
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
					if (T_VARIABLE == $id && T_PUBLIC == $touchedVisibility) {
						$attributes['public'][] = $text;
						$touchedVisibility = null;
						$this->printPlaceholder($text);
					} elseif (T_VARIABLE == $id && T_PRIVATE == $touchedVisibility) {
						$attributes['private'][] = $text;
						$touchedVisibility = null;
						$this->printPlaceholder($text);
					} elseif (T_VARIABLE == $id && T_PROTECTED == $touchedVisibility) {
						$attributes['protected'][] = $text;
						$touchedVisibility = null;
						$this->printPlaceholder($text);
					} elseif (T_FUNCTION == $id) {
						$touchedFunction = true;
					} elseif ($touchedFunction && T_STRING == $id) {
						$functionList[] = $text;
						$touchedVisibility = null;
						$touchedFunction = false;
					}
				}
				$functionList = array_combine($functionList, $functionList);
				$append = false;
				foreach ($attributes as $visibility => $variables) {
					foreach ($variables as $var) {
						$str = $this->generate($visibility, $var);
						foreach ($functionList as $k => $v) {
							if (false !== stripos($str, $v)) {
								unset($functionList[$k]);
								$append = true;
								continue 2;
							}
						}
						if ($append) {
							$this->appendCode($str);
							continue;
						}
						$this->code = str_replace(sprintf(self::PLACEHOLDER, $var), $str, $this->code);
					}
				}

				$this->appendCode($text);
				break;
			default:
				$this->appendCode($text);
				break;
			}
		}
		$this->code = preg_replace(self::PLACEHOLDER_REGEX, ';', $this->code);
		return $this->code;
	}

	private function generate($visibility, $var) {
		switch ($this->type) {
		case self::TYPE_SNAKE_CASE:
			$ret = $this->generateSnakeCase($visibility, $var);
			break;
		case self::TYPE_GOLANG:
			$ret = $this->generateGolang($visibility, $var);
			break;
		case self::TYPE_CAMEL_CASE:
		default:
			$ret = $this->generateCamelCase($visibility, $var);
			break;
		}
		return $ret;
	}

	private function generateCamelCase($visibility, $var) {
		$str = $this->newLine . $visibility . ' function set' . ucfirst(str_replace('$', '', $var)) . '(' . $var . '){' . $this->newLine . '$this->' . str_replace('$', '', $var) . ' = ' . $var . ';' . $this->newLine . '}' . $this->newLine . $this->newLine;
		$str .= $visibility . ' function get' . ucfirst(str_replace('$', '', $var)) . '(){' . $this->newLine . 'return $this->' . str_replace('$', '', $var) . ';' . $this->newLine . '}' . $this->newLine;
		return $str;
	}

	private function generateGolang($visibility, $var) {
		$str = $this->newLine . $visibility . ' function Set' . ucfirst(str_replace('$', '', $var)) . '(' . $var . '){' . $this->newLine . '$this->' . str_replace('$', '', $var) . ' = ' . $var . ';' . $this->newLine . '}' . $this->newLine . $this->newLine;
		$str .= $visibility . ' function ' . ucfirst(str_replace('$', '', $var)) . '(){' . $this->newLine . 'return $this->' . str_replace('$', '', $var) . ';' . $this->newLine . '}' . $this->newLine;
		return $str;
	}

	private function generateSnakeCase($visibility, $var) {
		$str = $this->newLine . $visibility . ' function set_' . (str_replace('$', '', $var)) . '(' . $var . '){' . $this->newLine . '$this->' . str_replace('$', '', $var) . ' = ' . $var . ';' . $this->newLine . '}' . $this->newLine . $this->newLine;
		$str .= $visibility . ' function get_' . (str_replace('$', '', $var)) . '(){' . $this->newLine . 'return $this->' . str_replace('$', '', $var) . ';' . $this->newLine . '}' . $this->newLine;
		return $str;
	}

	private function printPlaceholder($text) {
		$this->skipPlaceholderUntilSemicolon();

		$this->appendCode(';' . $this->newLine . sprintf(self::PLACEHOLDER, $text));
	}

	private function skipPlaceholderUntilSemicolon() {
		if ($this->rightUsefulTokenIs(ST_EQUAL)) {
			return $this->printAndStopAt(ST_SEMI_COLON);
		}
		each($this->tkns);
	}
}