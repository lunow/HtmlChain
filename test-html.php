<?php

	require_once 'html.php';
	require_once 'PHPUnit/Autoload.php';
	

	/*
	 *	Test the HTML Base Class
	 */
	class HtmlTest extends PHPUnit_Framework_TestCase {
		protected $html;

		protected function setUp() {
			$this->html = new Html();
		}

		public function testClosedTags() {
			$this->assertEquals('<base />', $this->html->base());
			$this->assertEquals('<base href="http://test.com" />', $this->html->base(array('href' => 'http://test.com')));
			$this->assertEquals('<link href="test.css" rel="stylesheet" />', $this->html->link(array('rel' => 'stylesheet', 'href' => 'test.css')));
			$this->assertEquals('<img src="img.jpg" />', $this->html->img(array('src' => 'img.jpg')));
		}

		public function testSimpleDiv() {			
			$code = '<div></div>';
			$this->assertEquals($this->html->div(), $code);
		}

		public function testSimpleDivWithAttribute() {
			$code = '<div class="test"></div>';
			$this->assertEquals($this->html->div(array('class' => 'test')), $code);
		}

		public function testAttributeWithoutValue() {
			$code = '<div autodiscover></div>';
			$this->assertEquals($code, $this->html->div(array('autodiscover')));
		}

		public function testAttributeWithAndWithoutValue() {
			$code = '<div autodiscover class="cool"></div>';
			$this->assertEquals($code, $this->html->div(array('class' => 'cool', 'autodiscover')));
			//this one fails because ksort() dont sort values without a key :(
			//@TODO: fix this!
			//$this->assertEquals($code, $this->html->div(array('autodiscover', 'class' => 'cool')));
		}

		public function testOrderOfAttribute() {
			$code = '<div a="1" b="2" c="3"></div>';
			$this->assertEquals($this->html->div(array('c' => '3', 'a' => '1', 'b' => '2')), $code);

			$code = '<div a="x" b="x" c="x"></div>';
			$this->assertEquals($this->html->div(array('c' => 'x', 'a' => 'x', 'b' => 'x')), $code);
		}

		public function testSimpleDivWithContent() {
			$code = '<div>hello</div>';
			$this->assertEquals($this->html->div('hello'), $code);
		}

		public function testSimpleDivWithClassAndContent() {
			$code = '<div class="test">hello</div>';
			$this->assertEquals($this->html->div('hello', array('class' => 'test')), $code);
		}

		public function testHtml5Doctype() {
			$code = '<!DOCTYPE html>';
			$this->assertEquals($this->html->doctype(), $code);
		}

		public function testMailto() {
			$code = '<a href="mailto:info@test.de">info@test.de</a>';
			$this->assertEquals($this->html->mailto('info@test.de'), $code);
		}

		public function testMailtoWithAttribut() {
			$code = '<a class="mail" href="mailto:info@test.de">info@test.de</a>';
			$this->assertEquals($this->html->mailto('info@test.de', array('class' => 'mail')), $code);
		}

		public function testUtf8Tag() {
			$code = '<meta charset="utf-8">';
			$this->assertEquals($this->html->utf8(), $code);
		}
	}