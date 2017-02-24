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

final class ReindentComments extends FormatterPass {
	public $commentStack = [];

	/**
	 * @codeCoverageIgnore
	 */
	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[T_COMMENT])) {
			return true;
		}

		return false;
	}

	public function format($source) {
		reset($this->commentStack);
		$this->tkns = token_get_all($source);
		$this->code = '';
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			$this->tkns[$this->ptr] = [$id, $text];
			if (T_COMMENT == $id) {
				if (LeftAlignComment::NON_INDENTABLE_COMMENT == $text) {
					continue;
				}

				$oldComment = current($this->commentStack);
				next($this->commentStack);
				if (substr($text, 0, 2) != '/*') {
					continue;
				}

				list($ptId, $ptText) = $this->inspectToken(-1);
				if (T_WHITESPACE != $ptId) {
					continue;
				}

				$indent = substr(strrchr($ptText, 10), 1);
				$indentLevel = strlen($indent);
				$innerIndentLevel = $indentLevel + 1;
				$innerIndent = str_repeat($this->indentChar, $innerIndentLevel);

				$lines = explode($this->newLine, $oldComment[1]);
				$forceIndentation = false;
				$leftMostIndentation = -1;
				foreach ($lines as $idx => $line) {
					if (trim($line) == '') {
						continue;
					}
					if (substr($line, 0, 2) == '/*') {
						continue;
					}
					if (substr($line, -2, 2) == '*/') {
						continue;
					}

					if (substr($line, 0, $innerIndentLevel) != $innerIndent) {
						$forceIndentation = true;
					}

					if (!$forceIndentation) {
						continue;
					}

					$lenLine = strlen($line);
					for ($i = 0; $i < $lenLine; ++$i) {
						if ("\t" != $line[$i]) {
							break;
						}
					}
					if (-1 == $leftMostIndentation) {
						$leftMostIndentation = $i;
					}
					$leftMostIndentation = min($leftMostIndentation, $i);
				}

				if ($forceIndentation) {
					foreach ($lines as $idx => $line) {
						if (trim($line) == '') {
							continue;
						}
						if (substr($line, 0, 2) == '/*') {
							continue;
						}
						if (substr($line, -2, 2) == '*/') {
							$lines[$idx] = str_repeat($this->indentChar, $indentLevel) . '*/';
							continue;
						}
						$lines[$idx] = $innerIndent . substr($line, $leftMostIndentation);
					}
				}
				$this->tkns[$this->ptr] = [T_COMMENT, implode($this->newLine, $lines)];
			}
		}

		return $this->renderLight($this->tkns);
	}
}