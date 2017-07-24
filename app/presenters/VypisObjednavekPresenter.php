<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

class VypisObjednavekPresenter extends BasePresenter
{
	private $databaze;
	private $zakladni_vypis = true;
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

		if($this->zakladni_vypis)
		{
		$sql = "SELECT id_objednavky, zakaznik, cinnosti, datum_cas FROM objednavky WHERE datum_cas >= CURDATE() ORDER BY datum_cas ASC";
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
				$data = $this->databaze->fetch("SELECT nazev, cena FROM cinnosti WHERE id_cinnosti=".($cinnosti[$i]*1)."");
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

	protected function createComponentSelectForm()
	{
		$formular = new UI\Form;        
		$formular->addText("datum_od","Datum od: ")->setAttribute("id", "datepicker1")->setAttribute("value", "Vyberte datum od");
        $formular->addText("datum_do","Datum do: ")->setAttribute("id", "datepicker2")->setAttribute("value", "Vyberte datum do");
        $formular->addSubmit("vyber", "Vyber")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        $renderer = $formular->getRenderer();
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
    	
    	$datum_od = $values["datum_od"];
    	$datum_do = $values["datum_do"];

    	$this->template->zakaznici = array();
		$this->template->cinnosti = array();
		$this->template->datumy = array();
		$this->template->id_objednavek = array();
		$this->template->ceny = array();

		$this->template->celkova_trzba = 0;

		$this->template->i = 0;
		$vypis = false;
		if($datum_od == "" && $datum_do == "")
		{
			$this->zakladni_vypis = true;
		}
		else if($datum_od != "" && $datum_do == "")
		{
			$datum_do = "2037-12-31";
			$vypis = true;
		}
		else if($datum_od == "" && $datum_do != "")
		{
			$datum_od = "2016-01-01";
			$vypis = true;
		}
		else
		{
			$vypis = true;
		}

		if($vypis)
		{
	    	if(strtotime($datum_od) < strtotime($datum_do))
	    	{
	    		$data = $this->databaze->query("SELECT id_objednavky, zakaznik, cinnosti, datum_cas FROM objednavky WHERE datum_cas >= '".$datum_od."' AND datum_cas <= '".$datum_do."' ORDER BY datum_cas ASC");
	    		foreach($data as $objednavka)
	    		{
	    			$data = $this->databaze->fetch("SELECT jmeno, prijmeni FROM zakaznici WHERE id_zakaznika=".$objednavka["zakaznik"]."");
					$this->template->zakaznici[$this->template->i] = $data["jmeno"]." ".$data["prijmeni"];
					$this->template->id_objednavek[$this->template->i] = $objednavka["id_objednavky"];

	    			$cinnosti = explode(",",$objednavka["cinnosti"]);
					//$this->template->cinnosti[$this->template->i] = "";
					for($i=0;$i<count($cinnosti);$i++)
					{
						$data = $this->databaze->fetch("SELECT nazev, cena FROM cinnosti WHERE id_cinnosti=".($cinnosti[$i]*1)."");
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

					$this->template->datumy[$this->template->i] = date_format($objednavka["datum_cas"], 'd.m.Y  H:i');
					//$this->template->datumy[$this->template->i] = date_format(date_create_from_format('Y-m-d H:i:s', $objednavka["datum_cas"]), 'd.m.Y  H:i:s') . "xxx";

					$this->template->i++;
	    		}
	    	}
	    	$this->zakladni_vypis = false;
		}
   	}
}