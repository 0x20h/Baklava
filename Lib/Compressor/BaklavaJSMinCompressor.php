<?php

App::import('Vendor', 'Baklava.JSMin', array('file' => 'minify-2.1.3/min/lib/JSMin.php'));
//App::uses('BaklavaCompressor', array('file' => 'Compressor/BaklavaCompressor.php'));
//App::import('Lib', 'Baklava.Compressor/BaklavaCompressor', array('file' => 'Compressor/BaklavaCompressor.php'));
require_once 'BaklavaCompressor.php';

class BaklavaJSMinCompressor implements BaklavaCompressor {

	public function compress($input) {
		return JSMin::minify($input);
	}
}
