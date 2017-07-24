<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

use Tracy\Debugger;

class VysledkyPresenter extends BasePresenter
{
	/** @var Nette\Database\Context */
	private $databaze;

    private $moznosti;

    public function __construct(Nette\Database\Context $databaze)
	{
		$this->databaze = $databaze;
	}

	public function renderDefault()
	{
		$this->template->anyVariable = "any value";
	}
}