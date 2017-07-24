<?php

namespace App\Presenters;

use Nette;
use App\Model;

use Nette\Database\Context;
use Nette\Database\Connection;

class CinnostiPresenter extends BasePresenter
{
	private $databaze;
	public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }

	public function renderDefault()
	{
		$uzivatel = $this->getUser();
		if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }
		$sql = "SELECT id_cinnosti, nazev, popis, cena FROM cinnosti";
		$data = $this->databaze->query($sql);
		$this->template->nazvy = array();
		$this->template->popisy = array();
		$this->template->ceny = array();
		$this->template->id_cinnosti = array();
		$this->template->i = 0;
		foreach($data as $cinnost)
		{
			$this->template->nazvy[$this->template->i] = $cinnost["nazev"];
			$this->template->popisy[$this->template->i] = $cinnost["popis"];
			$this->template->ceny[$this->template->i] = $cinnost["cena"];
			$this->template->id_cinnosti[$this->template->i] = $cinnost["id_cinnosti"];
			$this->template->i++;
		}
	}
}
