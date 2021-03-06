<?php namespace Drafterbit\Extensions\User\Controllers;

use Drafterbit\Component\Validation\Exceptions\ValidationFailsException;
use Drafterbit\Extensions\System\BaseController;
use Carbon\Carbon;

class Admin extends BaseController {

	public function index()
	{
		$this->model('@user\Auth')->restrict('user.view');
		$userIds = $this->get('input')->post('users');

		if($userIds) {
			$action = $this->get('input')->post('action');

			switch($action) {
				case "Delete":
					foreach ($userIds as $id) {
						$this->model('@user\User')->delete($id);
					}
					message('Users deleted !', 'success');
					break;
				default:
					break;
			}
		}

		// get data
		$cache = $this->get('cache');
		if( ! $cache->contains('users')) {
			$cache->save('users', $this->model('@user\User')->all());
		}
		$users = $cache->fetch('users');

		foreach ($users as $user) {
			$user->groups = $this->model('@user\UsersGroup')->getByUser($user->id);
		}

		set('users', $users);
		set('id', 'users');
		set('title', __('Users'));
		set('usersTable', $this->datatables('users', $this->_table(), $users));

		return $this->render('@user/admin/index', $this->getData());
	}

	private function _table()
	{
		$editUrl = admin_url('user/edit');

		return array(
			['field' => 'real_name', 'label' => 'Name', 'format' => function($value, $item) use ($editUrl) {
					return "<a href='$editUrl/{$item->id}'>$value <i class='fa fa-edit'></i></a>"; }],
			['field' => 'email', 'label' => 'Email'],
			['field' => 'status', 'label' => 'Status', 'format' => function($value, $item) {
					return $value == 1 ? __('active') : __('blocked'); }],
			['field' => 'groups', 'label' => 'Group']
		);
	}

	private function _toolbarEdit()
	{
		return array(
			'new-post' => array(
				'type' => 'submit.success',
				'label' => 'Update',
				'name'=> 'action',
				'value' => 'update',
				'faClass' => 'fa-check'
			),
			'trash' => array(
				'type' => 'a',
				'href' => admin_url('user/create'),
				'label' => 'Delete',
				'faClass' => 'fa-trash-o'
			),
		);
	}

	public function create()
	{
		$this->model('@user\Auth')->restrict('user.add');
		$postData = $this->get('input')->post();

		if ($postData) {
			try {
				$this->validate('user', $postData);

				if($this->model('@user\User')->getByEmail($postData['email'])) {
					throw new ValidationFailsException('That email was registered.');
				}
				
				$data = $this->createInsertData($postData);
				$id = $this->model('@user\User')->insert($data);
				set('justSaved', true);

				//insert group
				$this->insertGroups( $postData['groups'], $id );

				if( isset($postData['send-password'])) {
					$this->sendPassword( $postData['email'], $postData['mail-message'], $postData['password']);
				}

				message('User saved !','success');

			} catch ( ValidationFailsException $e) {
				message($e->getMessage(), 'error');
			
			} catch ( \Swift_SwiftException $e) {

				$message = "User saved, but email was not sent due to error: {$e->getMessage()}. You probably can send it manually.";
				message($message, 'warning');
			}
		}

		$groups = $this->model('@user\UsersGroup')->all();
		set('groupOptions', $groups);
		set('id', 'pages-create');
		set('title', __('Create New Page'));

		return $this->render('@user/admin/create', $this->getData());		
	}

	public function edit($id = null)
	{
		$this->model('@user\Auth')->restrict('user.edit');

		$groups = $this->model('@user\UsersGroup')->all();

		$postData = $this->get('input')->post();

		if ($postData) {
			try {
				$this->validate('user', $postData);
				
				$data = $this->createUpdateData($postData);
				$this->model('@user\User')->update($data, array('id' => $id));

				//insert group
				$this->insertGroups( $postData['groups'], $id );

				message('User Updated !','success');

			} catch ( ValidationFailsException $e) {
				message($e->getMessage(), 'error');
			}
		}

		$user = $this->model('@user\User')->getSingleBy('id', $id);
		$user->groupIds = $this->model('@user\User')->getGroupIds($user->id);
		
		set([
			'groupOptions' => $groups,
			'realName' => $user->real_name,
			'email' => $user->email,
			'website' => $user->website,
			'bio' => $user->bio,
			'groupIds' => $user->groupIds,
			'active' => $user->status,
			'userId' => $user->id,
			'id' => 'pages-edit',
			'title' => __('Edit Pages')
		]);
	
		
		return $this->render('@user/admin/edit', $this->getData());
	}

	protected function insertGroups($groups, $id)
	{
		$this->model('@user\User')->clearGroups($id);

		foreach($groups as $group) {
			$this->model('@user\User')->insertGroup($group, $id);
		}
	}

	protected function createInsertData($post, $update = false)
	{
		$data = array();
		$data['email'] = $post['email'];
		
		if(isset($post['password']) and trim($post['password']) !== '') {
			$data['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
		}
		
		$data['bio'] = isset($post['bio']) ? $post['bio'] : null;
		$data['active'] = isset($post['active']) ? $post['active'] : 1;
		$data['website'] = isset($post['website']) ? $post['website'] : null;
		$data['real_name'] = isset($post['real-name']) ? $post['real-name'] : null;
		$data['updated_at'] = Carbon::Now();

		if( ! $update) {
			$data['created_at'] = Carbon::Now();
		}

		return $data;
	}

	protected function createUpdateData($post)
	{
		return $this->createInsertData($post, true);
	}

	protected function sendPassword($email, $messageBody, $password)
	{
		if( is_null($messageBody) or trim($messageBody) == '') {
			$messageBody = sprintf("this is your password: %s", $password);
		} else {
			$messageBody = sprintf($messageBody, $password);
		}

		$fromEmail = $this->get('config')->get('mail.from');

		$message = $this->get('mail')
			->setSubject('Registrar Confirmation')
			->setFrom($fromEmail)
			->setTo($email)
			->setBody($messageBody);
	
		return $this->get('mailer')->send($message);
	}
}