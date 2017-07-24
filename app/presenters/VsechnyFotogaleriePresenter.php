<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;
use Nette\Application\UI\Form;

use Nette\Forms\Controls;


class VsechnyFotogaleriePresenter extends BasePresenter
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

        $data = $this->databaze->query("SELECT id_galerie, nazev FROM fotogalerie");
        $this->template->nazvy_galerii = array();
        $this->template->id_galerii = array();
        $this->template->url_fotek = array();
        $i = 0;
        foreach($data as $fotogalerie)
        {
        	$this->template->nazvy_galerii[$i] = $fotogalerie["nazev"];
        	$this->template->id_galerii[$i] = $fotogalerie["id_galerie"];
        	//$data_fotky = $this->databaze->query("SELECT url_fotky FROM nahrane_fotky WHERE id_fotogalerie=".$fotogalerie["id_galerie"]);
            $data_fotky = $this->databaze->query("SELECT url_nahledu FROM nahrane_fotky_nahledy WHERE id_fotogalerie=".$fotogalerie["id_galerie"]);
        	$j = 0;
        	foreach($data_fotky as $fotka)
        	{
        		if($j == 0)
        		{
        			$this->template->url_fotek[$i] = $fotka["url_nahledu"];
        		}
        		else
        		{
        			$this->template->url_fotek[$i] = $this->template->url_fotek[$i].";".$fotka["url_nahledu"];
        		}
        		if($j == 2)
        		{
        			break;
        		}
        		$j++;
        	}
        	$i++;
        	$j = 0;
        }
	}
}