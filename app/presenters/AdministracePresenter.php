<?php

namespace App\Presenters;

use Nette;
use App\Model;


class AdministracePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }
	}

}