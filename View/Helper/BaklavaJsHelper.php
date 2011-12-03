<?php
App::uses('JsHelper', 'View/Helper');

class BaklavaJsHelper extends JsHelper {

	public $helpers = array('Baklava.Baklava', 'Html');

	public function __construct(View $View, array $settings = array()) {
		// proxy settings to Baklava helper
		$this->helpers['Baklava.Baklava'] = array_merge($settings, array('className' => null));
		parent::__construct($View, $settings);
	}

	public function writeBuffer(array $options = array()) {
		$options = array_merge(array(
				'clear' => true,
				'cache_config' => 'default',
			), $options
		);
		$script = implode("\n", $this->getBuffer($options['clear']));
		
		$sig = md5($script);
		if (!($compressed = Cache::read($sig, $options['cache_config']))) {
			$compressed = $this->Baklava->compress('js', $script);
			Cache::write($sig, $compressed, $options['cache_config']);
		}

		return $this->Html->scriptBlock($compressed);


	}


	public function capture($what = 'start') {
		if ($what == 'start') {
			ob_start();
		} else {
			$content = ob_get_contents();
			ob_end_clean();
			// strip <script> tags
			$content = preg_replace("#.*</?script.*>#m", "", $content);
			$this->buffer($content);
		}
	}
}
