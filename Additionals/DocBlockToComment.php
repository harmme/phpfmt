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

// From PHP-CS-Fixer
final class DocBlockToComment extends AdditionalPass {
	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[T_DOC_COMMENT])) {
			return true;
		}

		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';
		$this->useCache = true;

		$touchedOpenTag = false;
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			$this->tkns[$this->ptr] = [$id, $text];
			$this->cache = [];

			if (T_DOC_COMMENT != $id) {
				continue;
			}

			if (!$touchedOpenTag && $this->leftUsefulTokenIs(T_OPEN_TAG)) {
				$touchedOpenTag = true;
				continue;
			}

			if ($this->isStructuralElement()) {
				continue;
			}

			$commentTokenText = &$this->tkns[$this->ptr][1];

			if ($this->rightUsefulTokenIs(T_VARIABLE)) {
				$commentTokenText = $this->updateCommentAgainstVariable($commentTokenText);
				continue;
			}

			if ($this->rightUsefulTokenIs([T_FOREACH, T_LIST])) {
				$commentTokenText = $this->updateCommentAgainstParenthesesBlock($commentTokenText);
				continue;
			}

			if (null === $this->rightUsefulToken() || $this->rightUsefulTokenIs(ST_CURLY_CLOSE)) {
				$commentTokenText = $this->updateComment($commentTokenText);
				continue;
			}

			$commentTokenText = $this->updateComment($commentTokenText);
		}

		return $this->renderLight($this->tkns);
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Replace docblocks with regular comments when used in non structural elements.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
EOT;
	}

	protected function walkAndNormalizeUntil($tknid) {
		while (list($index, $token) = each($this->tkns)) {
			$this->ptr = $index;
			$this->cache = [];
			if ($token[0] == $tknid) {
				$t = &$this->tkns[$this->ptr];
				$t = $this->getToken($token);
				return $t;
			}
		}
	}

	private function isStructuralElement() {
		return $this->rightUsefulTokenIs([
			T_PRIVATE, T_PROTECTED, T_PUBLIC,
			T_FUNCTION, T_ABSTRACT, T_CONST,
			T_NAMESPACE, T_REQUIRE, T_REQUIRE_ONCE,
			T_INCLUDE, T_INCLUDE_ONCE, T_FINAL,
			T_CLASS, T_INTERFACE, T_TRAIT, T_STATIC,
		]);
	}

	private function updateComment($commentTokenText) {
		return preg_replace('/\/\*\*/', '/*', $commentTokenText, 1);
	}

	private function updateCommentAgainstParenthesesBlock($commentTokenText) {
		$this->walkAndNormalizeUntil(ST_PARENTHESES_OPEN);
		$variables = $this->variableListFromParenthesesBlock($this->tkns, $this->ptr);

		$foundVar = false;
		foreach ($variables as $var) {
			if (false !== strpos($commentTokenText, $var)) {
				$foundVar = true;
				break;
			}
		}
		if (!$foundVar) {
			$commentTokenText = $this->updateComment($commentTokenText);
		}
		return $commentTokenText;
	}

	private function updateCommentAgainstVariable($commentTokenText) {
		list(, $nextText) = $this->rightUsefulToken();
		$this->ptr = $this->rightUsefulTokenIdx();
		$this->cache = [];
		if (!$this->rightUsefulTokenIs(ST_EQUAL) ||
			false === strpos($commentTokenText, $nextText)) {
			$commentTokenText = $this->updateComment($commentTokenText);
		}
		return $commentTokenText;
	}

	private function variableListFromParenthesesBlock($tkns, $ptr) {
		$sizeOfTkns = sizeof($tkns);
		$variableList = [];
		$count = 0;
		for ($i = $ptr; $i < $sizeOfTkns; ++$i) {
			$token = $tkns[$i];
			list($id, $text) = $this->getToken($token);

			if (T_VARIABLE == $id) {
				$variableList[] = $text;
			}
			if (ST_PARENTHESES_OPEN == $id) {
				++$count;
			}
			if (ST_PARENTHESES_CLOSE == $id) {
				--$count;
			}
			if (0 == $count) {
				break;
			}
		}
		return array_unique($variableList);
	}
}