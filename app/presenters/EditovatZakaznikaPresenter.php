<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

class EditovatZakaznikaPresenter extends BasePresenter
{
	private $databaze;
    private $id_zakaznika;
	public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }

	public function renderDefault($id_zakaznika)
	{
        $uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }
        $this->id_zakaznika = $id_zakaznika;
	}

	protected function createComponentSelectForm()
	{
		$formular = new UI\Form;
        $this->id_zakaznika = $this->getParameter("id_zakaznika");
        $data = $this->databaze->fetch("SELECT jmeno, prijmeni, adresa FROM zakaznici WHERE id_zakaznika=".$this->id_zakaznika."");

		$formular->addText("jmeno", "Jméno: ")->setAttribute("id", "jmeno")->setDefaultValue($data["jmeno"]);
		$formular->addText("prijmeni", "Příjmení: ")->setAttribute("id", "prijmeni")->setDefaultValue($data["prijmeni"]);
		$formular->addTextArea("adresa", "Adresa: ")->setAttribute("id", "adresa")->setAttribute("rows", "3")->setDefaultValue($data["adresa"]);

        $data = $this->databaze->query("SELECT id_objednavky, datum_cas FROM objednavky WHERE zakaznik=".$this->id_zakaznika." AND datum_cas >= CURDATE()");
        $objednavky = array();
        $zaskrtnuto = array();
        foreach($data as $objednavka)
        {
            $objednavky[$objednavka["id_objednavky"]] = $objednavka["datum_cas"];
            //array_push($zaskrtnuto, true);
            array_push($zaskrtnuto, $objednavka["id_objednavky"]);
            //array_push($zaskrtnuto, $objednavka["id_objednavky"]);
        }

        $formular->addCheckboxList("objednavky", "Objednávky:",$objednavky)->setDefaultValue($zaskrtnuto);

		$formular->addSubmit("vlozit", "Editovat zákazníka")->setAttribute("class", "button expanded");
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
        $objednavky = $values["objednavky"];
    	//echo var_dump($objednavky);
    	//$this->databaze->query("UPDATE SET WHERE");


        /*$sql = "SELECT jmeno, prijmeni FROM zakaznici WHERE jmeno='".$jmeno."' AND prijmeni='".$prijmeni."'";
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
    	}*/
    }
}