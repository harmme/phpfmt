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

final class CakePHPStyle extends AdditionalPass {
	private $foundTokens;

	public function candidate($source, $foundTokens) {
		$this->foundTokens = $foundTokens;
		return true;
	}

	public function format($source) {
		$fmt = new PSR2ModifierVisibilityStaticOrder();
		if ($fmt->candidate($source, $this->foundTokens)) {
			$source = $fmt->format($source);
		}
		$fmt = new MergeElseIf();
		if ($fmt->candidate($source, $this->foundTokens)) {
			$source = $fmt->format($source);
		}
		$source = $this->addUnderscoresBeforeName($source);
		$source = $this->removeSpaceAfterCasts($source);
		$source = $this->mergeEqualsWithReference($source);
		$source = $this->resizeSpaces($source);
		return $source;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Applies CakePHP Coding Style';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<?php
namespace A;

class A {
	private $__a;
	protected $_b;
	public $c;

	public function b() {
		if($a) {
			noop();
		} else {
			noop();
		}
	}

	protected function _c() {
		if($a) {
			noop();
		} else {
			noop();
		}
	}
}
?>
EOT;
	}

	private function addUnderscoresBeforeName($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';
		$levelTouched = null;
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_PUBLIC:
			case T_PRIVATE:
			case T_PROTECTED:
				$levelTouched = $id;
				$this->appendCode($text);
				break;

			case T_VARIABLE:
				if (null !== $levelTouched && $this->leftUsefulTokenIs([T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC])) {
					$text = str_replace('$_', '$', $text);
					$text = str_replace('$_', '$', $text);
					if (T_PROTECTED == $levelTouched) {
						$text = str_replace('$', '$_', $text);
					} elseif (T_PRIVATE == $levelTouched) {
						$text = str_replace('$', '$__', $text);
					}
				}
				$this->appendCode($text);
				$levelTouched = null;
				break;
			case T_STRING:
				if (
					null !== $levelTouched &&
					$this->leftUsefulTokenIs(T_FUNCTION) &&
					'_' != $text &&
					'__' != $text &&
					'__construct' != $text &&
					'__destruct' != $text &&
					'__call' != $text &&
					'__callStatic' != $text &&
					'__get' != $text &&
					'__set' != $text &&
					'__isset' != $text &&
					'__unset' != $text &&
					'__sleep' != $text &&
					'__wakeup' != $text &&
					'__toString' != $text &&
					'__invoke' != $text &&
					'__set_state' != $text &&
					'__clone' != $text &&
					' __debugInfo' != $text
				) {
					if (substr($text, 0, 2) == '__') {
						$text = substr($text, 2);
					}
					if (substr($text, 0, 1) == '_') {
						$text = substr($text, 1);
					}
					if (T_PROTECTED == $levelTouched) {
						$text = '_' . $text;
					} elseif (T_PRIVATE == $levelTouched) {
						$text = '__' . $text;
					}
				}
				$this->appendCode($text);
				$levelTouched = null;
				break;
			default:
				$this->appendCode($text);
				break;
			}
		}

		return $this->code;
	}

	private function mergeEqualsWithReference($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			// Otherwise, just print me
			case ST_REFERENCE:
				if ($this->leftUsefulTokenIs(ST_EQUAL)) {
					$this->rtrimAndAppendCode($text . $this->getSpace());
					break;
				}

			default:
				$this->appendCode($text);
			}
		}
		return $this->code;
	}

	private function removeSpaceAfterCasts($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_ARRAY_CAST:
			case T_BOOL_CAST:
			case T_DOUBLE_CAST:
			case T_INT_CAST:
			case T_OBJECT_CAST:
			case T_STRING_CAST:
			case T_UNSET_CAST:
			case T_STRING:
			case T_VARIABLE:
			case ST_PARENTHESES_OPEN:
				if (
					$this->leftUsefulTokenIs([
						T_ARRAY_CAST,
						T_BOOL_CAST,
						T_DOUBLE_CAST,
						T_INT_CAST,
						T_OBJECT_CAST,
						T_STRING_CAST,
						T_UNSET_CAST,
					])
				) {
					$this->rtrimAndAppendCode($text);
					break;
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

	private function resizeSpaces($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_COMMENT:
			case T_DOC_COMMENT:
				if (!$this->hasLnBefore() && $this->leftTokenIs(ST_CURLY_OPEN)) {
					$this->rtrimAndAppendCode($this->getSpace() . $text);
					break;
				} elseif ($this->rightUsefulTokenIs(T_CONSTANT_ENCAPSED_STRING)) {
					$this->appendCode($text . $this->getSpace());
					break;
				}
				$this->appendCode($text);
				break;
			case T_CLOSE_TAG:
				if (!$this->hasLnBefore()) {
					$this->rtrimAndAppendCode($this->getSpace() . $text);
					break;
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
}
