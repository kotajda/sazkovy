<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

use Nette\Security\User;


class PrihlaseniPresenter extends BasePresenter
{
	private $databaze;
	public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }
    
	public function renderDefault()
	{
		$this->template->prihlaseni = 'true';
        //$this->template->vykreslit_formular = false;
	}

	protected function createComponentLoginForm()
	{
        $this->template->vykreslit_formular = true;

		$formular = new UI\Form;
        $uzivatel = $this->getUser();
        /*if(!$uzivatel->isLoggedIn())
        {*/
        $formular->addText("uzivatel", "Uživatel: ")->setAttribute("id", "uzivatel")->setAttribute("value", "xxx");
		$formular->addPassword("heslo", "Heslo: ")->setAttribute("id", "heslo");
		$formular->addSubmit("prihlasit", "Přihlásit")->setAttribute("class", "button expanded");
        $formular->onSuccess[] = [$this, "loginFormSucceeded"];
        $renderer = $formular->getRenderer();
        $this->template->zprava = "";
        //return $formular;
        /*}
        else
        {
            $this->template->zprava = "Uživatel ".$uzivatel->getIdentity()->getId()." je přihlášen";
            $this->template->vykreslit_formular = false;
        }*/
        return $formular;
	}

	public function loginFormSucceeded(UI\Form $form, $values)
    {
    	$jmeno = $values["uzivatel"];
    	$heslo = $values["heslo"];
    	$this->template->zprava = "xxx";
    	
        //$user = new User();
        $uzivatel = $this->getUser();

        $authenticator = new Nette\Security\SimpleAuthenticator([
            'uzivatel' => 'heslo',
        ]);
        $uzivatel->setAuthenticator($authenticator);

        try{
            if(!$uzivatel->isLoggedIn())
            {
                $uzivatel->login($jmeno,$heslo);
                $uzivatel->setExpiration('2 days', TRUE);
                $this->redirect("Homepage:");
            }
            else
            {
                $this->template->zprava = "Uživatel ".$uzivatel->getIdentity()->getId()." je přihlášen";
            }
        }
        catch (Nette\Security\AuthenticationException $e) 
        {
            $this->template->zprava = "Chyba: Přihlášení se nezdařilo.";
        }
        //$uzivatel->login($jmeno,$heslo);


    	/**$sql = "SELECT uzivatel, heslo FROM uzivatele WHERE uzivatel='".$uzivatel."' AND heslo='".sha1($heslo)."'";
    	$data = $this->databaze->fetch($sql);
    	//$data = true;
    	if($data)
    	{
            
    	}*/
    }
}