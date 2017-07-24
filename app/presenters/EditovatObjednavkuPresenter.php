<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

use Nette\Utils\Html;

class EditovatObjednavkuPresenter extends BasePresenter
{
	private $databaze;
    private $id_objednavky;
	public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }

	public function renderDefault($id_objednavky)
	{
        $uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }
        $this->id_objednavky = $id_objednavky;
		
	}

    public function actionEditovat($id_objednavky)
    {
        //echo "yyyyyyyyyyyy".$id_objednavky;
        
    }

	protected function createComponentSelectForm()
	{
        //echo "xxxxxxxxxxxxxxxx".$this->id_objednavky;
        

        $this->id_objednavky = $this->getParameter("id_objednavky");
        $data = $this->databaze->fetch("SELECT zakaznik, cinnosti, datum_cas FROM objednavky WHERE id_objednavky=".$this->id_objednavky."");
        $dataZakaznik = $this->databaze->fetch("SELECT id_zakaznika, jmeno, prijmeni FROM zakaznici WHERE id_zakaznika =".$data["zakaznik"]."");
        $cinnosti = explode(",", $data["cinnosti"]);
        $datum = explode(" ", $data["datum_cas"])[0];
        $cas = explode(" ", $data["datum_cas"])[1];
        //echo var_dump($cinnosti)."<br /><br />";
        $dataCinnosti = array();
        for($i=0;$i<count($cinnosti);$i++)
        {
            $dataCinnosti[$i] = $this->databaze->fetch("SELECT id_cinnosti, nazev FROM cinnosti WHERE id_cinnosti='".$cinnosti[$i]."'");
        }
        $vybrane_cinnosti = array();
        for($i=0;$i<count($dataCinnosti);$i++)
        {
                //$vybrane_cinnosti[$dataCinnosti[$i]["id_cinnosti"]] = $dataCinnosti[$i]["nazev"];
                $vybrane_cinnosti[$dataCinnosti[$i]["id_cinnosti"]] = Html::el('options', $dataCinnosti[$i]["nazev"])->onClick("smazat_cinnost('".$dataCinnosti[$i]["id_cinnosti"]."');");

        }

		
        $formular = new UI\Form;
        $data = $this->databaze->query("SELECT id_zakaznika, jmeno, prijmeni FROM zakaznici");
        $zakaznici = array();
        foreach($data as $zakaznik)
        {
            $zakaznici[$zakaznik["id_zakaznika"]] = $zakaznik["jmeno"]." ".$zakaznik["prijmeni"];
        }
        
        $formular->addSelect("zakaznici","Zákazník:",$zakaznici)->setPrompt('Zvolte zákazníka')->setDefaultValue($dataZakaznik["id_zakaznika"]);

        $data = $this->databaze->query("SELECT id_cinnosti, nazev FROM cinnosti");
        $cinnosti = array();
        foreach($data as $cinnost)
        {
            //$cinnosti[$cinnost["id_cinnosti"]-1] = $cinnost["nazev"];
            $cinnosti[$cinnost["id_cinnosti"]] = Html::el('options', $cinnost["nazev"])->onClick("vyber_cinnosti('".$cinnost["id_cinnosti"]."','".$cinnost["nazev"]."');");
            //$moznostiPole[$moznost->id] = Html::el('options',  $moznost->competitionName . " - ".  $moznost->sport)->onClick("vyber_souteze(".$moznost->id.",'".$moznost->competitionName."','".$moznost->sport."');");

        }
        $formular->addMultiSelect("cinnosti","Cinnosti:",$cinnosti);

        $formular->addMultiSelect("vybrane_cinnosti", "Vybrané činnosti",$vybrane_cinnosti)->setAttribute("id", "vybrane_cinnosti")->setAttribute("onBlur", "oznacit_vsechny(this);");
        //$formular->addMultiSelect("vybrane_cinnosti")->setAttribute("id", "vybrane_cinnosti")->setAttribute("onBlur", "oznacit_vsechny(this);");


        $formular->addText("datum","Datum: ")->setAttribute("id", "datepicker")->setAttribute("value", "Vyberte datum")->setDefaultValue($datum);
        //<input type="text" name="datum" id="datepicker" value="Vyberte datum">

        $hodina = explode(":", $cas)[0]*1;
        $minuta = explode(":", $cas)[1]*1;
        $hodiny = array();
        for($i=1;$i<=24;$i++)
        {
            $hodiny[$i-1] = $i;
        }
        $formular->addSelect("hodiny","Hodiny:",$hodiny)->setPrompt('Hodiny')->setDefaultValue($hodina);

        $minuty = array();
        for($i=1;$i<=60;$i++)
        {
            $minuty[$i-1] = $i;
        }
        $formular->addSelect("minuty","Minuty:",$minuty)->setPrompt('Minuty')->setDefaultValue($minuta);       
		
        $formular->addSubmit("objednat", "Editovat objednávku")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        $renderer = $formular->getRenderer();
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
        $this->id_objednavky = $this->getParameter("id_objednavky");

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
        //echo "objednavka je: ".$this->id_objednavky."<br />";
        $this->databaze->query("UPDATE objednavky SET zakaznik='".$zakaznik."', cinnosti='".$vybrane_cinnosti."', datum_cas='".$datum."' WHERE id_objednavky='".$this->id_objednavky."'");
        //$this->databaze->query("INSERT INTO objednavky (zakaznik, cinnosti, datum_cas) VALUES ('".$zakaznik."','".$vybrane_cinnosti."','".$datum."')");
    	$this->template->zprava = "Objednávka byla aktualizována vložena do databáze...";
    }
}