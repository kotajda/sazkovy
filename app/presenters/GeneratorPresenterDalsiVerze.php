<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

use Nette\Utils\Html;

use Tracy\Debugger;


class GeneratorPresenterDalsiVerze extends BasePresenter
{
    /** @var Nette\Database\Context */
    private $databaze;

    private $moznosti;

    public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
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
            $moznostiPole[$moznost->id] = Html::el('options',  $moznost->competitionName . " - ".  $moznost->sport)->onClick("vyber_souteze(".$moznost->id.",'".$moznost->competitionName."','".$moznost->sport."');");

//echo "<option value=".$sport["id_souteze"]." onClick=\"vyber_souteze(".$sport["id_souteze"].",'".$sport["competitionName"]."','".$sport["sport"]."');\" >".$sport["competitionName"]." (".$sport["sport"].")</option>";           

            
        }

        $this->template->souteze = array();
        $this->template->souteze = $moznostiPole;

        //$form->addTextArea("moznosti", "")->setValue(var_dump($this->databaze->table("tipsport_kurzova_nabidka")));
        
        /*$form->addMultiSelect("souteze","",$moznostiPole)
            ->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);")
            ->addOptionAttributes(array("onClick" => "vyber_souteze(id_souteze, jmeno_souteze, sport)", "xxx" => "www"))
            ->addOptionAttributes(array("onClick" => "vyber_souteze(1, 2, 3)"));*/

        $form->addMultiSelect("souteze","", $moznostiPole)->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");
        
        $form->addMultiSelect("vybrane_souteze")
            ->setAttribute("id", "vybrane_souteze")
            ->setAttribute("onBlur", "oznacit_vsechny(this);")
            ->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");

        $form->addText("kurz", "Kurz:")
            ->setAttribute("style", "position:relative;top:25%;height:50%");

        $form->addSubmit("login", "Registrovat")
            ->setAttribute("style", "position:relative;top:25%;height:50%");

        $form->onSuccess[] = [$this, "selectFormSucceeded"];


        $renderer = $form->getRenderer();
        //$renderer->wrappers['moznosti']['size'] = 20;

        //echo var_dump($form->getRawValue());

        return $form;
    }

    // volá se po úspěšném odeslání formuláře
    public function selectFormSucceeded(UI\Form $form, $values)
    {
        // ...
        //$this->flashMessage("Byl jste úspěšně registrován.");

        //$souteze = $values["souteze"]; //uloží se vybrané ligy jako pole
        $souteze = $form["vybrane_souteze"]->getRawValue();


        $kurz = $values["kurz"]; //zvolený kurz


        //$this->template->form["vybrane_souteze"] = $souteze;
        //$form->addMultiSelect("xxx","", $form["vybrane_souteze"]);//->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");
        //echo var_dump($form->vybrane_souteze);

        $podminka = "";
        for($i=0;$i<count($souteze);$i++)
        {
          $podminka = $podminka."tipsport_zakladni_nabidka_zapasy.id_souteze=".$souteze[$i]." OR ";
        }
        $podminka = substr($podminka, 0, strlen($podminka)-4);
        $podminka;

        //$sql = "SELECT tipsport_kurzova_nabidka_zapasy.matchId as matchId, tipsport_kurzova_nabidka_moznosti.shortName as shortName, rate, name FROM tipsport_kurzova_nabidka, tipsport_kurzova_nabidka_zapasy, tipsport_kurzova_nabidka_moznosti WHERE (".$podminka.") AND tipsport_kurzova_nabidka_zapasy.matchId = tipsport_kurzova_nabidka_moznosti.matchId AND tipsport_kurzova_nabidka.id_souteze = tipsport_kurzova_nabidka_moznosti.id_souteze AND rate <= ".$kurz." ORDER BY rate";
        //dotazem si vytáhnu zápasy a také jejich příležitosti, které jsou menší než zvolený kurz


        $sql = "SELECT tipsport_zakladni_nabidka_zapasy.matchId as matchId, tipsport_zakladni_nabidka_moznosti.shortName as shortName, rate, name FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze AND rate <= ".$kurz." ORDER BY rate";



        //echo $sql2;


        $data = $this->databaze->query($sql);

        $vypis = "";
        $this->template->nabidky = array();
        $this->template->nabidky_kurzy = array();
        $this->template->nabidky_moznosti = array();
        $this->template->i = 0;
        $this->template->kurzy_pole = array();
        $this->template->nabidky_moznosti_id = array();
        $this->template->nabidky_moznosti_id_pole = array();
        $this->template->id_tiketu = array();
        $pom_id_tiketu = 1;

        $this->template->kurz_tiketu = 1;
        $kurz_tiketu = 1;

        $this->template->kurzy_tiketu = array();

        $this->template->kurzy_pozice = array();
        $this->template->kurz = $kurz;

        $zapasy_na_tiketu = array();
        

        $pom_nabidky = array();
        $pom_nabidky_kurzy = array();
        $pom_nabidky_moznosti = array();
        $pom_kurzy_pole = array();
        $pom_kurzy_pozice = array();
        $pom_prilezitost_rate = array();
        $pom_zapasy_na_tiketu = array();

        $pom_kurzy_pro_tiket = array();

        $pom_nabidky_moznosti_id = array();
        $pom_nabidky_moznosti_id_pole = array();

        $posledni_rate = 1;

        $pom_i = -1;

        $pom_index = 0;

        $posledni_kurz = 1;
        $doplneni = false;
        //echo $sql;
        foreach($data as $prilezitost) //projíždím zápasy odpovídající zvolenému nastavení  
        {
            //$kurz_tiketu = $kurz_tiketu * $prilezitost["rate"];
            
            //$sql = "SELECT sport, competitionName, tipsport_kurzova_nabidka.url as nabidka_url, tipsport_kurzova_nabidka_zapasy.matchId as matchId, name, tipsport_kurzova_nabidka_zapasy.url as zapasy_url, GROUP_CONCAT(tipsport_kurzova_nabidka_moznosti.shortName) as shortName, GROUP_CONCAT(tipsport_kurzova_nabidka_moznosti.rate) as rate FROM tipsport_kurzova_nabidka, tipsport_kurzova_nabidka_zapasy, tipsport_kurzova_nabidka_moznosti WHERE (".$podminka.") AND tipsport_kurzova_nabidka_zapasy.matchId = tipsport_kurzova_nabidka_moznosti.matchId AND tipsport_kurzova_nabidka.id_souteze = tipsport_kurzova_nabidka_moznosti.id_souteze AND tipsport_kurzova_nabidka_zapasy.matchId =".$prilezitost["matchId"]." GROUP BY matchId";
            //Dotaz by měl získat kompletní řádky do výpisu

            $sql = "SELECT sport, competitionName, tipsport_zakladni_nabidka_souteze.url as nabidka_url, tipsport_zakladni_nabidka_zapasy.matchId as matchId, name, tipsport_zakladni_nabidka_zapasy.url as zapasy_url, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.shortName) as shortName, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.rate) as rate, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.opportunityId) as opportunityId FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze AND tipsport_zakladni_nabidka_zapasy.matchId =".$prilezitost["matchId"]." GROUP BY matchId";

            if($kurz_tiketu < $kurz + 0.5)
            {
                if($doplneni) //doplňuje se na začátek tiketu
                {
                    echo var_dump($pom_nabidky)."<br /><br />";
                    for($i=0;$i<count($pom_nabidky);$i++)
                    {
                        array_push($this->template->nabidky, $pom_nabidky[$i]);
                        array_push($this->template->kurzy_pole, $pom_kurzy_pole[$i]);
                        array_push($this->template->kurzy_pozice,$pom_kurzy_pozice[$i]);
                        array_push($this->template->nabidky_moznosti_id_pole,$pom_nabidky_moznosti_id_pole[$i]);
                        $this->template->i++;
                        array_push($this->template->id_tiketu,$pom_id_tiketu);

                        //echo $kurz_tiketu."*".$pom_nabidky[$i]["rate"]." =".($kurz_tiketu * $pom_nabidky[$i]["rate"])."<br />";
                        //$kurz_tiketu = $kurz_tiketu * $pom_nabidky[$i]["rate"];
                        //echo $kurz_tiketu."*".doubleval($pom_nabidky_kurzy[$i])." =".($kurz_tiketu * doubleval($pom_nabidky_kurzy[$i]))."<br />";
                        //echo var_dump($pom_nabidky_kurzy[$i])."<br />";
                        //echo var_dump($pom_nabidky_kurzy)."<br />";
                        
                        //$kurz_tiketu = $kurz_tiketu * floatval($pom_nabidky_kurzy[$i]);
                        
                        //echo $kurz_tiketu." * ".$pom_kurzy_pro_tiket[$i]." = ".($kurz_tiketu * $pom_kurzy_pro_tiket[$i])."<br />";
                        $kurz_tiketu = $kurz_tiketu * $pom_kurzy_pro_tiket[$i];
                        //echo "-------------".$pom_kurzy_pro_tiket[$i]."<br />";
                    }

                    $doplneni = false;
                    $pom_nabidky = array();
                    $pom_nabidky_kurzy = array();
                    $pom_nabidky_moznosti = array();
                    $pom_kurzy_pole = array();
                    $pom_kurzy_pozice = array();
                    $pom_kurzy_pro_tiket = array();
                }

                $nabidky = $this->databaze->fetch($sql);                
                $nalezeno = in_array($prilezitost["matchId"], $zapasy_na_tiketu); //hledá, jestli už je zápas na tiketu
                if($nalezeno)  //když je zápas na tiketu, tak si ho uložím do pomocného pole, abych ho mohl naplnit do dalšího tiketu
                {
                    //echo $kurz_tiketu."*".$prilezitost["rate"]."=".($kurz_tiketu * $prilezitost["rate"])."<br />";
                    //$kurz_tiketu = $kurz_tiketu * $prilezitost["rate"];

                    array_push($pom_nabidky, $nabidky);
                    array_push($pom_kurzy_pozice, $prilezitost["shortName"]);
                    array_push($pom_nabidky_kurzy, explode(",", end($pom_nabidky)["rate"]));
                    array_push($pom_nabidky_moznosti, explode(",", end($pom_nabidky)["shortName"]));
                    array_push($pom_kurzy_pole, array_combine(end($pom_nabidky_moznosti), end($pom_nabidky_kurzy)));

                    array_push($pom_nabidky_moznosti_id, explode(",", end($pom_nabidky)["opportunityId"]));
                    array_push($pom_nabidky_moznosti_id_pole, array_combine(end($pom_nabidky_moznosti), end($pom_nabidky_moznosti_id)));

                    array_push($pom_kurzy_pro_tiket, $prilezitost["rate"]);
                    //$kurz_tiketu = $kurz_tiketu / $prilezitost["rate"];

                }
                else
                {
                    //echo $kurz_tiketu."*".$prilezitost["rate"]."=".($kurz_tiketu * $prilezitost["rate"])."<br />";
                    $kurz_tiketu = $kurz_tiketu * $prilezitost["rate"];

                    array_push($zapasy_na_tiketu, $prilezitost["matchId"]); //doplňuje se pole id, které jsou na tiketu, pro hledání, aby nebyly dvě stejné

                    //
                    array_push($this->template->nabidky, $nabidky);
                    array_push($this->template->kurzy_pozice, $prilezitost["shortName"]);
                    array_push($this->template->nabidky_kurzy, explode(",", end($this->template->nabidky)["rate"]));
                    array_push($this->template->nabidky_moznosti, explode(",", end($this->template->nabidky)["shortName"]));
                    array_push($this->template->kurzy_pole, array_combine(end($this->template->nabidky_moznosti), end($this->template->nabidky_kurzy)));

                    array_push($this->template->nabidky_moznosti_id, explode(",", end($this->template->nabidky)["opportunityId"]));
                    array_push($this->template->nabidky_moznosti_id_pole, array_combine(end($this->template->nabidky_moznosti), end($this->template->nabidky_moznosti_id)));
                    $this->template->i++;
                    array_push($this->template->id_tiketu,$pom_id_tiketu);
                }
                $posledni_kurz = $prilezitost["rate"];              
            }
            else
            { //toto je, když se dostane na konec jednoho tiketu a už je v pořadí zápas z dalšího
                //echo $kurz_tiketu."/".$posledni_kurz." = ";
                
                //$kurz_tiketu = $kurz_tiketu / $prilezitost["rate"];
                //echo $kurz_tiketu."<br />";
                //$kurz_tiketu = $kurz_tiketu / $pom_kurzy_pro_tiket[count($pom_kurzy_pro_tiket)-1];
                
                $kurz_tiketu = $kurz_tiketu / $posledni_kurz;
                
                //echo $kurz_tiketu."<br />";

                $this->template->kurzy_tiketu[$this->template->i - 2] = $kurz_tiketu;
                $pom_id_tiketu++;
                //$kurz_tiketu = $prilezitost["rate"];
                $kurz_tiketu = 1;

                $this->template->id_tiketu[count($this->template->id_tiketu) - 1] = $pom_id_tiketu;

                array_push($pom_nabidky,array_pop($this->template->nabidky));
                array_push($pom_kurzy_pozice,array_pop($this->template->kurzy_pozice));
                array_push($pom_nabidky_kurzy,array_pop($this->template->nabidky_kurzy));
                array_push($pom_nabidky_moznosti,array_pop($this->template->nabidky_moznosti));
                array_push($pom_kurzy_pole,array_pop($this->template->kurzy_pole));
                array_push($pom_nabidky_moznosti_id, array_pop($this->template->nabidky_moznosti_id));
                array_push($pom_nabidky_moznosti_id_pole, array_pop($this->template->nabidky_moznosti_id_pole));
                $this->template->i--;



                /*array_push($pom_nabidky, $this->databaze->fetch($sql));
                array_push($pom_kurzy_pozice, $prilezitost["shortName"]);
                array_push($pom_nabidky_kurzy, explode(",", end($pom_nabidky)["rate"]));
                array_push($pom_nabidky_moznosti, explode(",", end($pom_nabidky)["shortName"]));
                array_push($pom_kurzy_pole, array_combine(end($pom_nabidky_moznosti), end($pom_nabidky_kurzy)));
*/
                //vytvářím id pro označení tiketu
                /*array_push($pom_nabidky_moznosti_id, explode(",", end($pom_nabidky)["opportunityId"]));
                array_push($pom_nabidky_moznosti_id_pole, array_combine(end($pom_nabidky_moznosti), end($pom_nabidky_moznosti_id)));*/

                array_push($pom_kurzy_pro_tiket, $prilezitost["rate"]);

                $doplneni=true;
            }





            //STARÁ VERZE:
            /*array_push($this->template->nabidky, $this->databaze->fetch($sql));
            $nalezeno = false;
            if($nalezeno)
            {
                
            }
            else
            {

                $nalezeno = array_search($prilezitost["name"], $this->template->nabidky);
                Debugger::barDump($nalezeno)."<br />";

                if(!$nalezeno)
                {
                    Debugger::barDump(end($this->template->nabidky)["matchId"]);
                    array_push($this->template->nabidky_kurzy, explode(",", end($this->template->nabidky)["rate"]));
                    array_push($this->template->nabidky_moznosti, explode(",", end($this->template->nabidky)["shortName"]));
                    array_push($this->template->kurzy_pole, array_combine(end($this->template->nabidky_moznosti), end($this->template->nabidky_kurzy)));
                    array_push($this->template->kurzy_pozice, $prilezitost["shortName"]);
                    $kurz_tiketu = $kurz_tiketu * $prilezitost["rate"];
                    $posledni_rate = $prilezitost["rate"];
                    for($i=0;$i<count($pom_prilezitost_rate);$i++)
                    {
                        if($kurz_tiketu < $kurz)
                        {
                            $kurz_tiketu = $kurz_tiketu / $posledni_rate;
                            $posledni_rate = array_shift($pom_prilezitost_rate);
                            echo $kurz_tiketu." * ".$posledni_rate." = ";
                            $kurz_tiketu = $kurz_tiketu * $posledni_rate;
                            echo $kurz_tiketu."<br />";
                            //Debugger::barDump($posledni_rate);
                        }
                    }
                    if($kurz_tiketu >= $kurz)
                    {
                        $kurz_tiketu = $kurz_tiketu / $posledni_rate;
                        $this->template->kurzy_tiketu[$this->template->i - 1] = $kurz_tiketu;
                        $kurz_tiketu = $prilezitost["rate"];
                        for($i=0;$i<count($pom_kurzy_pole);$i++)
                        {
                            array_push($this->template->nabidky,array_shift($pom_nabidky));
                            array_push($this->template->kurzy_pole, array_shift($pom_kurzy_pole));
                            array_push($this->template->kurzy_pozice, array_shift($pom_kurzy_pozice));                            
                        }

                        $pom_nabidky = array();
                        $pom_nabidky_kurzy = array();
                        $pom_nabidky_moznosti = array();
                        $pom_kurzy_pole = array();
                        $pom_kurzy_pozice = array();
                    }
                    $this->template->i++;
                }

                $nalezeno = false;
            }*/
        }
        //echo var_dump($this->template->id_tiketu);
        //echo var_dump($this->template->kurzy_tiketu);

    }

    public function renderDefault()
    {
        $this->template->anyVariable = "any value";
        /*$this->template->moznosti = $this->moznosti;
        echo "xxx".var_dump($this->template->moznosti);*/
    }



}
