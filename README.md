# The HtmlChain

The HtmlChain is a small class for creating code with PHP:

	$html = new HtmlChain();
	echo $html->div();

Will output:

	<div></div>

Okay, thats very borring. But you can chain the elements! So, whats about this:

	echo $html
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

With this result:

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
	</html>


## Named Div Container

The Chain recognize next elements and try to close it for you:

	echo $html->div()->div();

Will result in:

	<div></div>
	<div></div>

If you like to nest the div containers, e.g. for a gridlayout, just pass a classname, seperated with underscore.

	echo $html
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

The result is a perfect structured, right indented html output:

	<div class="container">
		<div class="row head">
			<div class="grid_6"></div>
			<div class="grid_6"></div>
		</div>
		<div class="row body">
			<div class="grid_4"></div>
			<div class="grid_4"></div>
			<div class="grid_4"></div>
		</div>
		<div class="row footer">
			<div class="grid_12"></div>
		</div>
	</div>

Sometimes there are problems, e.g.:

	echo $html->h1('Hello')->p('World');

Results in:
	
	<h1>Hello<p>World</p></h1>

But there are a solution, its named `end()` (or `close()` with the shortcut `c()`):

	echo $html->h1('Hello')->end()->p('World');

And you get:

	<h1>Hello</h1>
	<p>World</p>



## Tags, Content and Attributes

With the magic `__call()` function you can use nearly every tag as a nativ function. So start with:

	echo $html->form();
	//or
	echo $html->table()->tr()->td('Cell 1')->td('Cell 2');

Pass a string as content and/or an array with attributes:

	echo $html->div('Hello', array('rel' => 'home'));
	//alternativ
	echo $html->div('Hello', ['rel' => 'home']); //hello php 5.4, yeah!!

Output:

	<div rel="home">Hello</div>

## Special Tags

There are a few special tags for faster code creation:

	$html->doctype(); //<!DOCTYPE html>
	$html->mailto('info@domain.com'); //<a href="mailto:info@domain.com">info@domain.com</a>
	$html->utf8(); //<meta charset="utf-8">

## Licence

Its just an example and a proof of concept. So its copyrighted by me. If you want to use it, just drop me a line.

**Check the PHPUnit Tests for more examples!**