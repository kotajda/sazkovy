<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;


class PrehledZpravPresenter extends BasePresenter
{

	public function renderDefault()
	{
		$uzivatel = $this->getUser();
        if(!$uzivatel->isLoggedIn())
        {
            $this->redirect("Prihlaseni:");
        }

        $this->template->jmena = array();
        $this->template->telefony = array();
        $this->template->emaily = array();
        $this->template->zpravy = array();
        $this->template->datum_cas = array();
        $this->template->id_zpravy = array();
        $this->template->stavy = array();
        $data = $this->databaze->query("SELECT id_zpravy, datum_cas, jmeno_prijmeni, telefon, email, zprava, stav FROM zpravy ORDER BY id_zpravy DESC");
        $i=0;
        foreach($data as $zprava)
        { 
            $datum_cas = date_parse($zprava["datum_cas"]);
            $this->template->datum_cas[$i] = $datum_cas["day"].". ".$datum_cas["month"].". ".$datum_cas["year"]." ".$datum_cas["hour"].":".$datum_cas["minute"];
            $this->template->jmena[$i] = $zprava["jmeno_prijmeni"];
            $this->template->telefony[$i] = $zprava["telefon"];
            $this->template->emaily[$i] = $zprava["email"];
            $this->template->zpravy[$i] = $zprava["zprava"];
            $this->template->id_zpravy[$i] = $zprava["id_zpravy"];
            $this->template->stavy[$i] = $zprava["stav"];
            $i++;
        }

	}

    protected function createComponentSelectForm()
    {
        $formular = new UI\Form;
        $operace = [ "precteno" => "Přečteno", "smazat" => "Smazat"];
        $formular->addSelect("operace","Operace",$operace)->setPrompt("Událost");
        $formular->addHidden("id_zpravy")->setHtmlId("id_zpravy");
        $formular->addSubmit("provest", "Provést")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        return $formular;
    }

    public function selectFormSucceeded(UI\Form $form, $values)
    {
        $operace = $values["operace"];
        $id_zpravy = $values["id_zpravy"];
        if($operace == "precteno")
        {
            $this->databaze->query("UPDATE zpravy SET stav='precteno' WHERE id_zpravy=".$id_zpravy);
        }
        else if($operace == "smazat")
        {
            $this->databaze->query("DELETE FROM zpravy WHERE id_zpravy=".$id_zpravy);
        }
    }
}
