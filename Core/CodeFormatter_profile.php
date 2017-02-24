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

/**
 * @codeCoverageIgnore
 */
final class CodeFormatter extends BaseCodeFormatter {
	private $currentTiming = null;

	private $timings = [];

	public function afterExecutedPass($source, $className) {
		$cn = get_class($className);
		$this->timings[$cn] = microtime(true) - $this->currentTiming;
		echo $cn, ':', (memory_get_usage() / 1024 / 1024), "\t", (memory_get_peak_usage() / 1024 / 1024), PHP_EOL;
	}

	public function afterFormat($source) {
		asort($this->timings, SORT_NUMERIC);
		$total = array_sum($this->timings);

		$lines = [];
		foreach ($this->timings as $pass => $timing) {
			$lines[] = [$pass, $timing, str_pad(round($timing / $total * 100, 3) . '%', 8, ' ', STR_PAD_LEFT)];
		}
		echo tabwriter($lines);
	}

	public function beforeFormat($source) {
		echo 'before:', (memory_get_usage() / 1024 / 1024), "\t", (memory_get_peak_usage() / 1024 / 1024), PHP_EOL;
	}

	public function beforePass($source, $className) {
		$this->currentTiming = microtime(true);
	}
}
