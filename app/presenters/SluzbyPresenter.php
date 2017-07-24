<?php

namespace App\Presenters;

use Nette;
use App\Model;


class SluzbyPresenter extends BasePresenter
{
	/*private $databaze;
	public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }*/

	public function renderDefault()
	{
		$this->template->nazvy_sluzeb = array();
		$this->template->popisy_sluzeb = array();
		$data = $this->databaze->query("SELECT nazev_sluzby, popis_sluzby FROM sluzby WHERE aktualnost=1");
		$i = 0;
		foreach ($data as $nabidky) 
		{
			$this->template->nazvy_sluzeb[$i] = $nabidky["nazev_sluzby"];
			$this->template->popisy_sluzeb[$i] = $nabidky["popis_sluzby"];
			$i++;
		}
	}

}
