<?php

namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
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
		
		$sql = "SELECT id_objednavky, zakaznik, cinnosti, datum_cas FROM objednavky WHERE datum_cas >= CURDATE() AND datum_cas < CURDATE() + 1 ORDER BY datum_cas ASC";
		$data = $this->databaze->query($sql);
		$this->template->zakaznici = array();
		$this->template->cinnosti = array();
		$this->template->datumy = array();
		$this->template->id_objednavek = array();
		$this->template->i = 0;
		$this->template->ceny = array();

		$this->template->celkova_trzba = 0;
		foreach($data as $objednavka)
		{
			$data = $this->databaze->fetch("SELECT jmeno, prijmeni FROM zakaznici WHERE id_zakaznika=".$objednavka["zakaznik"]."");
			$this->template->zakaznici[$this->template->i] = $data["jmeno"]." ".$data["prijmeni"];
			$this->template->id_objednavek[$this->template->i] = $objednavka["id_objednavky"];
			$cinnosti = explode(",",$objednavka["cinnosti"]);
			//$this->template->cinnosti[$this->template->i] = "";
			for($i=0;$i<count($cinnosti);$i++)
			{
				$data = $this->databaze->fetch("SELECT nazev FROM cinnosti WHERE id_cinnosti=".($cinnosti[$i]*1)."");
				if($i != 0)
				{
					$this->template->cinnosti[$this->template->i] = $this->template->cinnosti[$this->template->i] . ", ".$data["nazev"];
					$this->template->ceny[$this->template->i] = $this->template->ceny[$this->template->i] + $data["cena"];
					$this->template->celkova_trzba = $this->template->celkova_trzba + $data["cena"];
				}
				else
				{
					$this->template->cinnosti[$this->template->i] = $data["nazev"];
					$this->template->ceny[$this->template->i] = $data["cena"];
					$this->template->celkova_trzba = $this->template->celkova_trzba + $data["cena"];
				}
			}

			//$this->template->datumy[$this->template->i] = $objednavka["datum_cas"];
			$this->template->datumy[$this->template->i] = date_format($objednavka["datum_cas"], 'd.m.Y  H:i');

			$this->template->i++;
		}
	}

}
