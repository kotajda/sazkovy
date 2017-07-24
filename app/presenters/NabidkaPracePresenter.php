<?php

namespace App\Presenters;

use Nette;
use App\Model;


class NabidkaPracePresenter extends BasePresenter
{
	/*private $databaze;
	public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }*/

	public function renderDefault()
	{
		$this->template->nazvy_pozic = array();
		$this->template->popisy_pozic = array();
		$data = $this->databaze->query("SELECT nazev_pozice, popis_pozice FROM nabidka_prace WHERE aktualni_nabidka=1");
		$i = 0;
		foreach ($data as $nabidky) 
		{
			$this->template->nazvy_pozic[$i] = $nabidky["nazev_pozice"];
			$this->template->popisy_pozic[$i] = $nabidky["popis_pozice"];
			$i++;
		}
	}

}
