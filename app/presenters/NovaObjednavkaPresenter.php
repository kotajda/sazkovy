<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

use Nette\Utils\Html;

class NovaObjednavkaPresenter extends BasePresenter
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
		$this->template->anyVariable = 'any value';
	}

    public function actionEditovat($id_objednavky)
    {
        $uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }

        $data = $this->databaze->fetch("SELECT zakaznik, cinnosti, datum_cas FROM objednavky WHERE id_objednavky=".$id_objednavky."");
        $dataZakaznik = $this->databaze->fetch("SELECT id_zakaznika, jmeno, prijmeni FROM zakaznici WHERE id_zakaznika =".$data["zakaznik"]."");
        $cinnosti = explode(",", $data["cinnosti"]);
        $dataCinnosti = array();
        for($i=0;$i<count($cinnosti);$i++)
        {
            $dataCinnosti[$i] = $this->databaze->fetch("SELECT FROM cinnosti WHERE id_cinnosti=".$cinnosti[$i]."");
        }
        //$this

    }

	protected function createComponentSelectForm()
	{
		$formular = new UI\Form;

        $data = $this->databaze->query("SELECT id_zakaznika, jmeno, prijmeni FROM zakaznici");
        $zakaznici = array();
        foreach($data as $zakaznik)
        {
            $zakaznici[$zakaznik["id_zakaznika"]] = $zakaznik["jmeno"]." ".$zakaznik["prijmeni"];
        }
        $formular->addSelect("zakaznici","Zákazník:",$zakaznici)->setPrompt('Zvolte zákazníka');;

        $data = $this->databaze->query("SELECT id_cinnosti, nazev FROM cinnosti");
        $cinnosti = array();
        foreach($data as $cinnost)
        {
            //$cinnosti[$cinnost["id_cinnosti"]-1] = $cinnost["nazev"];
            $cinnosti[$cinnost["id_cinnosti"]] = Html::el('options', $cinnost["nazev"])->onClick("vyber_cinnosti('".$cinnost["id_cinnosti"]."','".$cinnost["nazev"]."');");
            //$moznostiPole[$moznost->id] = Html::el('options',  $moznost->competitionName . " - ".  $moznost->sport)->onClick("vyber_souteze(".$moznost->id.",'".$moznost->competitionName."','".$moznost->sport."');");

        }
        $formular->addMultiSelect("cinnosti","Cinnosti:",$cinnosti);

        $formular->addMultiSelect("vybrane_cinnosti")->setAttribute("id", "vybrane_cinnosti")->setAttribute("onBlur", "oznacit_vsechny(this);");

        $formular->addText("datum","Datum: ")->setAttribute("id", "datepicker");//->setAttribute("value", "Vyberte datum");
        //<input type="text" name="datum" id="datepicker" value="Vyberte datum">


        $hodiny = array();
        for($i=1;$i<=24;$i++)
        {
            $hodiny[$i-1] = $i;
        }
        $formular->addSelect("hodiny","Hodiny:",$hodiny)->setPrompt('Hodiny');;

        $minuty = array();
        for($i=1;$i<=60;$i++)
        {
            $minuty[$i-1] = $i;
        }
        $formular->addSelect("minuty","Minuty:",$minuty)->setPrompt('Minuty');;        
		
        $formular->addSubmit("objednat", "Nová objednávka")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        $renderer = $formular->getRenderer();
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
    	$zakaznik = $values["zakaznici"];
    	$cinnosti = $form["vybrane_cinnosti"]->getRawValue();
        $datum = $values["datum"]." ".($values["hodiny"]+1).":".($values["minuty"]+1).":00"; //kvůli indexu přidávám +1, jinak by tam bylo o hodinu méně

        if(($zakaznik == "" || !is_numeric($zakaznik)) || $cinnosti == array() || $values["datum"] == "" || $values["hodiny"] == "" || $values["minuty"] == "")
        {
            $this->template->zprava = "Špatně vyplněný formulář...";
            return 0;
        }


        $vybrane_cinnosti = "";
        for($i=0;$i<count($cinnosti);$i++)
        {
            if($vybrane_cinnosti != "")
            {
                $vybrane_cinnosti = $vybrane_cinnosti.",".$cinnosti[$i];
            }
            else
            {
                $vybrane_cinnosti = $cinnosti[$i];
            }
        }

        $this->databaze->query("INSERT INTO objednavky (zakaznik, cinnosti, datum_cas) VALUES ('".$zakaznik."','".$vybrane_cinnosti."','".$datum."')");
    	$this->template->zprava = "Nová objednávka byla vložena do databáze...";
    }
}