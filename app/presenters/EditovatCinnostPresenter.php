<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

class EditovatCinnostPresenter extends BasePresenter
{
	private $databaze;
    private $id_cinnosti;
	public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }

	public function renderDefault($id_cinnosti)
	{
        $uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }
		$this->template->id_cinnosti = $id_cinnosti;
	}

	protected function createComponentSelectForm()
	{
        $this->id_cinnosti = $this->getParameter("id_cinnosti");
        $data = $this->databaze->fetch("SELECT nazev, popis, cena FROM cinnosti WHERE id_cinnosti=".$this->id_cinnosti."");


		$formular = new UI\Form;

		$formular->addText("nazev", "Název: ")->setAttribute("id", "nazev")->setDefaultValue($data["nazev"]);
        $formular->addText("cena", "Cena: ")->setAttribute("id", "cena")->setDefaultValue($data["cena"]);
        //$formular->addText("popis", "Popis: ")->setAttribute("id", "popis");
        $formular->addTextArea("popis", "Popis: ")->setAttribute("id", "popis")->setAttribute("rows", "3")->setDefaultValue($data["popis"]);
        
		$formular->addSubmit("vlozit", "Aktualizovat činnost")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        $renderer = $formular->getRenderer();
        //$this->template->zprava = "";
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
        $this->id_cinnosti = $this->getParameter("id_cinnosti");

    	$nazev = $values["nazev"];
    	$popis = $values["popis"];
        $cena = $values["cena"];
    	$this->template->zprava = "";

        if((!is_string($nazev) || $nazev == "") || (!is_string($popis) || $popis == "") || !is_numeric($cena))
        {
            $this->template->zprava = "Špatně vyplněný formulář...";
            return 0;
        }

        $this->databaze->query("UPDATE cinnosti SET nazev='".$nazev."', popis='".$popis."', cena='".$cena."' WHERE id_cinnosti=".$this->id_cinnosti."");
        $this->template->zprava = "Činnost byla aktualizována...";
    	/*$sql = "SELECT nazev, popis FROM cinnosti WHERE nazev='".$nazev."'";
    	$data = $this->databaze->fetch($sql);
    	//$data = true;
    	if($data)
    	{
    		$this->template->zprava = "Činnost s tímto názvem už je v databázi...";
    	}
    	else
    	{
    		/*$sql = "INSERT INTO cinnosti (nazev, popis, cena) VALUES('".$nazev."','".$popis."','".$cena."')";
    		$this->databaze->query($sql);*/
           /* $this->databaze->query("UPDATE cinnosti SET nazev='".$nazev."', popis='".$popis."', cena='".$cena."' WHERE id_cinnosti=".$this->id_cinnosti."");
    		$this->template->zprava = "Činnost byla aktualizovánas...";
    	}*/
    }
}