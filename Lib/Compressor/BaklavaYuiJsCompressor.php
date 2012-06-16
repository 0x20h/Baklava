<?php
require_once 'BaklavaCompressor.php';
//App::import('Lib', 'Baklava.Compressor/BaklavaCompressor', array('file' => 'Compressor/BaklavaCompressor.php'));

class BaklavaYuiJsCompressor implements BaklavaCompressor {

	public function compress($input, array $options = array()) {
		$tmpFile = tempnam(TMP, 'yui');
		$handle = fopen($tmpFile, 'w');
		fwrite($handle, $input);
		$out = shell_exec('cat '.$tmpFile.' | java -jar '.$options['jar']. ' --type js');
		unlink($tmpFile);

		if ($out) {
			return $out;
		}

		return $input;
	}
}
