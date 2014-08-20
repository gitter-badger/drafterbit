<?php namespace Drafterbit\Admin\Controllers;

use Drafterbit\Admin\BaseController;

class Admin extends BaseController {
	
	public function dashboard()
	{
		$this->get('asset')->css($this->assetPath('css/index.css'));

		$header = $this->model('UI')->header('Dashboard', 'Statistic and more');
		$view = $this->render('admin/dashboard');

		return $this->wrap($header.$view);
	}
}