<?php namespace Drafterbit\Extensions\Pages\Models;

class Pages extends \Drafterbit\Framework\Model {

	public function all($status = 'untrashed')
	{
		if($this->get('debug')) {
			return $this->doGetAll($status);
		}

		$cache = $this->get('cache');
		if( ! $cache->contains('pages.'.$status)) {
			$cache->save('pages.'.$status, $this->doGetAll($status));
		}

		return $cache->fetch('pages.'.$status);
	}


	private function doGetAll($status)
	{
		$query = $this->withQueryBuilder() ->select('*') ->from('#_pages','p');

		if($status == 'trashed') {
			$query->where('p.deleted_at != :deleted_at');
			$query->setParameter(':deleted_at', '0000-00-00 00:00:00');			
		} else {

			$query->Where('p.deleted_at = :deleted_at');
			$query->setParameter(':deleted_at', '0000-00-00 00:00:00');

			if($status !== 'untrashed'){
				$query->andWhere('p.status = :status');
				$s = $status == 'published' ? 1 : 0;
				$query->setParameter(':status', $s);
			}
		}

		return $query->fetchAllObjects();
	}

	public function insert($data)
	{
		$this->get('db')->insert('#_pages', $data);
		return $this->get('db')->lastInsertId();
	}

	public function update($data, $id)
	{
		return
		$this->get('db')->update('#_pages', $data, array('id' => $id));
	}

	public function getBy($key, $value = null, $singleRequested=false)
	{
		$queryBuilder = $this->get('db')->createQueryBuilder();
		$stmt = $queryBuilder->select('*')->from('#_pages', 'p');

		if (is_array($key)) {
		
			foreach ($key as $k => $v) {
	            $holder = ":$k";
	            $queryBuilder->where("$k = $holder")
	               ->setParameter($holder, $v);
        	}
		
		} else {
			
			$queryBuilder->where("$key = :$key")
			->setParameter(":$key", $value);
		}

		$pages = $stmt->execute()->fetchAll(\PDO::FETCH_CLASS);

		if($singleRequested) {
			return reset($pages);
		}

		return $pages;
	}

	public function getSingleBy($key, $value = null)
	{
		return $this->getBy($key, $value, true);
	}

	/**
	 * Delete pages permanently
	 *
	 * @param array $ids
	 * @return void
	 */
	public function delete($ids)
	{
		$ids = (array) $ids;
		$ids = array_map(function($v){return "'$v'";}, $ids);
		$idString = implode(',', $ids);

		$this->withQueryBuilder()
		->delete('#_pages')
		->where('id IN ('.$idString.')')
			->execute();

		$this->clearCache();
	}

	/**
	 * Trash pages by given ids
	 *
	 * @param array $ids
	 * @return void
	 */
	public function trash($ids)
	{
		$ids = array_map(function($v){return "'$v'";}, $ids);
		$idString = implode(',', $ids);
		$deleted_at = new \Carbon\Carbon;

		$this->withQueryBuilder()
			->update('#_pages', 'p')
			->set('deleted_at',"'$deleted_at'")
			->where('p.id IN ('.$idString.')')
			->execute();

		$this->clearCache();
	}

	/**
	 * Restore trashed pages
	 *
	 * @return void
	 */
	public function restore($ids)
	{
		$ids = array_map(function($v){return "'$v'";}, $ids);

		$idString = implode(',', $ids);
		$deleted_at = new \Carbon\Carbon;

		$this->withQueryBuilder()
			->update('#_pages', 'p')
			->set('deleted_at',"'0000-00-00 00:00:00'")
			->where('p.id IN ('.$idString.')')
			->execute();

		$this->clearCache();
	}

	/**
	 * Clear stored data cache
	 *
	 * @return void
	 */
	private function clearCache()
	{
		$cache = $this->get('cache');

		foreach (['published', 'unpulished', 'trashed'] as $part) {
			$cache->delete('pages.'.$part);
		}
	}
}