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

class SandboxedPass extends FormatterPass {
	public function candidate($source, $foundTokens) {
		return static::candidate($source, $foundTokens);
	}

	public function format($source) {
		return static::format($source);
	}

	final protected function alignPlaceholders($origPlaceholder, $contextCounter) {
		return parent::alignPlaceholders($origPlaceholder, $contextCounter);
	}

	final protected function appendCode($code = '') {
		return parent::appendCode($code);
	}

	final protected function getCrlf() {
		return parent::getCrlf();
	}

	final protected function getCrlfIndent() {
		return parent::getCrlfIndent();
	}

	final protected function getIndent($increment = 0) {
		return parent::getIndent($increment);
	}

	final protected function getSpace($true = true) {
		return parent::getSpace($true);
	}

	final protected function getToken($token) {
		return parent::getToken($token);
	}

	final protected function hasLn($text) {
		return parent::hasLn($text);
	}

	final protected function hasLnAfter() {
		return parent::hasLnAfter();
	}

	final protected function hasLnBefore() {
		return parent::hasLnBefore();
	}

	final protected function hasLnLeftToken() {
		return parent::hasLnLeftToken();
	}

	final protected function hasLnRightToken() {
		return parent::hasLnRightToken();
	}

	final protected function inspectToken($delta = 1) {
		return parent::inspectToken($delta);
	}

	final protected function isShortArray() {
		return parent::isShortArray();
	}

	final protected function leftMemoTokenIs($token) {
		return parent::leftMemoTokenIs($token);
	}

	final protected function leftMemoUsefulTokenIs($token, $debug = false) {
		return parent::leftMemoUsefulTokenIs($token, $debug);
	}

	final protected function leftToken($ignoreList = []) {
		return parent::leftToken($ignoreList = []);
	}

	final protected function leftTokenIdx($ignoreList = []) {
		return parent::leftTokenIdx($ignoreList = []);
	}

	final protected function leftTokenIs($token, $ignoreList = []) {
		return parent::leftTokenIs($token, $ignoreList = []);
	}

	final protected function leftTokenSubsetAtIdx($tkns, $idx, $ignoreList = []) {
		return parent::leftTokenSubsetAtIdx($tkns, $idx, $ignoreList = []);
	}

	final protected function leftTokenSubsetIsAtIdx($tkns, $idx, $token, $ignoreList = []) {
		return parent::leftTokenSubsetIsAtIdx($tkns, $idx, $token, $ignoreList = []);
	}

	final protected function leftUsefulToken() {
		return parent::leftUsefulToken();
	}

	final protected function leftUsefulTokenIdx() {
		return parent::leftUsefulTokenIdx();
	}

	final protected function leftUsefulTokenIs($token) {
		return parent::leftUsefulTokenIs($token);
	}

	final protected function memoPtr() {
		return parent::memoPtr();
	}

	final protected function peekAndCountUntilAny($tkns, $ptr, $tknids) {
		return parent::peekAndCountUntilAny($tkns, $ptr, $tknids);
	}

	final protected function printAndStopAt($tknids) {
		return parent::printAndStopAt($tknids);
	}

	final protected function printAndStopAtEndOfParamBlock() {
		return parent::printAndStopAtEndOfParamBlock();
	}

	final protected function printBlock($start, $end) {
		return parent::printBlock($start, $end);
	}

	final protected function printCurlyBlock() {
		return parent::printCurlyBlock();
	}

	final protected function printUntil($tknid) {
		return parent::printUntil($tknid);
	}

	final protected function printUntilAny($tknids) {
		return parent::printUntilAny($tknids);
	}

	final protected function printUntilTheEndOfString() {
		return parent::printUntilTheEndOfString();
	}

	final protected function refInsert(&$tkns, &$ptr, $item) {
		return parent::refInsert($tkns, $ptr, $item);
	}

	final protected function refSkipBlocks($tkns, &$ptr) {
		return parent::refSkipBlocks($tkns, $ptr);
	}

	final protected function refSkipIfTokenIsAny($tkns, &$ptr, $skipIds) {
		return parent::refSkipIfTokenIsAny($tkns, $ptr, $skipIds);
	}

	final protected function refWalkBackUsefulUntil($tkns, &$ptr, array $expectedId) {
		return parent::refWalkBackUsefulUntil($tkns, $ptr, $expectedId);
	}

	final protected function refWalkBlock($tkns, &$ptr, $start, $end) {
		return parent::refWalkBlock($tkns, $ptr, $start, $end);
	}

	final protected function refWalkBlockReverse($tkns, &$ptr, $start, $end) {
		return parent::refWalkBlockReverse($tkns, $ptr, $start, $end);
	}

	final protected function refWalkCurlyBlock($tkns, &$ptr) {
		return parent::refWalkCurlyBlock($tkns, $ptr);
	}

	final protected function refWalkCurlyBlockReverse($tkns, &$ptr) {
		return parent::refWalkCurlyBlockReverse($tkns, $ptr);
	}

	final protected function refWalkUsefulUntil($tkns, &$ptr, $expectedId) {
		return parent::refWalkUsefulUntil($tkns, $ptr, $expectedId);
	}

	final protected function render($tkns = null) {
		return parent::render($tkns);
	}

	final protected function renderLight($tkns = null) {
		return parent::renderLight($tkns);
	}

	final protected function rightToken($ignoreList = []) {
		return parent::rightToken($ignoreList);
	}

	final protected function rightTokenIdx($ignoreList = []) {
		return parent::rightTokenIdx($ignoreList);
	}

	final protected function rightTokenIs($token, $ignoreList = []) {
		return parent::rightTokenIs($token, $ignoreList);
	}

	final protected function rightTokenSubsetAtIdx($tkns, $idx, $ignoreList = []) {
		return parent::rightTokenSubsetAtIdx($tkns, $idx, $ignoreList);
	}

	final protected function rightTokenSubsetIsAtIdx($tkns, $idx, $token, $ignoreList = []) {
		return parent::rightTokenSubsetIsAtIdx($tkns, $idx, $token, $ignoreList);
	}

	final protected function rightUsefulToken() {
		return parent::rightUsefulToken();
	}

	final protected function rightUsefulTokenIdx() {
		return parent::rightUsefulTokenIdx();
	}

	final protected function rightUsefulTokenIs($token) {
		return parent::rightUsefulTokenIs($token);
	}

	final protected function rtrimAndAppendCode($code = '') {
		return parent::rtrimAndAppendCode($code);
	}

	final protected function rtrimLnAndAppendCode($code = '') {
		return parent::rtrimLnAndAppendCode($code);
	}

	final protected function scanAndReplace(&$tkns, &$ptr, $start, $end, $call, $lookFor) {
		return parent::scanAndReplace($tkns, $ptr, $start, $end, $call, $lookFor);
	}

	final protected function scanAndReplaceCurly(&$tkns, &$ptr, $start, $call, $lookFor) {
		return parent::scanAndReplaceCurly($tkns, $ptr, $start, $call, $lookFor);
	}

	final protected function setIndent($increment) {
		return parent::setIndent($increment);
	}

	final protected function siblings($tkns, $ptr) {
		return parent::siblings($tkns, $ptr);
	}

	final protected function substrCountTrailing($haystack, $needle) {
		return parent::substrCountTrailing($haystack, $needle);
	}

	final protected function tokenIs($direction, $token, $ignoreList = []) {
		return parent::tokenIs($direction, $token, $ignoreList);
	}

	final protected function walkAndAccumulateCurlyBlock(&$tkns) {
		return parent::walkAndAccumulateCurlyBlock($tkns);
	}

	final protected function walkAndAccumulateStopAt(&$tkns, $tknid) {
		return parent::walkAndAccumulateStopAt($tkns, $tknid);
	}

	final protected function walkAndAccumulateStopAtAny(&$tkns, $tknids) {
		return parent::walkAndAccumulateStopAtAny($tkns, $tknids);
	}

	final protected function walkAndAccumulateUntil(&$tkns, $tknid) {
		return parent::walkAndAccumulateUntil($tkns, $tknid);
	}

	final protected function walkAndAccumulateUntilAny(&$tkns, $tknids) {
		return parent::walkAndAccumulateUntilAny($tkns, $tknids);
	}

	final protected function walkUntil($tknid) {
		return parent::walkUntil($tknid);
	}
}