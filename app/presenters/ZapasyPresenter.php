<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

use Nette\Utils\Html;

use Tracy\Debugger;


class ZapasyPresenter extends BasePresenter
{
    /** @var Nette\Database\Context */
    private $databaze;

    private $moznosti;

    public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }

    public function renderDefault()
    {
        //$this->template->anyVariable = "any value";
        /*$this->template->moznosti = $this->moznosti;
        echo "xxx".var_dump($this->template->moznosti);*/
        //$this->template->cislo = 1;
        
        //$this->template->_form = $this['selectForm'];
        $this->template->getLatte()->addProvider('formsStack', [$this["selectForm"]]);

    }

    protected function createComponentSelectForm()
    {
        $form = new UI\Form;

        $moznosti = $this->databaze->query("SELECT id, sport, competitionName FROM tipsport_zakladni_nabidka_souteze");
        //$this->moznosti = $moznosti;
        $moznostiPole = array();

        //$option = array();

        foreach ($moznosti as $moznost) 
        {
            //$moznostiPole[$moznost->id_souteze] = $moznost->competitionName . " - ".  $moznost->sport;
            //$moznostiPole[$moznost->id_souteze]["onClick"] = "funkce(1)";
            //$moznostiPole[$moznost->id] = Html::el('options',  $moznost->competitionName . " - ".  $moznost->sport)->onClick("vyber_souteze(".$moznost->id.",'".$moznost->competitionName."','".$moznost->sport."');");
            $moznostiPole[$moznost->id] = Html::el('options',  $moznost->competitionName . " - ".  $moznost->sport);

//echo "<option value=".$sport["id_souteze"]." onClick=\"vyber_souteze(".$sport["id_souteze"].",'".$sport["competitionName"]."','".$sport["sport"]."');\" >".$sport["competitionName"]." (".$sport["sport"].")</option>";           

            
        }

        $this->template->souteze = array();
        $this->template->souteze = $moznostiPole;

        //$form->addTextArea("moznosti", "")->setValue(var_dump($this->databaze->table("tipsport_kurzova_nabidka")));
        
        /*$form->addMultiSelect("souteze","",$moznostiPole)
            ->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);")
            ->addOptionAttributes(array("onClick" => "vyber_souteze(id_souteze, jmeno_souteze, sport)", "xxx" => "www"))
            ->addOptionAttributes(array("onClick" => "vyber_souteze(1, 2, 3)"));*/

        //$form->addMultiSelect("souteze","", $moznostiPole)->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");
        $form->addSelect("souteze","", $moznostiPole)->setPrompt("Vyberte si ligu");//->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");
        
        /*$form->addSelect("vybrane_souteze")
            ->setAttribute("id", "vybrane_souteze")
            ->setAttribute("onBlur", "oznacit_vsechny(this);")
            ->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");*/
        $form->addSelect("vybrany_zapas")
            ->setAttribute("id", "vybrany_zapas");
            //->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");

            //->setAttribute("style", "position:relative;top:25%;height:50%");

        $form->addSubmit("zobrazit", "Zobrazit")->setAttribute("class", "button expanded");
            //->setAttribute("style", "position:relative;top:25%;height:50%");

        //$form->setMethod("GET");

        $form->onSuccess[] = [$this, "selectFormSucceeded"];

        $renderer = $form->getRenderer();
        //$renderer->wrappers['moznosti']['size'] = 20;

        //echo var_dump($form->getRawValue());
        //$this->template->id_tiketu = 0;
        return $form;
    }

    // volá se po úspěšném odeslání formuláře
    public function selectFormSucceeded(UI\Form $form, $values)
    {
        //$casovac = time();
        $values = $form->getHttpData();
        unset($values['send']);
        //dump($values);

        $data = $this->databaze->query("SELECT matchId, name FROM tipsport_zakladni_nabidka_zapasy WHERE id_souteze=".$values["souteze"]);
        $zapasy = array();
        foreach($data as $zapas)
        {
            $zapasy[$zapas["matchId"]] = $zapas["name"];
        }
        $this['selectForm']['vybrany_zapas']->setItems($zapasy);

        $id_zapasu = $values["vybrany_zapas"];
        $this->template->id_zapasu = $id_zapasu;

        $this->template->nazev_souteze = $this->databaze->fetch("SELECT sport, competitionName FROM tipsport_zakladni_nabidka_souteze WHERE id=".$values["souteze"]);

        $nazev_zapasu = $this->databaze->fetch("SELECT name FROM tipsport_zakladni_nabidka_zapasy WHERE matchId=".$id_zapasu);
        $nazev_zapasu = $nazev_zapasu["name"];
        $this->template->nazev_zapasu = $nazev_zapasu;
        //echo "Zvolený název: ".$pom_nazev."<hr />";

        $nazev_zapasu = explode("-", $nazev_zapasu);
        //$sql = "SELECT nazev_zapasu FROM fortuna_nabidka_zapasy WHERE nazev_zapasu LIKE '%".$nazev_zapasu[0]."%".$nazev_zapasu[1]."%'";

        
        $sql_tipsport = "SELECT GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.shortName) as nazev_moznosti, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.rate) as kurz_moznosti FROM tipsport_zakladni_nabidka_moznosti WHERE matchId=".$id_zapasu." GROUP BY matchId";

        $data = $this->databaze->fetch($sql_tipsport);
        $this->template->kurzy = array();
        $nazev_moznosti = explode(",",$data["nazev_moznosti"]);
        $kurz_moznosti = explode(",",$data["kurz_moznosti"]);
        $this->template->kurzy["tipsport"] = array_combine($nazev_moznosti, $kurz_moznosti);

        $sql_fortuna = "SELECT nazev_zapasu, GROUP_CONCAT(nazev_moznosti) AS nazev_moznosti, GROUP_CONCAT(kurz_moznosti) AS kurz_moznosti, fortuna_nabidka_sazky_moznosti.id_sazky 
                        FROM fortuna_nabidka_zapasy, fortuna_nabidka_sazky, fortuna_nabidka_sazky_moznosti 
                        WHERE fortuna_nabidka_zapasy.id_zapasu = fortuna_nabidka_sazky.id_zapasu AND fortuna_nabidka_sazky.id_zapasu = fortuna_nabidka_sazky_moznosti.id_zapasu AND fortuna_nabidka_sazky.id_sazky = fortuna_nabidka_sazky_moznosti.id_sazky AND
                    nazev_zapasu LIKE '%".$nazev_zapasu[0]."%".$nazev_zapasu[1]."%' AND nazev_sazky='zápas' AND nazev_moznosti != '12'
                        GROUP BY id_sazky";
        //echo $sql_fortuna."<br />";
        $data = $this->databaze->fetch($sql_fortuna);
        /*foreach ($data as $zapas) 
        {
            echo "Původní název: ".$pom_nazev." --- Našlo se: ".$zapas["nazev_zapasu"]."<br />";
        }*/
        
        $nazev_moznosti = explode(",",$data["nazev_moznosti"]);
        $kurz_moznosti = explode(",",$data["kurz_moznosti"]);
        $this->template->kurzy["fortuna"] = array_combine($nazev_moznosti, $kurz_moznosti);


        //$sql = "SELECT userName, authorProfileLink, tip, rate, url, text, matchName FROM  tipsport_analyzy WHERE ;
        $sql = "SELECT userName, authorProfileLink, tip, rate, url, text, matchName FROM  tipsport_analyzy, tipsport_jmena_zapasu WHERE tipsport_analyzy.matchId = tipsport_jmena_zapasu.matchId AND tipsport_analyzy.matchId=".$id_zapasu." AND tipsport_jmena_zapasu.matchId=".$id_zapasu;
        //echo $sql;
        //$sql = "SELECT userName, authorProfileLink, tip, rate, url, text, matchName FROM  tipsport_analyzy, tipsport_jmena_zapasu WHERE tipsport_analyzy.matchId = tipsport_jmena_zapasu.matchId";
        
        $analyzy = $this->databaze->query($sql);

        $this->template->data = array();
        $i = 0;
        foreach($analyzy as $data)
        {
            $this->template->data[$i] = $data;
            $i++;
        }

        //echo $id_zapasu;
         // třeba toto id 2433519 
       /* $sql = "SELECT id_tiketu FROM tipsport_tiketarena_sazky WHERE matchId=".$id_zapasu;
        $tikety = $this->databaze->query($sql);*/
        
        $this->template->data_tiket = array();
        $this->template->data_zapasy = array();
        $i = 0;
      /* foreach ($tikety as $tiket) 
        {
        */
        //$sql = "SELECT tipsport_tiketarena.id_tiketu as id_tiketu, userName, author, rate, amountPaid, potentialWin, currency, url FROM tipsport_tiketarena JOIN tipsport_tiketarena_sazky ON matchId=".$id_zapasu." WHERE tipsport_tiketarena.id_tiketu = tipsport_tiketarena_sazky.id_tiketu AND currency = 'CZE'";
        
        /*$sql = "SELECT tipsport_tiketarena.id_tiketu, userName, author, rate, amountPaid, potentialWin, currency, url 
                FROM tipsport_tiketarena 
                JOIN tipsport_tiketarena_sazky ON matchId= 2433519
                WHERE tipsport_tiketarena.id_tiketu = tipsport_tiketarena_sazky.id_tiketu
                AND currency = 'CZK'";*/

        $sql = "SELECT tipsport_tiketarena.id_tiketu as id_tiketu, userName, author, rate, amountPaid, potentialWin, currency, url 
                FROM tipsport_tiketarena 
                JOIN tipsport_tiketarena_sazky ON matchId=".$id_zapasu." 
                WHERE tipsport_tiketarena.id_tiketu = tipsport_tiketarena_sazky.id_tiketu
                AND currency = 'CZK'";

            //$sql = "SELECT id_tiketu, userName, author, rate, amountPaid, potentialWin, currency, url FROM tipsport_tiketarena WHERE currency = 'CZK' AND id_tiketu=".$tiket["id_tiketu"];
        //3026
            $tikety2 = $this->databaze->query($sql);
            //$this->template->title = "xxx";
            //echo "y";
            
            foreach($tikety2 as $data)
            {
                //echo "x";
                //echo $data["arenaId"]." --- ".$data["rate"]."<br />";
                //echo "tiket: ".$data["id_tiketu"]."<br />";
                $this->template->data_tiket[$i] = $data;
                $sql2 = "SELECT eventName, opportunityfullName FROM tipsport_tiketarena_sazky WHERE id_tiketu = ".$data["id_tiketu"];
                //SELECT eventName, opportunityfullName FROM tipsport_tiketarena_sazky WHERE id_tiketu = 2884
                $zapasy = $this->databaze->query($sql2);
                $j=0;
                foreach($zapasy as $data_zapasy)
                {
                    //echo $data["id_tiketu"]."<br />";
                    //$this->template->data_zapasy[$i][$data["id_tiketu"]] = $data_zapasy;
                    //echo $data_zapasy["eventName"]." ------ ".$data_zapasy["opportunityfullName"];
                    $this->template->data_zapasy[$data["id_tiketu"]][$j] = $data_zapasy;
                    $j++;
                }
                //echo "<hr />";
                //echo "i: ".$i." --- j: ".$j."<br />";
                $i++;
            }
            //dump($this->template->data_zapasy);
            //echo $i."<br />";
            //echo "doba trvani je: ".(time() - $casovac);
        //}


        //$this->redirect('this', array('vybrany_zapas' => $id_zapasu));
    }

    public function handleKlikSouteze($value)
    {
        $data = $this->databaze->query("SELECT matchId, name FROM tipsport_zakladni_nabidka_zapasy WHERE id_souteze=".$value);

        $zapasy = array();
        foreach($data as $zapas)
        {
            $zapasy[$zapas["matchId"]] = $zapas["name"];
        }
        $this['selectForm']['vybrany_zapas']->setPrompt("Vyberte si zápas")->setItems($zapasy);
        //$this['selectForm']['vybrane_souteze']->setPrompt("Vyberte si zápas")->setItems($zapasy)->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");
            
        //$this->redrawControl('wrapper');
        $this->redrawControl('vybrane');
    }
}