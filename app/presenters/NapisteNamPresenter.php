<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;


class NapisteNamPresenter extends BasePresenter
{

	public function renderDefault()
	{

	}

	protected function createComponentSelectForm()
	{
		$data = $this->databaze->fetch("SELECT id_kontaktu, mobil, pevna_linka, fax, email, ulice, mesto, psc FROM kontaktni_udaje");
		$formular = new UI\Form;
		$formular->addText("jmeno_prijmeni","Mobil:");
		$formular->addText("telefon","Pevná linka:");
		$formular->addText("email","Email:");
		$formular->addTextArea("zprava","Popis pozice:")->setAttribute("rows",10)->setHtmlId("popis_pozice");
		$formular->addSubmit("odeslat", "Odeslat zprávu")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "selectFormSucceeded"];
        return $formular;
	}

	public function selectFormSucceeded(UI\Form $form, $values)
    {
    	$jmeno_prijmeni = $values["jmeno_prijmeni"];
    	$telefon = $values["telefon"];
    	$email = $values["email"];
    	$zprava = $values["zprava"];
    	if($jmeno_prijmeni == "")
    	{
    		$this->template->zprava = "Vložte vaše jméno";
    		return 0;
    	}
    	if($telefon == "" && $email == "")
    	{
    		$this->template->zprava = "Vložte telefon nebo email";
    		return 0;
    	}
    	if($zprava == "")
    	{
    		$this->template->zprava = "Vložte zprávu.";
    		return 0;
    	}
    	$this->databaze->query("INSERT INTO zpravy(jmeno_prijmeni, telefon, email, zprava,stav) VALUES('".$jmeno_prijmeni."','".$telefon."','".$email."','".$zprava."','')");

        if(!is_numeric($telefon))
        {
            $telefon = "Telefon nebyl vložen";
        }
        $mail = new Message;
        //$email->setFrom("Něco <asdf@jjjjj.cz>")
        $mail->setFrom($jmeno_prijmeni." <".$email.">")
        ->addTo("kotajda@gmail.com")
        ->setSubject("Máte novou zprávu")
        ->setHtmlBody("<p>Na stavitelstvi-sindler.cz byla zaznamenána nová zpráva:</p><p>Autor zprávy: ".$jmeno_prijmeni."</p><p>Telefon: ".$telefon."</p><p>Email: ".$email."</p> <p>".$zprava."</p>");

        $odeslat = new SendmailMailer;
        $odeslat->send($mail);

    	$this->template->zprava = "Zpráva byla odeslána.";
    }
}