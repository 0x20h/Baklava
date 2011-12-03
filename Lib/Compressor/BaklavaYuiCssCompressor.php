<?php
require_once 'BaklavaCompressor.php';

class BaklavaYuiCssCompressor implements BaklavaCompressor {

	public function compress($input, array $options = array()) {
		$tmpFile = tempnam(TMP, 'yui');
		$handle = fopen($tmpFile, 'w');
		fwrite($handle, $input);
		$out = shell_exec('cat '.$tmpFile.' | java -jar '.$options['jar']. ' --type css');
		unlink($tmpFile);
		return $out;
	}
}
