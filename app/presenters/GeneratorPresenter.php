<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI;

use Nette\Database\Context;
use Nette\Database\Connection;

use Nette\Utils\Html;

use Tracy\Debugger;

use App;
use App\Model\GeneratorManager;

class GeneratorPresenter extends BasePresenter
{
    /** @var Nette\Database\Context */
    private $databaze;

    private $moznosti;

    /** @var GeneratorManager */
    private $generator_manager;

    public function __construct(Nette\Database\Context $databaze, GeneratorManager $generator_manager)
    {
        $this->databaze = $databaze;
        $this->generator_manager = $generator_manager;
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

        $sazkovky = ["tipsport" => "Tipsport", "fortuna" => "Fortuna"];
        $form->addRadioList("sazkovka","Sázková kancelář",$sazkovky);//->getSeparatorPrototype()->setName(NULL);

        $form->addSubmit("generovat", "Generuj tikety")
            ->setAttribute("class", "button")
            ->setAttribute("style", "position:relative;top:25%;height:50%");

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
        $souteze = $form["vybrane_souteze"]->getRawValue(); //zvoleé soutěže
        $kurz = $values["kurz"]; //zvolený kurz
        $sazkovka = $values["sazkovka"];

        $podminka = "";
        $podminka_fortuna = "";
        for($i=0;$i<count($souteze);$i++)
        {
          $podminka = $podminka."tipsport_zakladni_nabidka_zapasy.id_souteze=".$souteze[$i]." OR ";
          $podminka_fortuna = $podminka_fortuna."tipsport_zakladni_nabidka_souteze.id=".$souteze[$i]." OR ";
        }
        $podminka = substr($podminka, 0, strlen($podminka)-4); //vytvoří se podmínka ze soutěží pro výběr z databáze
        $podminka_fortuna = substr($podminka_fortuna, 0, strlen($podminka_fortuna)-4);

        $this->template->nabidky = array();
        $this->template->kurzy_pozice = array(); 
        $this->template->nabidky_kurzy = array();
        $this->template->nabidky_moznosti = array();
        $this->template->kurzy_pole = array();
        
        $this->template->nabidky_moznosti_id_pole = array();
        $nabidky_moznosti_id = array();
        $pom_nabidky_moznosti_id_pole = array();
        $pom_nabidky_moznosti_id = array();
        
        //vytvořím si pomocné pole, do kterých si dám  duplicity z tiketu, které budou na druhém tiketu...
        $pom_nabidky = array();
        $pom_kurzy = array();
        $pom_kurzy_pozice = array(); 
        $pom_nabidky_kurzy = array();
        $pom_nabidky_moznosti = array();
        $pom_kurzy_pole = array();

        $this->template->i = 0;
        $this->template->id_tiketu = array(); //id jednotlivých vygenerovaných tiketů
        $id_tiketu = 1;
        $this->template->kurz_tiketu = array(); //kurzy pro jednotlivé vygenerované tikety
        $kurz_tiketu = 1; //průběžný kurz tiketu
        $this->template->tiket = array(); //určuje, kde končí každý jednotlivý tiket... tam, kde je zapsána 1, je pozice konce tiketu
        $nalezeno = false; //určuje, jestli je daný zápas na tiketu
        $zapasy_na_tiketu = array();
        
        if($sazkovka == "tipsport")
        {
            $this->generator_manager->generatorTipsport($podminka, $nabidky, $prilezitost, $kurz, $this->template->nabidky, $this->template->kurzy_pozice, $this->template->nabidky_kurzy, $this->template->nabidky_moznosti, $this->template->kurzy_pole,$this->template->nabidky_moznosti_id_pole,$this->template->i,$this->template->id_tiketu,$this->template->kurz_tiketu, $this->template->tiket, $nabidky_moznosti_id, $pom_nabidky_moznosti_id_pole, $pom_nabidky_moznosti_id, $pom_nabidky, $pom_kurzy, $pom_kurzy_pozice, $pom_nabidky_kurzy, $pom_nabidky_moznosti, $pom_kurzy_pole, $id_tiketu, $kurz_tiketu, $nalezeno, $zapasy_na_tiketu);
        }
        else if($sazkovka == "fortuna")
        {
            $this->generator_manager->generatorFortuna($podminka_fortuna, $nabidky, $prilezitost, $kurz, $this->template->nabidky, $this->template->kurzy_pozice, $this->template->nabidky_kurzy, $this->template->nabidky_moznosti, $this->template->kurzy_pole,$this->template->nabidky_moznosti_id_pole,$this->template->i,$this->template->id_tiketu,$this->template->kurz_tiketu, $this->template->tiket, $nabidky_moznosti_id, $pom_nabidky_moznosti_id_pole, $pom_nabidky_moznosti_id, $pom_nabidky, $pom_kurzy, $pom_kurzy_pozice, $pom_nabidky_kurzy, $pom_nabidky_moznosti, $pom_kurzy_pole, $id_tiketu, $kurz_tiketu, $nalezeno, $zapasy_na_tiketu);
        }

        $this->template->tiket[$this->template->i-1] = 1;
        $this->template->kurz_tiketu[$this->template->i-1] = $kurz_tiketu;
        
        $this->template->mensiMoznost = array();
        $this->template->vetsiMoznost = array();

        //dump($this->template);
    }

    //private $mensiMoznost;
    //private $vetsiMoznost;

    public function handleDalsiMoznosti($id_tiketu, $zapasy, $oznaceno, $souteze, $nejmensi_kurz, $nejvetsi_kurz)
    {
        if ($this->isAjax()) 
        {
            /*$pom1 = $_POST["id"];
            $pom2 = $_POST["param"];*/
            /*$param1 = $_GET["param1"];
            $param2 = $_GET["param1"];*/
            //$this->template->mensiMoznost = "x: ".$id_tiketu." --- ".$zapasy." --- ".$oznaceno." --- ".$souteze." --- ".$nejmensi_kurz." --- ".$nejvetsi_kurz;
            //$this->context->httpRequest->getPost('id');
            //.$_POST["id"];
            //echo var_dump($_GET);
            //$this->template->mensiMoznost = "xxx: ";




            //připravené pro vkládání
            $this->template->mensiMoznost = array();
            $this->template->vetsiMoznost = array();
            $souteze = explode(",",$souteze);
            $podminka = ""; // vybrané soutěže pro podmínku
            for($i=0;$i<count($souteze);$i++)
            {
              $podminka = $podminka."tipsport_zakladni_nabidka_zapasy.id_souteze=".$souteze[$i]." OR ";
            }
           $podminka = substr($podminka, 0, strlen($podminka)-4); 

            $zapasyPodminka = ""; //podmínka, aby nebyly vybrány zápasy, co už jsou na tiketu
            $zapasy = explode(",", $zapasy);
            for($i=0;$i<count($zapasy);$i++)
            {
              $zapasyPodminka = $zapasyPodminka."tipsport_zakladni_nabidka_zapasy.matchId != ".$zapasy[$i]." AND ";
            }
            $zapasyPodminka = substr($zapasyPodminka, 0, strlen($zapasyPodminka)-4); 


            $sqlMensi = "SELECT tipsport_zakladni_nabidka_zapasy.matchId as matchId, tipsport_zakladni_nabidka_moznosti.shortName as shortName, rate, name FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze AND (".$zapasyPodminka.") AND rate < ".$nejmensi_kurz." ORDER BY rate DESC LIMIT 1";
            //$this->template->mensiMoznost = $sqlMensi; 
            $dataMensi = $this->databaze->fetch($sqlMensi);
            //echo "dataMensi: ".var_dump($dataMensi);

            if(!isset($dataMensi["matchId"]))
            {
                $dataMensi["matchId"] = 0;
            }
            $sqlVetsi = "SELECT tipsport_zakladni_nabidka_zapasy.matchId as matchId, tipsport_zakladni_nabidka_moznosti.shortName as shortName, rate, name FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze AND (".$zapasyPodminka.") AND tipsport_zakladni_nabidka_zapasy.matchId != ".$dataMensi["matchId"]." AND rate > ".$nejvetsi_kurz." ORDER BY rate ASC LIMIT 1";
            $dataVetsi = $this->databaze->fetch($sqlVetsi);
            
            $nabidky = array();
            $sqlMensi = "SELECT sport, competitionName, tipsport_zakladni_nabidka_souteze.url as nabidka_url, tipsport_zakladni_nabidka_zapasy.matchId as matchId, name, tipsport_zakladni_nabidka_zapasy.url as zapasy_url, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.shortName) as shortName, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.rate) as rate, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.opportunityId) as opportunityId FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze AND tipsport_zakladni_nabidka_zapasy.matchId =".$dataMensi["matchId"]." GROUP BY matchId";
            $nabidky[0] = $this->databaze->fetch($sqlMensi);

            if(!isset($dataVetsi["matchId"]))
            {
                $dataVetsi["matchId"] = 0;
            }
            $sqlVetsi = "SELECT sport, competitionName, tipsport_zakladni_nabidka_souteze.url as nabidka_url, tipsport_zakladni_nabidka_zapasy.matchId as matchId, name, tipsport_zakladni_nabidka_zapasy.url as zapasy_url, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.shortName) as shortName, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.rate) as rate, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.opportunityId) as opportunityId FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze AND tipsport_zakladni_nabidka_zapasy.matchId =".$dataVetsi["matchId"]." GROUP BY matchId";
            $nabidky[1] = $this->databaze->fetch($sqlVetsi);
            
            if($nabidky[0] != null)
            {   
                $nabidky_kurzy = explode(",", $nabidky[0]["rate"]);
                $nabidky_moznosti = explode(",", $nabidky[0]["shortName"]);
                //$this->template->mensiMoznost = count($nabidky_kurzy)." --- ".count($nabidky_moznosti);
                
                $kurzy_pole = array_combine($nabidky_moznosti, $nabidky_kurzy);

                $nabidky_moznosti_id = explode(",", $nabidky[0]["opportunityId"]);
                $nabidky_moznosti_id_pole = array_combine($nabidky_moznosti, $nabidky_moznosti_id);

                /*$this->template->mensiMoznost = "<div class='large-7 columns kurzy_polozka'>{$nabidky[0]["name"]}</div>
    <div class='large-1 columns text-center kurzy {if ".$dataMensi["shortName"]." == 1}oznaceno{/if} {$id_tiketu}_{$nabidky[0]["matchId"]}' id='{isset(".$nabidky_moznosti_id_pole["1"].") ? ".$nabidky_moznosti_id_pole["1"]." : 'x'}' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id);'>{(isset(".$kurzy_pole["1"].") ? ".$kurzy_pole["1"]." : 'x')}</div>
    <div class='large-1 columns text-center kurzy {if ".$dataMensi["shortName"]." == 10}oznaceno{/if} {$id_tiketu}_{$nabidky[0]["matchId"]}' id='{isset(".$nabidky_moznosti_id_pole["10"].") ? ".$nabidky_moznosti_id_pole["10"]." : 'x'}' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>{(isset(".$kurzy_pole["10"].") ? ".$kurzy_pole["10"]." : 'x')}</div>
    <div class='large-1 columns text-center kurzy {if ".$dataMensi["shortName"]." == 0}oznaceno{/if} {$id_tiketu}_{$nabidky[0]["matchId"]}' id='{isset(".$nabidky_moznosti_id_pole["0"].") ? ".$nabidky_moznosti_id_pole["0"]." : 'x'}' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>{(isset(".$kurzy_pole["0"].") ? ".$kurzy_pole["0"]." : 'x')}</div>
    <div class='large-1 columns text-center kurzy {if ".$dataMensi["shortName"]." === 02}oznaceno{/if} {$id_tiketu}_{$nabidky[0]["matchId"]}' id='{isset(".$nabidky_moznosti_id_pole["02"].") ? ".$nabidky_moznosti_id_pole["02"]." : 'x'}' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>{(isset(".$kurzy_pole["02"].") ? ".$kurzy_pole["02"]." : 'x')}</div>
    <div class='large-1 columns text-center kurzy {if ".$dataMensi["shortName"]." === 2}oznaceno{/if} {$id_tiketu}_{$nabidky[0]["matchId"]}' id='{isset(".$nabidky_moznosti_id_pole["2"].") ? ".$nabidky_moznosti_id_pole["2"]." : 'x'}' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>{(isset(".$kurzy_pole["2"].")? ".$kurzy_pole["2"]." :  'x')}</div>";*/

    $this->template->mensiMoznost[$id_tiketu] = Html::el()->setHtml("<div class='large-7 columns kurzy_polozka'>{$nabidky[0]["name"]}</div>
    <div class='large-1 columns text-center kurzy ".(($dataMensi["shortName"] == "1") ? "oznaceno" : "")." {$id_tiketu}_{$nabidky[0]["matchId"]}' id='".(isset($nabidky_moznosti_id_pole["1"]) ? $nabidky_moznosti_id_pole["1"] : 'x')."' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id);'>".(isset($kurzy_pole["1"]) ? $kurzy_pole["1"] : 'x')."</div>
    <div class='large-1 columns text-center kurzy ".(($dataMensi["shortName"] == "10") ? "oznaceno" : "")." {$id_tiketu}_{$nabidky[0]["matchId"]}' id='".(isset($nabidky_moznosti_id_pole["10"]) ? $nabidky_moznosti_id_pole["10"] : 'x')."' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>".(isset($kurzy_pole["10"]) ? $kurzy_pole["10"] : 'x')."</div>
    <div class='large-1 columns text-center kurzy ".(($dataMensi["shortName"] == "0") ? "oznaceno" : "")." {$id_tiketu}_{$nabidky[0]["matchId"]}' id='".(isset($nabidky_moznosti_id_pole["0"]) ? $nabidky_moznosti_id_pole["0"] : 'x')."' onMouseOfver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>".(isset($kurzy_pole["0"]) ? $kurzy_pole["0"] : 'x')."</div>
    <div class='large-1 columns text-center kurzy ".(($dataMensi["shortName"] === "02") ? "oznaceno" : "")." {$id_tiketu}_{$nabidky[0]["matchId"]}' id='".(isset($nabidky_moznosti_id_pole["02"]) ? $nabidky_moznosti_id_pole["02"] : 'x')."' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>".(isset($kurzy_pole["02"]) ? $kurzy_pole["02"] : 'x')."</div>
    <div class='large-1 columns text-center kurzy ".(($dataMensi["shortName"] === "2") ? "oznaceno" : "")." {$id_tiketu}_{$nabidky[0]["matchId"]}' id='".(isset($nabidky_moznosti_id_pole["2"]) ? $nabidky_moznosti_id_pole["2"] : 'x')."' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>".(isset($kurzy_pole["2"]) ? $kurzy_pole["2"] : 'x')."</div>");

      //$this->template->mensiMoznost = Html::el("h1","TEXT");

            }
            else
            {
                $this->template->mensiMoznost[$id_tiketu] = "";
            }

            if($nabidky[1] != null)
            {
                $nabidky_kurzy = explode(",", $nabidky[1]["rate"]);
                $nabidky_moznosti = explode(",", $nabidky[1]["shortName"]);
                //$this->template->mensiMoznost = count($nabidky_kurzy)." --- ".count($nabidky_moznosti);
                
                $kurzy_pole = array_combine($nabidky_moznosti, $nabidky_kurzy);

                $nabidky_moznosti_id = explode(",", $nabidky[1]["opportunityId"]);
                $nabidky_moznosti_id_pole = array_combine($nabidky_moznosti, $nabidky_moznosti_id);

                $this->template->vetsiMoznost[$id_tiketu] = Html::el()->setHtml("<div class='large-7 columns kurzy_polozka'>{$nabidky[1]["name"]}</div>
    <div class='large-1 columns text-center kurzy ".(($dataVetsi["shortName"] == "1") ? "oznaceno" : "")." {$id_tiketu}_{$nabidky[1]["matchId"]}' id='".(isset($nabidky_moznosti_id_pole["1"]) ? $nabidky_moznosti_id_pole["1"] : 'x')."' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id);'>".(isset($kurzy_pole["1"]) ? $kurzy_pole["1"] : 'x')."</div>
    <div class='large-1 columns text-center kurzy ".(($dataVetsi["shortName"] == "10") ? "oznaceno" : "")." {$id_tiketu}_{$nabidky[1]["matchId"]}' id='".(isset($nabidky_moznosti_id_pole["10"]) ? $nabidky_moznosti_id_pole["10"] : 'x')."' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>".(isset($kurzy_pole["10"]) ? $kurzy_pole["10"] : 'x')."</div>
    <div class='large-1 columns text-center kurzy ".(($dataVetsi["shortName"] == "0") ? "oznaceno" : "")." {$id_tiketu}_{$nabidky[1]["matchId"]}' id='".(isset($nabidky_moznosti_id_pole["0"]) ? $nabidky_moznosti_id_pole["0"] : 'x')."' onMouseOfver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>".(isset($kurzy_pole["0"]) ? $kurzy_pole["0"] : 'x')."</div>
    <div class='large-1 columns text-center kurzy ".(($dataVetsi["shortName"] === "02") ? "oznaceno" : "")." {$id_tiketu}_{$nabidky[1]["matchId"]}' id='".(isset($nabidky_moznosti_id_pole["02"]) ? $nabidky_moznosti_id_pole["02"] : 'x')."' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>".(isset($kurzy_pole["02"]) ? $kurzy_pole["02"] : 'x')."</div>
    <div class='large-1 columns text-center kurzy ".(($dataVetsi["shortName"] === "2") ? "oznaceno" : "")." {$id_tiketu}_{$nabidky[1]["matchId"]}' id='".(isset($nabidky_moznosti_id_pole["2"]) ? $nabidky_moznosti_id_pole["2"] : 'x')."' onMouseOver='pres_kurz(this);' onMouseOut='od_kurz(this);' onClick='klik(this.className,this.id)'>".(isset($kurzy_pole["2"]) ? $kurzy_pole["2"] : 'x')."</div>");
            }
            else
            {
                $this->template->vetsiMoznost[$id_tiketu] = "";
            }

            $this->template->mensiMoznost[$id_tiketu] = "AAAAAAA ".$id_tiketu;
            $this->template->vetsiMoznost[$id_tiketu] = "bbbbbbb ".$id_tiketu;

            //$this->template->mensiKurz_2 = "222222222222222";
            //$this->redrawControl("obal1");
            /*$this->redrawControl("snippet--mensiKurz_".$id_tiketu."");
            $this->redrawControl("snippet--vetsiKurz_".$id_tiketu."");*/

            /*$this->redrawControl("mensiKurz");
            $this->redrawControl("vetsiKurz");*/
            //$this->template->snippet["mensiKurz_2"] = 'Updated item';
            
            //$this->redrawControl("obal");

            //$this->template->setFile("");
        }
    }

    public function renderDefault()
    {
        $this->template->anyVariable = "any value";
        /*$this->template->moznosti = $this->moznosti;
        echo "xxx".var_dump($this->template->moznosti);*/
        $this->template->cislo = 1;
    }



}