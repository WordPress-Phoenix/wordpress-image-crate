<?php

/**
 * Abstract WP_Trapper_Keeper Image Provider Class
 *
 * Implemented from abstract factory pattern.
 *
 * @version  0.1.1
 * @package  WP_Trapper_Keeper
 * @category Abstract Class
 * @author   justintucker
 */
class USAToday_Image_Provider extends Image_Provider {

	public function __construct() {

		$this->tab_name = 'USAToday Images Tester';
		$this->run();

	}

}