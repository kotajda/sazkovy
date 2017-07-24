<?php

namespace App\Presenters;

use Nette;
use App\Model;

use Nette\Database\Context;
use Nette\Database\Connection;

class ZakazniciPresenter extends BasePresenter
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
        
		$sql = "SELECT id_zakaznika, jmeno, prijmeni, adresa FROM zakaznici ORDER BY prijmeni";
		$data = $this->databaze->query($sql);
		$this->template->jmena = array();
		$this->template->prijmeni = array();
		$this->template->adresy = array();
		$this->template->objednavky = array();
		$this->template->id_zakazniku = array();
		$this->template->i = 0;
		foreach($data as $zakaznik)
		{
			$this->template->jmena[$this->template->i] = $zakaznik["jmeno"];
			$this->template->prijmeni[$this->template->i] = $zakaznik["prijmeni"];
			$this->template->adresy[$this->template->i] = $zakaznik["adresa"];
			$this->template->id_zakazniku[$this->template->i] = $zakaznik["id_zakaznika"];
			$dataObj = $this->databaze->query("SELECT datum_cas FROM objednavky WHERE zakaznik=".$zakaznik["id_zakaznika"]." AND datum_cas > CURDATE() ORDER BY datum_cas ASC");
			$this->template->objednavky[$this->template->i] = "";
			foreach($dataObj as $objednavka)
			{
				if($this->template->objednavky[$this->template->i] == "")
				{
					$this->template->objednavky[$this->template->i] = date_format($objednavka["datum_cas"], 'd.m.Y  H:i');
				}
				else
				{
					$this->template->objednavky[$this->template->i] = $this->template->objednavky[$this->template->i].", ".date_format($objednavka["datum_cas"], 'd.m.Y  H:i');
				}
			}
			$this->template->i++;
		}
	}
}
