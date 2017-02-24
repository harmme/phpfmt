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

final class SortUseNameSpace extends AdditionalPass {
	private $pass = null;

	public function __construct() {
		$sortFunction = function ($useStack) {
			usort($useStack, function ($a, $b) {
				$len = strlen($a) - strlen($b);
				if (0 === $len) {
					return strcmp($a, $b);
				}
				return $len;
			});
			return $useStack;
		};
		$this->pass = new OrderAndRemoveUseClauses($sortFunction);
	}

	public function candidate($source, $foundTokens) {
		return $this->pass->candidate($source, $foundTokens);
	}

	public function format($source) {
		return $this->pass->format($source);
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Organize use clauses by length and alphabetic order.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return '';
	}
}