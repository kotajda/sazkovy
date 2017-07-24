<?php

namespace App\Model;

use Nette;
use Nette\Application\UI;
use Nette\Utils\Html;


/**
 * Users management.
 */
class NejKurzyManager
{
	use Nette\SmartObject;

	/** @var Nette\Database\Context */
	private $databaze;


	public function __construct(Nette\Database\Context $database)
	{
		$this->databaze = $database;
	}

	public function vytvorFormular()
	{
		$form = new UI\Form;

        $moznosti = $this->databaze->query("SELECT id, sport, competitionName FROM tipsport_zakladni_nabidka_souteze");
        //$this->moznosti = $moznosti;
        $moznostiPole = array();

        //$option = array();

        foreach ($moznosti as $moznost) 
        {
            $moznostiPole[$moznost->id] = Html::el('options',  $moznost->competitionName . " - ".  $moznost->sport)->onClick("vyber_souteze(".$moznost->id.",'".$moznost->competitionName."','".$moznost->sport."');");

        }

        /*$this->template->souteze = array();
        $this->template->souteze = $moznostiPole;*/

        $souteze = array();
        $souteze = $moznostiPole;

        $form->addMultiSelect("souteze","", $moznostiPole)->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");
        
        $form->addMultiSelect("vybrane_souteze")
            ->setAttribute("id", "vybrane_souteze")
            ->setAttribute("onBlur", "oznacit_vsechny(this);")
            ->setAttribute("style", "position:relative;top: 50%;transform: translateY(-50%);");

        $form->addText("pocet", "Pocet:")
            ->setDefaultValue("Počet zápasů...")
            ->setAttribute("onClick", "smazat_text_input(this);")
            ->setAttribute("onBlur", "vlozit_default(this);")
            ->setAttribute("style", "position:relative;top:25%;height:50%");

        $sazkovky = ["vse" => "Všechny", "tipsport" => "Tipsport", "fortuna" => "Fortuna"];
        $form->addRadioList("sazkovka","Sázková kancelář",$sazkovky);

        $form->addSubmit("zobrazit", "Zobrazit kurzy")
            ->setAttribute("class", "button")
            ->setAttribute("style", "position:relative;top:25%;height:50%");

        //$form->onSuccess[] = [$this, "selectFormSucceeded"];


        $renderer = $form->getRenderer();
        //$renderer->wrappers['moznosti']['size'] = 20;

        //echo var_dump($form->getRawValue());

        return $form;
	}

	public function zobrazit($form, $values, &$templ_nabidky, &$templ_kurzy_pozice, &$templ_nabidky_kurzy, &$templ_nabidky_moznosti, &$templ_i, &$templ_kurzy_pole, &$templ_nabidky_moznosti_id, &$templ_nabidky_moznosti_id_pole, &$templ_id_tiketu, &$templ_sazkovka, $razeni)
	{
		$souteze = $form["vybrane_souteze"]->getRawValue();
        $pocet = $values["pocet"];
        $sazkovka = $values["sazkovka"];
        $podminka = "";
        $podminka2 = "";
        if(empty($souteze) || empty($pocet) || empty($sazkovka))
        {
            return 0;
        }
        for($i=0;$i<count($souteze);$i++)
        {
          $podminka = $podminka."tipsport_zakladni_nabidka_zapasy.id_souteze=".$souteze[$i]." OR ";
          $podminka2 = $podminka2."tipsport_zakladni_nabidka_souteze.id=".$souteze[$i]." OR ";
        }
        $podminka = substr($podminka, 0, strlen($podminka)-4);
        $podminka2 = substr($podminka2, 0, strlen($podminka2)-4);

        $minmax = "MAX";
        if($razeni == "ASC")
        {
        	$minmax = "MIN";
        }

        $sql_fortuna = "";
        if($sazkovka == "vse" || $sazkovka == "fortuna")
        {
            $data = $this->databaze->query("SELECT sport, competitionName FROM tipsport_zakladni_nabidka_souteze WHERE ".$podminka2);
            $sql_fortuna_podminka_souteze = "";
            $sql_fortuna_podminka_sporty = "";
            foreach ($data as $souteze) 
            {
                //echo $souteze["competitionName"]."<br />";
                $nazev_souzeze = substr($souteze["competitionName"],0,6);
                //echo $nazev_souzeze."<br />";
                if(is_numeric(substr($nazev_souzeze, 0,1)))
                {
                    $nazev_souzeze = str_replace(" ", "", $nazev_souzeze);
                }
                //echo $nazev_souzeze."<br />";

                //$sql_fortuna_podminka_souteze = $sql_fortuna_podminka_souteze."fortuna_nabidka_turnaje.nazev_turnaje LIKE '%".substr($souteze["competitionName"],0,5)."%' OR ";
                $sql_fortuna_podminka_souteze = $sql_fortuna_podminka_souteze."fortuna_nabidka_turnaje.nazev_turnaje LIKE '%".$nazev_souzeze."%' OR ";
                //echo explode("-",$souteze["sport"])[0]."<br />";
                $nazev_kategorie = explode(" - ",$souteze["sport"])[0];
                if($nazev_kategorie == "Lední hokej")
                {
                    $nazev_kategorie = "Hokej";
                }
                //echo $nazev_kategorie."<hr />";
                $sql_fortuna_podminka_sporty = $sql_fortuna_podminka_sporty."fortuna_nabidka_kategorie.nazev_kategorie LIKE '%".$nazev_kategorie."%' OR ";
            }
            $sql_fortuna_podminka_souteze = substr($sql_fortuna_podminka_souteze, 0, strlen($sql_fortuna_podminka_souteze)-4);
            $sql_fortuna_podminka_sporty = substr($sql_fortuna_podminka_sporty, 0, strlen($sql_fortuna_podminka_sporty)-4);
            $sql_fortuna = "SELECT ".$minmax."(kurz_moznosti) as nej_kurz, nazev_zapasu as name, nazev_kategorie as sport, nazev_turnaje as competitionName, odkaz as nabidka_url, fortuna_nabidka_zapasy.id_zapasu as matchId, GROUP_CONCAT(nazev_moznosti) as shortName, nazev_moznosti as nejmensi_pozice, GROUP_CONCAT(kurz_moznosti) as rate, GROUP_CONCAT(id_moznosti) as opportunityId FROM fortuna_nabidka_kategorie, fortuna_nabidka_sazky, fortuna_nabidka_sazky_moznosti, fortuna_nabidka_turnaje, fortuna_nabidka_zapasy WHERE (".$sql_fortuna_podminka_souteze.") AND (".$sql_fortuna_podminka_sporty.") 
                AND fortuna_nabidka_kategorie.id_kategorie = fortuna_nabidka_turnaje.id_kategorie 
                AND fortuna_nabidka_turnaje.id_kategorie = fortuna_nabidka_zapasy.id_kategorie
                AND fortuna_nabidka_zapasy.id_kategorie = fortuna_nabidka_sazky.id_kategorie
                AND fortuna_nabidka_sazky.id_kategorie = fortuna_nabidka_sazky_moznosti.id_kategorie
                AND fortuna_nabidka_turnaje.id_turnaje = fortuna_nabidka_zapasy.id_turnaje 
                AND fortuna_nabidka_zapasy.id_turnaje = fortuna_nabidka_sazky.id_turnaje
                AND fortuna_nabidka_sazky.id_turnaje = fortuna_nabidka_sazky_moznosti.id_turnaje
                AND fortuna_nabidka_zapasy.id_zapasu = fortuna_nabidka_sazky.id_zapasu 
                AND fortuna_nabidka_sazky.id_zapasu = fortuna_nabidka_sazky_moznosti.id_zapasu
                AND fortuna_nabidka_sazky.id_sazky = fortuna_nabidka_sazky_moznosti.id_sazky
                AND nazev_sazky = 'zápas'
                AND nazev_moznosti !='12'
                GROUP BY matchId ORDER BY nej_kurz ".$razeni." LIMIT ".$pocet."";
        }

        $sql_tipsport = "";
        if($sazkovka == "vse" || $sazkovka == "tipsport")
        {
            $sql_tipsport = "SELECT ".$minmax."(rate) as nej_kurz, name, sport, competitionName, tipsport_zakladni_nabidka_souteze.url as nabidka_url, tipsport_zakladni_nabidka_zapasy.matchId as matchId, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.shortName) as shortName, tipsport_zakladni_nabidka_moznosti.shortName as nejmensi_pozice, GROUP_CONCAT(rate) AS rate, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.opportunityId) as opportunityId 
                FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti 
                WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze
                GROUP BY matchId
                ORDER BY nej_kurz ".$razeni."
                LIMIT ".$pocet."";
        }
        $sql = "";
        if($sazkovka == "vse")
        {
            $sql = "(".$sql_fortuna.") UNION (".$sql_tipsport.") ORDER BY nej_kurz DESC";
        }
        else if($sazkovka == "tipsport")
        {
            $sql = $sql_tipsport;
        }
        else if($sazkovka == "fortuna")
        {
            $sql = $sql_fortuna;
        }
        //echo $sql;
        $data = $this->databaze->query($sql);

        foreach($data as $prilezitosti)
        {
            if(strpos($prilezitosti["nabidka_url"],"tipsport") != false)
            {
                array_push($templ_sazkovka, "Tipsport");
            }
            else
            {
                array_push($templ_sazkovka, "Fortuna");   
            }

            array_push($templ_nabidky, $prilezitosti);

            array_push($templ_kurzy_pozice, $prilezitosti["nejmensi_pozice"]);
            //array_push($this->template->nabidky_kurzy, explode(",", end($this->template->nabidky)["rate"]));
            array_push($templ_nabidky_kurzy, explode(",", $prilezitosti["rate"]));
            //array_push($this->template->nabidky_moznosti, explode(",", end($this->template->nabidky)["shortName"]));
            array_push($templ_nabidky_moznosti, explode(",", $prilezitosti["shortName"]));
            array_push($templ_kurzy_pole, array_combine(end($templ_nabidky_moznosti), end($templ_nabidky_kurzy)));
            
            array_push($templ_id_tiketu, 1);

            $templ_i++;

        }
	}
}