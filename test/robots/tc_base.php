<?php
class TcBase extends TcAtk14Robot {

	function _setUp(){
		$this->dbmole->begin();
		$this->setUpFixtures();
	}

	function _tearDown(){
		$this->dbmole->rollback();
	}
}
