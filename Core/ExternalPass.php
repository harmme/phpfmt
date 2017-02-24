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

class ExternalPass {
	private $passName = '';

	public function __construct($passName) {
		$this->passName = $passName;
	}

	public function candidate() {
		return !empty($this->passName);
	}

	public function format($source) {
		$descriptorspec = [
			0 => ['pipe', 'r'], // stdin is a pipe that the child will read from
			1 => ['pipe', 'w'], // stdout is a pipe that the child will write to
			2 => ['pipe', 'w'], // stderr is a file to write to
		];

		$cwd = getcwd();
		$env = [];
		$argv = $_SERVER['argv'];
		$pipes = null;

		$external = str_replace('fmt.', 'fmt-external.', $cwd . DIRECTORY_SEPARATOR . $argv[0]);

		$cmd = $_SERVER['_'] . ' ' . $external . ' --pass=' . $this->passName;
		$process = proc_open(
			$cmd,
			$descriptorspec,
			$pipes,
			$cwd,
			$env
		);
		if (!is_resource($process)) {
			fclose($pipes[0]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			proc_close($process);
			return $source;
		}
		fwrite($pipes[0], $source);
		fclose($pipes[0]);

		$source = stream_get_contents($pipes[1]);
		fclose($pipes[1]);

		fclose($pipes[2]);
		proc_close($process);
		return $source;
	}
}
