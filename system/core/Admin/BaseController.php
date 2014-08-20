<?php namespace Drafterbit\Admin;

use Drafterbit\Support\Controller;
use Drafterbit\User\Models\Auth;

class BaseController extends Controller {

	protected $baseTemplate;

	public function __construct( Auth $auth )
	{
		$this->auth = $auth;
		$this->auth->authenticate();
		$session = $this->get('session');

		//flash message
		$message = $session->getFlashBag()->get('message');

		app('dispatcher')->addListener('controller.before.call', function() use ($message) {
				
				if(isset($message['text'], $message['type']))
				app('current.controller')->message($message['text'], $message['type']);
		});

		// assets
		$adminCSS = $this->module('admin')->getResourcesPath().'public/css/';
		$adminJs = $this->module('admin')->getResourcesPath().'public/js/';
		$this->get('asset')
			->css('@fontawesome', '@fontawesome')
			->css('@bootstrapcss')
			->css('@toastrcss')
			->css($adminCSS.'overrides-toastr.css')
			->css($adminCSS.'overrides-bootstrap.css')
			->css($adminCSS.'overrides-datatables.css')
			->css($adminCSS.'style.css')
	
			->js('@jquery')
			->js('@bootstrapjs')
			->js('@toastrjs')
			->js($adminJs.'layout.js')
			->js($adminJs.'app.js');
	}

	private function menu()
	{
		return array(
			['label' => 'Dashboard', 'href' => admin_url()],
			['label' => 'Content', 'children' => [
					[ 'label' => 'Pages', 'href' => admin_url('pages')],
					// Pending
					// [ 'label' => 'Files', 'href' => base_url('admin/files')],
					[ 'label' => 'Blog', 'href' => admin_url('blog')]
				],
			],
			['label' => 'Users', 'children' => [
					[ 'label' => 'User', 'href' => admin_url('user')],
					[ 'label' => 'Group', 'href' => admin_url('user/group')]
				]
			],
			['label' => 'Setting', 'children' => [
					[ 'label' => 'General', 'href' => admin_url('setting/general')],
					[ 'label' => 'Themes', 'href' => admin_url('setting/themes')],
				]
			],
			['label' => 'System', 'children' => [
					[ 'label' => 'Log', 'href' => admin_url('system/log')],
					[ 'label' => 'Cache', 'href' => admin_url('system/cache')],
				]
			],
			['label' => 'Help', 'children' => [
					[ 'label' => 'Help', 'href' => 'javascript:alert(\'Coming Soon !\')'],
					[ 'label' => 'Doc wiki', 'href' => 'javascript:alert(\'Coming Soon !\')'],
					[ 'label' => 'Official Support', 'href' => 'javascript:alert(\'Coming Soon !\')']
				]
			],
		);
	}

	public function buildTemplate()
	{
		$content = parent::view();

		//$partials['nav'] = $this->get('template')->render('partials/nav@admin', array());

		//gravatar
		$session = $this->get('session');

		$hash = md5(strtolower($session->get('user.email')));
		$url = "http://www.gravatar.com/avatar/$hash?d=mm&s=17";
		$userName = $session->get('user.name');
		$userGravatar = $url;
		
		$nav = $this->model('UI@admin')->nav($this->menu(), $userName, $userGravatar);
		$partials['nav'] = $nav;

		$this->set('content', $content);
		$this->set('partials', $partials);
		return $this->get('template')->render('base@admin', $this->data);
	}

	public function view()
	{
		if( strtolower($this->get('input')
					->server('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest') {
			
			// @todo append js on demand
			return parent::view();
		}

		if(!isset($this->data['messages'])) {
            $this->data['messages'] = false;
        }

		$jsFileName = $this->get('asset')->writeJs();
		$fileName = $this->get('asset')->writeCSS();
		
		$this->data['stylesheet'] = base_url('admin/asset/css/'.$fileName.'.css');
		$this->data['script'] = base_url('admin/asset/js/'.$jsFileName.'.js');

		return $this->buildTemplate();
	}

	public function wrap($content)
	{
		if(!isset($this->data['messages'])) {
            $this->data['messages'] = false;
        }

		$jsFileName = $this->get('asset')->writeJs();
		$fileName = $this->get('asset')->writeCSS();
		
		$this->data['stylesheet'] = base_url('admin/asset/css/'.$fileName.'.css');
		$this->data['script'] = base_url('admin/asset/js/'.$jsFileName.'.js');

		//gravatar
		$session = $this->get('session');
		$hash = md5(strtolower($session->get('user.email')));
		$url = "http://www.gravatar.com/avatar/$hash?d=mm&s=17";
		$userName = $session->get('user.name');
		$userGravatar = $url;
		
		$nav = $this->model('UI@admin')->nav($this->menu(), $userName, $userGravatar);
		$footer = $this->model('UI@admin')->footer();
		$partials['nav'] = $nav;
		$partials['footer'] = $footer;

		$this->set('content', $content);
		$this->set('partials', $partials);
		return $this->get('template')->render('base@admin', $this->data);
	}

	/**
     * Add Message.
     *
     * @param string $text
     * @param string $type
     * @param string $title
     */
    public function message($text, $type = 'info', $title = null)
    {
        $message = array();
        $message['text'] = $text;
        $message['type'] = $type;
        $message['title'] = $title;

        if(!isset($this->data['messages'])) {
            $this->data['messages'] = array();
        }

        return array_push( $this->data['messages'], $message);
    }
}