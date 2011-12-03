<?php

require_once 'BaklavaCompressor.php';
//App::import('Lib', 'Baklava.Compressor/BaklavaCompressor', array('file' => 'Compressor/BaklavaCompressor.php'));
App::import('Vendor', 'Baklava.Minify_CSS_Compressor', array('file' => 'minify-2.1.3/min/lib/Minify/CSS/Compressor.php'));

class BaklavaCSSCompressor implements BaklavaCompressor {

	public function compress($input, array $options = array()) {
		$options = array_merge(array('preserveComments' => false), $options);
		return Minify_CSS_Compressor::process($input, $options);
	}
}
