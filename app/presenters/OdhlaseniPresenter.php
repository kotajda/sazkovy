<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

use Nette\Security\User;


class OdhlaseniPresenter extends BasePresenter
{
	private $databaze;
	public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }
    
	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
        //$this->template->vykreslit_formular = false;

        $uzivatel = $this->getUser();
        if($uzivatel->isLoggedIn())
        {
            $uzivatel->logout();
            $this->redirect("Prihlaseni:");
        }
        else
        {
            $this->redirect("Prihlaseni:");
        }
	}
}