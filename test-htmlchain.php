<?php

	require_once 'PHPUnit/Autoload.php';
	require_once 'html.php';

	/*
	 *	Test the HTML Chain Class - interesting!!
	 */
	class HtmlChainTest extends PHPUnit_Framework_TestCase {
		protected $chain;

		protected function setUp() {
			$this->chain = new HtmlChain();
			$this->chain->whitespace = false;
		}

		public function testSimpleDiv() {
			$code = '<div></div>';
			$result = (string)$this->chain->div();
			$this->assertEquals($result, $code);
		}

		public function testSimpleDivWithName() {
			$code = '<div class="named"></div>';
			$result = (string)$this->chain->div_named();
			$this->assertEquals($result, $code);
		}

		public function testSimpleDivWithNameAndClass() {
			$code = '<div class="named class"></div>';
			$result = (string)$this->chain->div_named(array('class' => 'class'));
			$this->assertEquals($result, $code);
		}

		public function testTwoDivsInARow() {
			$code = '<div></div><div></div>';
			$result = (string)$this->chain
				->div()
				->div()
			;
			$this->assertEquals($result, $code);
		}

		public function testTwoDivsNested() {
			$code = '<div class="parent"><div></div></div>';
			$result = (string)$this->chain
				->div_parent()
					->div()
			;
			$this->assertEquals($result, $code);
		}

		public function testH1ContentWithSpan() {
			$code = '<h1><span>Hello World</span></h1>';
			$result = (string)$this->chain
				->h1()
				->span('Hello World')
			;
			$this->assertEquals($result, $code);
		}

		public function testH1ContentWithSpanAndAttributes() {
			$code = '<h1><span rel="tooltip" title="Yes, its me">Hello World</span></h1>';
			$result = (string)$this->chain
				->h1()
				->span('Hello World', array('rel' => 'tooltip', 'title' => 'Yes, its me'))
			;
			$this->assertEquals($result, $code);
		}

		public function testResetAttributes() {
			$code = '<h1 rel="one"></h1><span>Hello World</span>';
			$result = (string)$this->chain
				->h1(array('rel' => 'one'))->end()
				->span('Hello World')
			;
			$this->assertEquals($result, $code);
		}

		public function testOverwriteAttributes() {
			$code = '<h1 rel="one"></h1><span rel="two">Hello World</span>';
			$result = (string)$this->chain
				->h1(array('rel' => 'one'))->end()
				->span('Hello World', array('rel' => 'two'))
			;
			$this->assertEquals($result, $code);
		}

		public function testEnd() {
			$code = '<h1></h1><span>Hello World</span>';
			$result = (string)$this->chain
				->h1()->end()
				->span('Hello World')
			;
			$this->assertEquals($result, $code);
		}

		public function testSimpleTable() {
			$code = '<table><tr><td>Cell 1.1</td><td>Cell 1.2</td></tr><tr><td>Cell 2.1</td><td>Cell 2.2</td></tr></table>';
			$result = (string)$this->chain
				->table()
					->tr()
						->td('Cell 1.1')
						->td('Cell 1.2')
					->tr()
						->td('Cell 2.1')
						->td('Cell 2.2')
			;
			$this->assertEquals($result, $code);
		}

		public function testNestedElements() {
			$code = '<div><h2>Headline<small>extra info</small></h2><p>&copy; 2012<a href="mailto:paul@paul-lunow.de">paul@paul-lunow.de</a></p></div>';
			$result = (string)$this->chain
				->div()
					->h2('Headline')
						->small('extra info')->end()
					->end()
					
					->p('&copy; 2012')
						->mailto('paul@paul-lunow.de')
			;
			$this->assertEquals($result, $code);
		}

		public function testNestedElementsAndRewindPath() {
			$code = '<div><h2>Headline<small>extra info</small></h2><p>&copy; 2012<a href="mailto:paul@paul-lunow.de">paul@paul-lunow.de</a></p></div><div><h2>Next Div</h2><p>This is a description</p></div>';
			$result = (string)$this->chain
				->div()
					->h2('Headline')
						->small('extra info')->end()
					->end()
					
					->p('&copy; 2012')
						->mailto('paul@paul-lunow.de')
				->div()
					->h2('Next Div')->end()
					->p('This is a description')
			;
			$this->assertEquals($result, $code);
		}

		public function testGridStructure() {
			$code = '<div class="container"><div class="row head"><div class="grid_6"></div><div class="grid_6"></div></div><div class="row body"><div class="grid_4"></div><div class="grid_4"></div><div class="grid_4"></div></div><div class="row footer"><div class="grid_12"></div></div></div>';
			$result = (string)$this->chain
				->div_container()
					->div_row(array('class' => 'head'))
						->div_grid_6()
						->div_grid_6()
					->div_row(array('class' => 'body'))
						->div_grid_4()
						->div_grid_4()
						->div_grid_4()
					->div_row(array('class' => 'footer'))
						->div_grid_12()
			;
			$this->assertEquals($result, $code);
		}

		public function testHtml5Structure() {
			$code = '<!DOCTYPE html><html><head><title>Fresh Page</title><meta charset="utf-8"></head><body><h1>Hello World</h1><p>What do you think?</p></body></html>';
			$result = (string)$this->chain
				->doctype()
				->html()
					->head()
						->title('Fresh Page')
						->utf8()
					->end()
					->body()
						->h1('Hello World')->end()
						->p('What do you think?')
			;
			$this->assertEquals($result, $code);
		}

		public function testHtml5StructureWithNiceWhitespace() {
			$code = '
<!DOCTYPE html>
<html>
	<head>
		<title>Fresh Page</title>
		<meta charset="utf-8">
	</head>
	<body>
		<h1>Hello World</h1>
		<p>What do you think?</p>
	</body>
</html>';
			$this->chain->whitespace = true;
			$result = (string)$this->chain
				->doctype()
				->html()
					->head()
						->title('Fresh Page')
						->utf8()
					->end()
					->body()
						->h1('Hello World')->end()
						->p('What do you think?')
			;
			$this->assertEquals($result, $code);
		}

		public function testHtmlForm() {
			$code = '<form action="contact.php" class="contact" method="post"><div class="input"><label for="name">Name</label><input id="name" name="name" type="text"></div><div class="input"><label for="email">Email</label><input id="email" name="email" type="text"></div><div class="input submit"><input type="submit" value="Submit!"></div></form>';
			$result = (string)$this->chain
				->form_contact(array('method' => 'post', 'action' => 'contact.php'))
					->div_input()
						->label('Name', array('for' => 'name'))
						->input(array('type' => 'text', 'name' => 'name', 'id' => 'name'))
					->div_input()
						->label('Email', array('for' => 'email'))
						->input(array('type' => 'text', 'name' => 'email', 'id' => 'email'))
					->div_input(array('class' => 'submit'))
						->input(array('type' => 'submit', 'value' => 'Submit!'))
			;
			$this->assertEquals($result, $code);
		}

		public function testNestedLists() {
			$code = '<ul><li>Point 1</li><li>Point 2<ul class="n1"><li class="n1">Point 2.1</li><li class="n1">Point 2.2</li></ul></li><li>Point 3</li></ul>';
			$result = (string)$this->chain
				->ul()
					->li('Point 1')
					->li('Point 2')
						->ul_n1()
							->li_n1('Point 2.1')
							->li_n1('Point 2.2')
					->li('Point 3')
			;
			$this->assertEquals($result, $code);
		}

		public function testStartNewChain() {
			$code = '<strong>Hello</strong>';
			$result0 = (string)$this->chain->div('Something');
			$result = (string)$this->chain->strong('Hello');
			$this->assertEquals($code, $result);
		}

		public function testLinkAndScriptForHead() {
			$link = '<link href="css/style.css" />';
			$script = '<script src="js/test.js"></script>';
			
			$result = (string)$this->chain
				->link(array('href' => 'css/style.css'))
				->script(array('src' => 'js/test.js'))
			;
			$this->assertEquals($link.$script, $result);

			$result = (string)$this->chain
				->script(array('src' => 'js/test.js'))
				->link(array('href' => 'css/style.css'))
			;
			$this->assertEquals($script.$link, $result);

			$result = (string)$this->chain
				->script(array('src' => 'js/test.js'))
				->link(array('href' => 'css/style.css'))
				->link(array('href' => 'css/style.css'))
			;
			$this->assertEquals($script.$link.$link, $result);
		}

	}