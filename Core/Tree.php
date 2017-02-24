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

final class Tree extends FormatterPass {
	public function candidate($source, $tokens) {
		return true;
	}

	public function consumeBlock(&$tkns, $start, $end) {
		$count = 1;
		$block = [];
		while (list(, $token) = each($tkns)) {
			list($id, $text) = $this->getToken($token);

			if ($start == $id) {
				++$count;
			}
			if ($end == $id) {
				--$count;
			}
			if (0 == $count) {
				break;
			}
			$block[] = [$id, $text];
		}

		return $block;
	}

	public function consumeCurlyBlock(&$tkns) {
		$count = 1;
		$block = [];
		while (list(, $token) = each($tkns)) {
			list($id, $text) = $this->getToken($token);

			if (ST_CURLY_OPEN == $id) {
				++$count;
			}
			if (T_CURLY_OPEN == $id) {
				++$count;
			}
			if (T_DOLLAR_OPEN_CURLY_BRACES == $id) {
				++$count;
			}
			if (ST_CURLY_CLOSE == $id) {
				--$count;
			}
			if (0 == $count) {
				break;
			}
			$block[] = [$id, $text];
		}
		return $block;
	}

	public function format($code) {
		$tokens = token_get_all($code);
		$tree = $this->parseTree($tokens);
		return $this->visit($tree);
	}

	public function parseTree($tokens) {
		$tree = [];

		while (list(, $token) = each($tokens)) {
			list($id, $text) = $this->getToken($token);

			// SOLVE COLON BLOCKS
			// T_ENDDECLARE, T_ENDFOR, T_ENDFOREACH, T_ENDIF, T_ENDSWITCH, T_ENDWHILE

			if (T_WHITESPACE == $id) {
				$lnCount = substr_count($text, "\n");
				$text = str_repeat("\n", $lnCount);
				if (0 == $lnCount) {
					$text = ' ';
				} elseif ($lnCount > 2) {
					$lnCount = 2;
					$text = str_repeat("\n", $lnCount);
				}
			}

			if (ST_PARENTHESES_OPEN == $id) {
				$block = $this->consumeBlock(
					$tokens,
					ST_PARENTHESES_OPEN,
					ST_PARENTHESES_CLOSE
				);
				$block = $this->parseTree($block);
				array_unshift($block, ST_PARENTHESES_BLOCK, ST_PARENTHESES_OPEN); // get rid of this array_unshift -- too sloww
				array_push($block, ST_PARENTHESES_CLOSE);
				$tree[] = $block;
				continue;
			}

			if (ST_BRACKET_OPEN == $id) {
				$block = $this->consumeBlock(
					$tokens,
					ST_BRACKET_OPEN,
					ST_BRACKET_CLOSE
				);
				$block = $this->parseTree($block);
				array_unshift($block, ST_BRACKET_BLOCK, ST_BRACKET_OPEN); // get rid of this array_unshift -- too sloww
				array_push($block, ST_BRACKET_CLOSE);
				$tree[] = $block;
				continue;
			}

			if (ST_CURLY_OPEN == $id) {
				$block = $this->consumeCurlyBlock(
					$tokens
				);
				$block = $this->parseTree($block);
				array_unshift($block, ST_CURLY_BLOCK, ST_CURLY_OPEN); // get rid of this array_unshift -- too sloww
				array_push($block, ST_CURLY_CLOSE);
				$tree[] = $block;
				continue;
			}

			$tree[] = [$id, $text];
		}

		return $tree;
	}

	public function visit($tree) {
		$str = '';

		foreach ($tree as $token) {
			list($id, $text) = $this->getToken($token);

			if (ST_PARENTHESES_BLOCK == $id || ST_BRACKET_BLOCK == $id || ST_CURLY_BLOCK == $id) {
				array_shift($token);
				$open = array_shift($token); // get rid of this array_unshift -- too sloww
				$close = array_pop($token);
				$block = $this->visit($token);
				$block = str_replace("\n", "\n\t", $block);
				$block = (
					$open .
					rtrim($block) .
					(
						("\t" == substr($block, -1) || ' ' == substr($block, -1))
						? "\n"
						: ''
					) .
					$close
				);
				$str .= $block;
				continue;
			}

			$str .= $text;
		}

		return $str;
	}
}