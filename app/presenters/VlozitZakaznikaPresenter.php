<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

class VlozitZakaznikaPresenter extends BasePresenter
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

		$formular->addText("jmeno", "Jméno: ")->setAttribute("id", "jmeno");
		$formular->addText("prijmeni", "Příjmení: ")->setAttribute("id", "prijmeni");
		$formular->addTextArea("adresa", "Adresa: ")->setAttribute("id", "adresa")->setAttribute("rows", "3");
		$formular->addSubmit("vlozit", "Vložit zákazníka")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        $renderer = $formular->getRenderer();
        //$this->template->zprava = "";
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
    	$jmeno = $values["jmeno"];
    	$prijmeni = $values["prijmeni"];
    	$adresa = $values["adresa"];
    	$this->template->zprava = "xxx";

        if($jmeno == "" || $prijmeni == "")
        {
            $this->template->zprava = "Špatně vyplněný formulář...";
            return 0;
        }

    	
    	$sql = "SELECT jmeno, prijmeni FROM zakaznici WHERE jmeno='".$jmeno."' AND prijmeni='".$prijmeni."'";
    	$data = $this->databaze->fetch($sql);
    	//$data = true;
    	if($data)
    	{
    		$this->template->zprava = "Zákazník s tímto jménem a příjmením už je v databázi...";
    	}
    	else
    	{
    		$sql = "INSERT INTO zakaznici (jmeno, prijmeni, adresa) VALUES('".$jmeno."','".$prijmeni."','".$adresa."')";
    		$this->databaze->query($sql);
    		$this->template->zprava = "Zákazník byl vložen do databáze...";
    	}
    }
}