<?php

namespace Model;

use Doctrine\Common\Collections\ArrayCollection as Collection;

/**
 * @Entity
 */
class Example {

	/**
	 * @Column(type="integer")
	 * @Id
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * 1 - obecny, 2 - nieobecny, ...
	 * @Column(type="integer", nullable=false) 
	 */
	protected $presence;

}
