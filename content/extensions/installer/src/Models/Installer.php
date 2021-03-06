<?php namespace Drafterbit\Extensions\Installer\Models;

use Drafterbit\Framework\Model;

class Installer extends Model {
	
	public function createAdmin($email, $password)
	{
		$this->get('db')->insert('#_groups', ['label'=> 'Superadmin']);
		$groupId = $this->get('db')->lastInsertId();

		$user['email'] = $email;
		$user['password'] = password_hash($password, PASSWORD_BCRYPT);

		$this->get('db')->insert('#_users', $user);
		$userId = $this->get('db')->lastInsertId();

		
		$this->get('db')->insert('#_users_groups', [
			'user_id' => $userId,
			'group_id' => $groupId
		]);

		return array('userId' => $userId, 'groupId' => $groupId);
	}

	public function addPermission($extension, $permissions)
	{
		$q = "INSERT INTO #_permissions (slug,label, extension) ";
		$q .= "VALUES ";
		foreach ($permissions as $key => $value) {
			$q .= "('$key', '$value', '$extension'),";
		}

		$q = rtrim($q, ',').';';

		return $this->get('db')->executeUpdate($q);
	}

	public function addAdminpermission($groupId)
	{
		$permissions = $this->get('db')
			->createQueryBuilder()
			->select('id')
			->from('#_permissions','pms')
			->execute()->fetchAll(\PDO::FETCH_CLASS);

		$q = 'INSERT INTO #_groups_permissions (group_id, permission_id) ';
		$q .= "VALUES ";

		foreach ($permissions as $permission) {
			$q .= "('$groupId', '{$permission->id}'),";
		}

		$q = rtrim($q, ',').';';
		return $this->get('db')->executeUpdate($q);
	}

	public function systemInit($name, $desc, $email, $userId)
	{
		$page = $this->createDummyPage($userId);

		$data['site.name'] = $name;
		$data['site.description'] = $desc;
		$data['offline'] = 0;
		$data['offline.message'] = "Site is under construction";
		$data['email'] = $email;
		$data['language'] = 'english';
		$data['format.date'] = 'm dS Y';
		$data['format.time'] = 'H:m:s';
		$data['theme'] = 'default';
		$data['homepage'] = "pages[$page]";
		$data['extensions'] = "system,admin,pages,blog,user";

		$q = "INSERT INTO #_system (name, value) ";
		$q .= "VALUES ";

		foreach ($data as $key => $value) {
			$q .= "('$key', '$value'),";
		}

		$q = rtrim($q, ',').';';
		return $this->get('db')->executeUpdate($q);
	}

	public function createDummyPage($user)
	{
		$data['title'] = "Hello World";
		$data['slug'] = "hello-world";
		$data['content'] = "This is Hello World Page is to be edited or removed.";
		$data['user_id'] = $user;
		$data['created_at'] = date('Y-m-d H:m:s');
		$data['status'] = 1;

		$this->get('db')->insert('#_pages', $data);
		return $this->get('db')->lastInsertId();
	}
}