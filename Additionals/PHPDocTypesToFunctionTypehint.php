<?php
namespace contal\fmt;

class PHPDocTypesToFunctionTypehint extends AdditionalPass {
	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[T_FUNCTION])) {
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
			case T_FUNCTION:
				$this->appendCode($text);
				if (!$this->rightUsefulTokenIs(T_STRING)) {
					continue;
				}
				if (!$this->leftTokenIs(T_DOC_COMMENT)) {
					continue;
				}

				$foundParams = [];
				$foundReturn = '';
				list(, $docBlock) = $this->leftToken();
				$words = explode(' ', $docBlock);
				while (list(, $word) = each($words)) {
					$word = trim(strtolower($word));
					switch ($word) {
					case '@param':
						$foundType = '';
						$foundName = '';
						while (list(, $word) = each($words)) {
							$word = trim(strtolower($word));
							if ('$' == $word[0]) {
								$foundName = $word;
								break;
							} else {
								$foundType = $word;
							}
						}
						$foundParams[$foundName] = $foundType;
					case '@return':
						while (list(, $word) = each($words)) {
							$word = trim(strtolower($word));
							$foundReturn = $word;
							break;
						}
					}
				}
				while (list($index, $token) = each($this->tkns)) {
					list($id, $text) = $this->getToken($token);
					$this->ptr = $index;
					if (ST_CURLY_OPEN == $id && '' != $foundReturn) {
						$text = ':' . $foundReturn . ' ' . $text;
						$this->appendCode($text);
						break;
					}
					if (T_VARIABLE == $id && isset($foundParams[$text])) {
						$text = $foundParams[$text] . ' ' . $text;
					}
					$this->appendCode($text);
				}
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
		return 'Read variable types from PHPDoc blocks and add them in function signatures.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<?php
// From:
/**
 * @param int $a
 * @param int $b
 * @return int
 */
function abc($a = 10, $b = 20, $c) {

}

// To:
/**
 * @param int $a
 * @param int $b
 * @return int
 */
function abc(int $a = 10, int $b = 20, $c): int {

}
?>
EOT;
	}
}