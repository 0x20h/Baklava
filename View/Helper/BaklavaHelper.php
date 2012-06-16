<?php
App::uses('Helper', 'View/Helper');
App::import('Lib', 'Baklava.BaklavaCompressor');

class BaklavaHelper extends AppHelper {
	public $helpers = array('Html', 'Js');

	protected $signatures = array(
		'css' => '',
		'js' => '',
	);

	protected $files = array(
		'css' => array(),
		'js' => array(),
	);

	protected $filenames = array();
	protected $compressors = array();
	protected $options = array();

	public function __construct(View $View, array $settings = array()) {
		parent::__construct($View, $settings);
		
		$this->options = Set::merge(array(
			'base_url' => $this->request->webroot . '_c',
			'base_path' => WWW_ROOT . '_c',
			'inline_threshold' => 512,
			'cache_config' => 'default',
		), $settings);
	}

	public function compressFiles($type, $files) {
		if (!is_array($files)) {
			$files = array($files);
		}

		if (!Configure::read('debug') && $cached = $this->cached($type, $files)) {
			return $cached;
		}

		$content = '';
		foreach ($files as $file) {
			$filename = $this->_resolve($type, $file);

			if ($filename) {
				$content .= file_get_contents($filename);
			}
		}

		if (strlen($content) < $this->options['inline_threshold']) {
			switch($type) {
				case 'js':
					$this->Js->buffer($content);
					return '';
				case 'css':
					$this->inlineCss .= $content;
					return '';
				default:
			}
		}

		$compressed = Configure::read('debug') ? $content : $this->compress($type, $content);
		file_put_contents($this->getFile($type, $files), $compressed);
		return $this->getUrl($type, $files);
	}

	public function compress($type, $content) {
		$sig = md5($content);

		if (!Configure::read('debug') && $compressed = Cache::read($sig, $this->options['cache_config'])) {
			return $compressed;
		}

		if (empty($this->compressors[$type])) {
			$compressor = $this->options['compressors'][$type];
			App::import($compressor['type'], $compressor['path']);
			list (, $className) = pluginSplit($compressor['path']);
			$className = basename($className);
			$this->compressors[$type] = new $className();

			if (!$this->compressors[$type] instanceof BaklavaCompressor) {
				throw new RuntimeException('expected instance of BaklavaCompressor');
			}
		}

		$Compressor = $this->compressors[$type];
		$options = isset($this->options['compressors'][$type]['options']) ? $this->options['compressors'][$type]['options'] : array();
		$compressed = $Compressor->compress($content, $options);
		!Configure::read('debug') && Cache::write($sig, $compressed, $this->options['cache_config']);
		return $compressed;
	}


	public function combine($type, $files, array $options = array()) {
		$options = array_merge(array('plugin' => null, 'order' => 'append'), $options, array('type' => $type));

		if (!is_array($files)) $files = array($files);

		foreach($files as $file) {
			if (($filename = $this->_resolve($type, $file)) && empty($this->filenames[$filename])) {
				$this->filenames[$filename] = true;
				$record = array('filename' => $filename, 'options' => $options, 'file' => $file);
			
				if ('append' === $options['order'] || $options['order'] > count($this->files[$type])) {
					$this->files[$type][] = $record;
				} elseif ('prepend' === $options['order'] || $options['order'] < 0) {
					array_unshift($this->files[$type], $record);
				} elseif (is_numeric($options['order'])) {
					$this->files[$type] = array_merge(
						array_slice($this->files[$type], 0, $options['order']),
						array($record),
						array_slice($this->files[$type], $options['order'], count($this->files[$type]))
					);
				} else {
					trigger_error('unknown value for option "order":'.$options['order']);
				}	
			}
		}
	}


	public function getCombined() {
		$links = array();
		$sig = array();
		
		foreach($this->files as $type => $records) {
			if (empty($records)) {
				continue;
			}

			$compress = array();

			$files = Set::extract($records, '{n}.file');
			$script = $this->compressFiles($type, $files);
			
			if ($script) {
				$links[$type] = $script;
			}
		}
		
		if (!empty($this->inlineCss)) {
			$links['style'] = $this->compress('css', $this->inlineCss);
		}

		return $links;
	}
	
	public function cached($type, array $files) {
		$sig = $this->getSignature($type, $files);
		$file = $this->options['base_path'] . DS . $sig . '.' . $type;

		if (file_exists($file)) {
			return $this->options['base_url'] . DS . $sig . '.' . $type;
		}

		return false;
	}

	public function getSignature($type, array $files) {
		$sig = $type;
		foreach ($files as $file) {
			$sig = md5($sig . $file);
		}

		return $sig;
	}

	public function getUrl($type, $files) {
		$sig = $this->getSignature($type, $files);
		return $this->options['base_url'] . DS . $sig . '.' . $type;
	}

	public function getFile($type, $files) {
		$sig = $this->getSignature($type, $files);
		return $this->options['base_path'] . DS . $sig . '.' . $type;
	}
	

	protected function _resolve($type, $file) {
		$paths = array(APP);

		if ($this->_View->theme) {
			$paths[] = App::themePath($this->_View->theme);
		}

		$plugins = App::objects('plugin');
		
		foreach($plugins as $plugin) {
			if (!CakePlugin::loaded($plugin)) {
				continue;
			}

			$paths[] = App::pluginPath($plugin);
		}

		foreach ($paths as $path) {
			$filename = $path . 'webroot' . DS . $type . DS . $file . '.' . $type;

			if (file_exists($filename)) {
				return $filename;
			}
		}

		trigger_error('unable to resolve '. $type . ' file: '.$file, E_USER_NOTICE);
		return false;
	}

	public function afterLayout($layoutFile) {
	}
}
