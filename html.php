<?php
	/**
	 *	HTML CLASS
	 *	VERSION 1.0
	 *
	 */
	class Html {
		public $debug = false;
		public $return = true;
		public $echo = false;
		
		private $method = '';
		private $tagname = '';
		private $content = '';
		private $attributes = array();
		private $dontClose = array('!DOCTYPE html', 'meta', 'link', 'input');
		private $close = array('link', 'base', 'img');

		private function __reset() {
			$this->method = '';
			$this->tagname = '';
			$this->content = '';
			$this->attributes = array();
		}

		private function __closeTag() {
			return '</'.$this->tagname.'>';
		}

		public function __call($name, $arguments) {
			if(empty($this->method)) {
				$this->tagname = strtolower($name);
				$this->content = '';
				if(isset($arguments[0]) && is_array($arguments[0])) {
					$this->attributes = $arguments[0];
				}
				else {
					$this->content = isset($arguments[0]) ? $arguments[0] : null;
					if(isset($arguments[1]) && is_array($arguments[1]) && count($arguments[1]) > 0) {
						$this->attributes = $arguments[1];
					}
				}

				$this->method = $this->tagname.'Tag';
				if(method_exists($this, $this->method)) {
					$result = $this->{$this->method}();
				}
				else {
					$result = $this->defaultTag();
				}

				if($this->debug) {
					echo "<table border='1'><tr><td>tagname</td><td>$this->tagname</td></tr><tr><td>content</td><td>$this->content</td></tr><tr><td>arguments</td><td><pre>". print_r($this->attributes, 1)."</pre></td></tr><tr><td><strong>result</strong></td><td><pre>".htmlspecialchars($result)."</pre></td></tr></table><br>";
				}
				$this->__reset();
				if($this->return) {
					return $result;
				}
				if($this->echo) {
					echo $result;
				}
				return $this;
			}
		}

		public function defaultTag() {
			$html = '<';
			$html.= $this->tagname;
			if(count($this->attributes) > 0) {
				ksort($this->attributes);
				foreach($this->attributes as $key => $value) {
					if(is_array($value)) {
						$val = implode(' ', $value);
					}
					else {
						$val = $value;
					}
					if(is_int($key)) {
						$html.= ' '.$value;
					}
					else {
						$html.= ' '.$key.'="'.$val.'"';
					}
				}
			}
			if(in_array($this->tagname, $this->close)) {
				$html.= ' />';
			}
			else {
				$html.= '>';
				if(!in_array($this->tagname, $this->dontClose)) {
					$html.= $this->content;
					$html.= '</'.$this->tagname.'>';
				}
			}
			//$this->__reset();
			return $html;
		}

		public function doctypeTag() {
			$this->tagname = '!DOCTYPE html';
			return $this->defaultTag();
		}

		public function mailtoTag() {
			$this->tagname = 'a';
			$this->attributes['href'] = 'mailto:'.$this->content;
			return $this->defaultTag();
		}

		public function utf8Tag() {
			$this->tagname = 'meta';
			$this->attributes['charset'] = 'utf-8';
			return $this->defaultTag();
		}
	}

	/**
	 *	HTMLCHAIN CLASS
	 *	VERSION 1.0
	 *
	 */
	class HtmlChain {
		var $Html = '';
		var $stack = array();
		var $path = array();
		var $library = array();
		var $i = 0;
		var $whitespace = true;
		var $dontNest = array('doctype', 'utf8', 'meta', 'title', 'label', 'link', 'script');

		function __construct() {
			$this->Html = new Html();
			$this->Html->debug = false;
			$this->Html->echo = false;
			$this->Html->return = true;
			$this->stack = array();
			$this->library = array();
			$this->path = array();
			$this->i = 0;
		}

		function getTag($key, $internal = false) {
			$tag = '';
			$parts = explode('-', $key, 2);
			$tag = $parts[0];
			if($internal) {
				return $tag;
			}
			if(stristr($tag, '_')) {
				$parts = explode('_', $tag, 2);
				$tag = $parts[0];
			}
			return $tag;
		}

		function addToStack($name) {
			$stack = &$this->stack;
			$this->i++;
			foreach($this->path as $key) {
				$stack = &$stack[$key];
			}
			$elementkey = $name.'-'.$this->i;
			$stack[$elementkey] = array();
			$this->path[] = $elementkey;
			return $elementkey;
		}

		function searchInPath($name) {
			$path = array_reverse($this->path);
			foreach($path as $i => $key) {
				$tag = $this->getTag($key, true);
				if($name == $tag) {
					return count($this->path)-1-$i;
				}
			}
		}

		function updatePath($name) {
			$i = $this->searchInPath($name);
			$this->path = array_slice($this->path, 0, $i);
		}

		function end() {
			array_pop($this->path);
			return $this;
		}

		function c() { return $this->end(); }
		function close() { return $this->end(); }

		function reset() {
			$this->__construct();
			return $this;
		}

		function __call($name, $arguments) {
			//save internal
			$this->updatePath($name);
			$key = $this->addToStack($name);
			
			//order the arguments
			$args = array('', '');
			if(isset($arguments[0]) && is_array($arguments[0])) {
				$args[0] = '';
				$args[1] = $arguments[0];
			}
			elseif(isset($arguments[0]) && is_string($arguments[0])) {
				$args[0] = $arguments[0];
				if(isset($arguments[1]) && is_array($arguments[1])) {
					$args[1] = $arguments[1];
				}
			}

			//explore the magic classes
			if(stristr($name, '_')) {
				$parts = explode('_', $name, 2);
				$class = '';
				if(isset($args[1]['class'])) {
					$class = ' '.$args[1]['class'];
				}
				$args[1]['class'] = $parts[1].$class;
			}

			//stop the nesting if the element are not nestable
			if(in_array($name, $this->dontNest)) {
				$this->end();
			}

			//store in library
			$this->library[$key] = $args;

			//and start the chain
			return $this;
		}

		function render($stack, $level = -1) {
			$br = $this->whitespace ? "\n" : '';
			$tab = $this->whitespace ? "\t" : '';
			$html = '';
			$level++;
			foreach($stack as $key => $value) {
				$tag = $this->getTag($key);
				$attributes = $this->library[$key];
				if(count($value) > 0) {
					$content = '';
					if(!empty($attributes[0])) {
						$content.= $br.str_repeat($tab, $level+1);
						$content.= $attributes[0];
					}
					$content.= $this->render($value, $level);
					$content.= $br.str_repeat($tab, $level);
					$attributes[0] = $content;
				}
				$html.= $br.str_repeat($tab, $level);
				$html.= call_user_func_array(array($this->Html, $tag), $attributes);
			}
			return $html;
		}

		function __toString() {
			$html = $this->render($this->stack);
			$this->reset();
			return $html;
		}

	}