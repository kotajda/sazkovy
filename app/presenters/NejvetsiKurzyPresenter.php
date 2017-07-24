<?php

namespace App\Presenters;

use Nette;
use App\Model;

use Nette\Application\UI;
use Nette\Utils\Html;

use App\Model\NejKurzyManager;

/**
 * Base presenter for all application presenters.
 */
class NejvetsiKurzyPresenter extends BasePresenter
{
    private $databaze;

    private $moznosti;

    private $nej_kurzy;

    public function __construct(Nette\Database\Context $databaze, NejKurzyManager $nej_kurzy)
    {
        $this->databaze = $databaze;
        $this->nej_kurzy = $nej_kurzy;
    }

    protected function createComponentSelectForm()
    {
        $form = new UI\Form;
        $form = $this->nej_kurzy->vytvorFormular();
        $form->onSuccess[] = [$this, "selectFormSucceeded"];
        return $form;
    }

    public function selectFormSucceeded(UI\Form $form, $values)
    {
        $this->template->nabidky = array();
        $this->template->kurzy_pozice = array();
        $this->template->nabidky_kurzy = array();
        $this->template->nabidky_moznosti = array();
        $this->template->i = 0;
        $this->template->kurzy_pole = array();
        $this->template->nabidky_moznosti_id = array();
        $this->template->nabidky_moznosti_id_pole = array();
        $this->template->id_tiketu = array();
        $this->template->sazkovka = array();

        $this->nej_kurzy->zobrazit($form, $values, $this->template->nabidky, $this->template->kurzy_pozice, $this->template->nabidky_kurzy, $this->template->nabidky_moznosti, $this->template->i, $this->template->kurzy_pole, $this->template->nabidky_moznosti_id, $this->template->nabidky_moznosti_id_pole, $this->template->id_tiketu, $this->template->sazkovka, "DESC");
    }
}
