<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;


class EditovatNabidkyPracePresenter extends BasePresenter
{
	/*private $databaze;
	public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }*/

	public function renderDefault()
	{
		$uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }
		$this->template->nazvy_pozic = array();
		$this->template->popisy_pozic = array();
		$this->template->aktualnost_nabidek = array();
		$this->template->id_nabidek = array();
		$data = $this->databaze->query("SELECT id_pozice, nazev_pozice, popis_pozice, aktualni_nabidka FROM nabidka_prace");
		$i = 0;
		foreach ($data as $nabidky) 
		{
			$this->template->nazvy_pozic[$i] = $nabidky["nazev_pozice"];
			$this->template->popisy_pozic[$i] = $nabidky["popis_pozice"];
			$this->template->aktualnost_nabidek[$i] = $nabidky["aktualni_nabidka"];
			$this->template->id_nabidek[$i] = $nabidky["id_pozice"];
			$i++;
		}
	}

	protected function createComponentSelectForm()
	{
		$formular = new UI\Form;
		$formular->addText("nazev_pozice","Název pozice:")->setHtmlId("nazev_pozice");
		$formular->addTextArea("popis_pozice","Popis pozice:")->setAttribute("rows",10)->setHtmlId("popis_pozice");
		$formular->addRadioList("aktivovat","Aktivovat:",[ 0 => "Ne", 1 => "Ano",])->getSeparatorPrototype()->setName(NULL);
		$formular->addHidden("id_nabidky")->setHtmlId("id_nabidky");
		$formular->addSubmit("vlozit", "Vložit nebo aktualizovat pozici")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
    	$nazev_pozice = $values["nazev_pozice"];
    	$popis_pozice = $values["popis_pozice"];
    	$aktivovat = $values["aktivovat"];
		$id_nabidky = $values["id_nabidky"];

		if($nazev_pozice == "")
		{
			$this->template->zprava = "Nebyl vložen název pracovní pozice.";
			return 0;
		} 
		if($popis_pozice == "")
		{
			$this->template->zprava = "Nebyl vložen popis pracovní pozice.";
			return 0;
		}
		if(!is_numeric($aktivovat))
		{
			$this->template->zprava = "Nebyl vložen status pracovní pozice.";
			return 0;
		}		

		if($nazev_pozice != "" && $popis_pozice != "" && is_numeric($aktivovat))
		{
			$data["id_pozice"] = -1;
			if(!empty($id_nabidky))
			{
				$data = $this->databaze->fetch("SELECT id_pozice FROM nabidka_prace WHERE id_pozice=".$id_nabidky);
			}
	       	if($data["id_pozice"] != $id_nabidky)
	       	{
	        	$this->databaze->query("INSERT INTO nabidka_prace(nazev_pozice, popis_pozice, aktualni_nabidka) VALUES('".$nazev_pozice."','".$popis_pozice ."','".$aktivovat."')");
	       	}
	       	else
	      	{
	          	$this->databaze->query("UPDATE nabidka_prace SET nazev_pozice='".$nazev_pozice."', popis_pozice='".$popis_pozice."', aktualni_nabidka='".$aktivovat."' WHERE id_pozice=".$id_nabidky);
	       	}   
       	}	

    }

}