<?php
/**
 * @property SiteDao $dao
 *
 */
class SiteLogic extends Logic
{
	public function __construct()
	{
		$this->_dao = ObjectCreater::create('SiteDao');
	}

	

}
