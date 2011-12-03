<?php
require_once 'BaklavaCompressor.php';
//App::import('Lib', 'Baklava.Compressor/BaklavaCompressor', array('file' => 'Compressor/BaklavaCompressor.php'));

class BaklavaClosureCompilerCompressor implements BaklavaCompressor {

	public function compress($input, array $options = array()) {
		$tmpFile = tempnam(TMP, 'closure_compiler');
		$handle = fopen($tmpFile, 'w');
		fwrite($handle, $input);
		$out = shell_exec('cat '.$tmpFile.' | java -jar '.$options['jar']);
		unlink($tmpFile);
		return $out;
	}
}
