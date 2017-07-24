<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

class VlozitCinnostPresenter extends BasePresenter
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

	protected function createComponentSelectForm()
	{
		$formular = new UI\Form;

		$formular->addText("nazev", "Název: ")->setAttribute("id", "nazev");
        $formular->addText("cena", "Cena: ")->setAttribute("id", "cena");
        //$formular->addText("popis", "Popis: ")->setAttribute("id", "popis");
        $formular->addTextArea("popis", "Popis: ")->setAttribute("id", "popis")->setAttribute("rows", "3");
        
		$formular->addSubmit("vlozit", "Vložit činnost")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        $renderer = $formular->getRenderer();
        //$this->template->zprava = "";
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
    	$nazev = $values["nazev"];
    	$popis = $values["popis"];
        $cena = $values["cena"];
    	$this->template->zprava = "";

        if((!is_string($nazev) || $nazev == "") || (!is_string($popis) || $popis == "") || !is_numeric($cena))
        {
            $this->template->zprava = "Špatně vyplněný formulář...";
            return 0;
        }

    	$sql = "SELECT nazev, popis FROM cinnosti WHERE nazev='".$nazev."'";
    	$data = $this->databaze->fetch($sql);
    	//$data = true;
    	if($data)
    	{
    		$this->template->zprava = "Činnost s tímto názvem už je v databázi...";
    	}
    	else
    	{
    		$sql = "INSERT INTO cinnosti (nazev, popis, cena) VALUES('".$nazev."','".$popis."','".$cena."')";
    		$this->databaze->query($sql);
    		$this->template->zprava = "Činnost byla vložen do databáze...";
    	}
    }
}