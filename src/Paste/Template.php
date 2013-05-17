<?php

namespace Paste;

// template view wrapper, in order to easily change templating libraries
class Template {

	// template contents
	public $_template;

	// cache templates when possible
	protected static $cache = array();

	// template file extension
	protected static $ext = '.stache';
	
	// template directory relative to app path
	public static $dir = 'templates';

	// factory for method chaining. supply optional template name
	public static function factory($template = NULL) {

		// instantiate this class
		$tpl = new Template;

		// load template if supplied
		if (! empty($template))
			$tpl->set($template);

		// return Template instance
		return $tpl;

	}

	// set main template
	public function set($template) {

		// load template
		$this->_template = $this->load($template);

	}

	// get template file contents
	public function load($template) {

		// no template set
		if (empty($template))
			return;

		// ensure correct file extension
		$template = (strstr($template, self::$ext)) ? $template : $template.self::$ext;

		// check template cache
		if (! isset(self::$cache[$template])) {
			
			// directory where content files are stored
			$template_path = Paste::$path.Template::$dir.'/';

			// load template file and add to cache
			self::$cache[$template] = file_get_contents(realpath($template_path.$template));

		}

		return self::$cache[$template];

	}

	// TODO: make this an array of partials that gets combined on render
	// merge one template into another via the {{{content}}} string
	public function partial($partial) {

		$partial = $this->load($partial);

		$this->_template = str_replace('{{{content}}}', $partial, $this->_template);

	}

	// render the template with supplied page model
	public function render($page = NULL) {

		// a Page model with inherited template and partials
		if ($page instanceof Page) {

			// get defined page template, inherited from parent if necessary
			$page_template = $page->template();

			// setup main page template
			$this->set($page_template);

			// get defined page partial if available
			$page_partial = $page->partial();

			// combine templates if partial defined
			if (! empty($page_partial))
				$this->partial($page_partial);

		}
		
	
		// TODO: instantiate engine in constructor, use FilesystemLoader
		$mustache = new \Mustache_Engine(array(
			// 'loader' => new \Mustache_Loader_FilesystemLoader(Paste::$template_path, array('extension' => ".stache")),
			'loader' => new \Mustache_Loader_StringLoader,
		));
		
		$tpl = $mustache->loadTemplate($this->_template);
		// $tpl = $mustache->loadTemplate($page_template);
		echo $tpl->render($page);
		

		// instantiate Mustache view and render template
		// return (string) new Mustache($this->_template, $page);

	}

}