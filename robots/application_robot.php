<?php
/**
 * The base class for every other robot.
 */
class ApplicationRobot extends Atk14Robot{
	function before_run(){
		$this->dbmole->begin();
	}

	function after_run(){
		$this->dbmole->commit();
	}
}
