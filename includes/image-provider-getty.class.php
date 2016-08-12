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
class Getty_Image_Provider extends Image_Provider {

	public function __construct() {

		$this->tab_name = 'Getty Images Tester';
		//add_action( 'print_media_templates', array( $this, 'output_templates' ) );
		$this->run();

	}

	//public function output_template() {}

}