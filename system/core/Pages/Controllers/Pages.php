<?php namespace Drafterbit\Pages\Controllers;

use Drafterbit\Pages\Models\Page as PageModel;
use Drafterbit\Support\Controller as BaseController;

class Pages extends BaseController {

	protected $page;

	public function __construct( PageModel $page)
	{
		$this->page = $page;
	}

	public function index()
	{
		return $this->get('twig')->render('index.html');
	}

	public function view($slug = null)
	{
		$page = $this->page->getSingleBy('slug', $slug) or show_404();
		set('page', $page);
		return $this->get('twig')->render('page/view.html', $this->data);
	}
}
	