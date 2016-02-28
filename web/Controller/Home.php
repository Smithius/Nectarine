<?php

namespace Controller;

class Home {

	/**
	 * @Route()
	 */
	public function index() {
		return array();
	}

	/**
	 * @Route("blog/{id}")
	 */
	public function blog($id = null) {
		return array(
			'id' => $id,
		);
	}

}
