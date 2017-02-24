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

final class PSR2AlignObjOp extends FormatterPass {
	const ALIGNABLE_TOKEN = "\x2 OBJOP%d \x3";

	public function candidate($source, $foundTokens) {
		if (isset($foundTokens[ST_SEMI_COLON]) || isset($foundTokens[T_ARRAY]) || isset($foundTokens[T_DOUBLE_ARROW]) || isset($foundTokens[T_OBJECT_OPERATOR])) {
			return true;
		}

		return false;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';
		$contextCounter = 0;
		$contextMetaCount = [];
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case ST_SEMI_COLON:
			case T_ARRAY:
			case T_DOUBLE_ARROW:
				++$contextCounter;
				$this->appendCode($text);
				break;

			case T_OBJECT_OPERATOR:
				if (!isset($contextMetaCount[$contextCounter])) {
					$contextMetaCount[$contextCounter] = 0;
				}
				if ($this->hasLnBefore() || 0 == $contextMetaCount[$contextCounter]) {
					$this->appendCode(sprintf(self::ALIGNABLE_TOKEN, $contextCounter) . $text);
					++$contextMetaCount[$contextCounter];
					break;
				}
			default:
				$this->appendCode($text);
				break;
			}
		}

		for ($j = 0; $j <= $contextCounter; ++$j) {
			$placeholder = sprintf(self::ALIGNABLE_TOKEN, $j);
			if (false === strpos($this->code, $placeholder)) {
				continue;
			}
			if (1 === substr_count($this->code, $placeholder)) {
				$this->code = str_replace($placeholder, '', $this->code);
				continue;
			}

			$lines = explode($this->newLine, $this->code);
			$linesWithObjop = [];
			$blockCount = 0;

			foreach ($lines as $idx => $line) {
				if (false !== strpos($line, $placeholder)) {
					$linesWithObjop[$blockCount][] = $idx;
					break;
				}
				++$blockCount;
				$linesWithObjop[$blockCount] = [];
			}

			foreach ($linesWithObjop as $group) {
				$firstline = reset($group);
				$positionFirstline = strpos($lines[$firstline], $placeholder);

				foreach ($group as $idx) {
					if ($idx == $firstline) {
						continue;
					}
					$line = ltrim($lines[$idx]);
					$line = str_replace($placeholder, str_repeat(' ', $positionFirstline) . $placeholder, $line);
					$lines[$idx] = $line;
				}
			}

			$this->code = str_replace($placeholder, '', implode($this->newLine, $lines));
		}
		return $this->code;
	}
}
