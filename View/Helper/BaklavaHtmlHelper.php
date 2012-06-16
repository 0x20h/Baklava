<?php
App::uses('HtmlHelper', 'View/Helper');

class BaklavaHtmlHelper extends HtmlHelper {

	public $helpers = array('Baklava.Baklava');

	public function __construct(View $View, array $settings = array()) {
		// proxy settings to Baklava helper
		$this->helpers['Baklava.Baklava'] = array_merge($settings, array('className' => null));
		parent::__construct($View, $settings);
	}


	public function css($file, $rel = null, $options = array()) {
		if (!isset($options['inline']) || $options['inline'] === true) {
			$file = $this->Baklava->compressFiles('css', $file);

			if (!empty($file)) {
				return parent::css($file, $rel, $options);
			}

			return '';
		}

		$options['rel'] = $rel;
		$this->Baklava->combine('css', $file, $options);
	}


	public function script($file, array $options = array()) {
		if (isset($options['inline']) && $options['inline'] == true) {
			// replace $file with compressed version
			$file = $this->Baklava->compressFiles('js', $file);
			if (!empty($file)) {
				return parent::script($file, $options);
			}

			return '';
		}

		$this->Baklava->combine('js', $file, $options);
	}


	public function scriptsForLayout($type = null) {
		$scripts = '';
		$files = $this->Baklava->getCombined();

		foreach ($files as $t => $file) {
			if($type && $type != $t) continue;
			switch ($t) {
				case 'css':
					$scripts .= parent::css($file, null, array('inline' => true));
					break;
				case 'js':
					$scripts .= parent::script($file, array('inline' => true, 'defer' => true));
					break;
				case 'style':
					$scripts .= parent::tag('style', $file, array('type' => 'text/css'));
					break;
			}
		}

		return $scripts;
	}


	public function afterLayout($layoutFile) {
		return $this->Baklava->afterLayout($layoutFile);
	}
}
