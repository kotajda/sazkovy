<?php

namespace App\Model;

use Nette;

use Nette\Database\Context;
use Nette\Database\Connection;

class GeneratorManager
{
    use Nette\SmartObject;

    /**
     * @var Nette\Database\Context
     */
    private $databaze;

    public function __construct(Nette\Database\Context $databaze)
    {
        $this->databaze = $databaze;
    }

    public function generator(&$nabidky, &$prilezitost, &$kurz, &$templ_nabidky, &$templ_kurzy_pozice, &$templ_nabidky_kurzy, &$templ_nabidky_moznosti, &$templ_kurzy_pole, &$templ_nabidky_moznosti_id_pole, &$templ_i, &$templ_id_tiketu, &$templ_kurz_tiketu, &$templ_tiket, &$nabidky_moznosti_id, &$pom_nabidky_moznosti_id_pole, &$pom_nabidky_moznosti_id, &$pom_nabidky, &$pom_kurzy, &$pom_kurzy_pozice, &$pom_nabidky_kurzy, &$pom_nabidky_moznosti, &$pom_kurzy_pole, &$id_tiketu, &$kurz_tiketu, &$nalezeno, &$zapasy_na_tiketu, $kurz_zacatek_tiketu = NULL)
    {   
        if(is_null($kurz_zacatek_tiketu))
        {
            $kurz_zacatek_tiketu = 1;
        }
        $nalezeno = in_array($nabidky, $zapasy_na_tiketu);
        if($nalezeno)
        {
            //naplnění pomocných polí duplicitními zápasama, které budou na začátku dalšího tiketu
            array_push($pom_nabidky, $nabidky);
            array_push($pom_kurzy, $prilezitost["rate"]);
            array_push($pom_kurzy_pozice, $prilezitost["shortName"]);
            array_push($pom_nabidky_kurzy, explode(",", $nabidky["rate"]));
            array_push($pom_nabidky_moznosti, explode(",", $nabidky["shortName"]));
            array_push($pom_kurzy_pole, array_combine(end($pom_nabidky_moznosti), end($pom_nabidky_kurzy)));

            array_push($pom_nabidky_moznosti_id, explode(",", end($pom_nabidky)["opportunityId"]));
            array_push($pom_nabidky_moznosti_id_pole, array_combine(end($pom_nabidky_moznosti), end($pom_nabidky_moznosti_id)));
        }
        else
        {
            array_push($zapasy_na_tiketu,$nabidky);
            //naplňování tiketu zápasama
            array_push($templ_nabidky, $nabidky);
            array_push($templ_kurzy_pozice, $prilezitost["shortName"]);
            array_push($templ_nabidky_kurzy, explode(",", $nabidky["rate"]));
            array_push($templ_nabidky_moznosti, explode(",", $nabidky["shortName"]));
            array_push($templ_kurzy_pole, array_combine(end($templ_nabidky_moznosti), end($templ_nabidky_kurzy)));
                
            array_push($nabidky_moznosti_id, explode(",", end($templ_nabidky)["opportunityId"]));
            array_push($templ_nabidky_moznosti_id_pole, array_combine(end($templ_nabidky_moznosti), end($nabidky_moznosti_id)));

            $templ_i++;
            array_push($templ_id_tiketu,$id_tiketu);
            $kurz_tiketu = $kurz_tiketu * $prilezitost["rate"];
            
            if($kurz_tiketu > $kurz + 0.5)
            {
                $kurz_tiketu = $kurz_tiketu / $prilezitost["rate"];
                $templ_tiket[$templ_i - 2] = 1;
                $templ_kurz_tiketu[$templ_i - 2] = $kurz_tiketu;
                //$kurz_tiketu = $prilezitost["rate"];
                //echo "kurz začátek tiketu: ".$kurz_zacatek_tiketu."<hr />";
                $kurz_tiketu = $kurz_zacatek_tiketu *  $prilezitost["rate"];
                $zapasy_na_tiketu = array();
                       
                for($i=0;$i<count($pom_nabidky);$i++)
                {
                    if($kurz_tiketu * $pom_kurzy[$i] < $kurz + 0.5)
                    {
                        //echo count($this->template->nabidky)." --- ".count($this->template->kurzy_pozice)." --- ".count($this->template->kurzy_pole)."<br />";
                        $pom_nabidka = array_shift($pom_nabidky);
                        /*array_push($zapasy_na_tiketu, $pom_nabidka);
                        array_push($templ_nabidky, $pom_nabidka);
                        array_push($templ_kurzy_pozice, array_shift($pom_kurzy_pozice));
                        array_push($templ_kurzy_pole, array_shift($pom_kurzy_pole));*/
                    
                        $templ_nabidky[count($templ_nabidky)] = $templ_nabidky[count($templ_nabidky)-2];
                        $templ_nabidky[count($templ_nabidky)-2] = $pom_nabidka;
                        $templ_kurzy_pozice[count($templ_kurzy_pozice)] = $templ_kurzy_pozice[count($templ_kurzy_pozice)-2];
                        $templ_kurzy_pozice[count($templ_kurzy_pozice)-2] = array_shift($pom_kurzy_pozice);
                        $templ_kurzy_pole[count($templ_kurzy_pole)] = $templ_kurzy_pole[count($templ_kurzy_pole)-2];
                        $templ_kurzy_pole[count($templ_kurzy_pole)-2] = array_shift($pom_kurzy_pole);

                        //echo $pom_nabidka["name"]."<hr />";
                        //echo $pom_nabidky["name"]."<hr />";
                        //dump($pom_nabidky);
                        //echo count($this->template->nabidky)." --- ".count($this->template->kurzy_pozice)." --- ".count($this->template->kurzy_pole)."<br />";
                            
                        //$predposledni = count($templ_nabidky) - 1;
                        //array_splice($zapasy_na_tiketu, $predposledni, 0, $pom_nabidka);                            
                        /*array_splice($this->template->nabidky, $predposledni, 0, $pom_nabidka);
                        array_splice($this->template->kurzy_pozice, $predposledni, 0, array_shift($pom_kurzy_pozice));
                        array_splice($this->template->kurzy_pole, $predposledni, 0, array_shift($pom_kurzy_pole));*/

                        /*array_splice($this->template->nabidky, (count($this->template->nabidky) - 2), 0, $pom_nabidka);
                        array_splice($this->template->kurzy_pozice, (count($this->template->kurzy_pozice) -2), 0, array_shift($pom_kurzy_pozice));
                        array_splice($this->template->kurzy_pole, (count($this->template->kurzy_pole) - 2), 0, array_shift($pom_kurzy_pole));*/

                        //echo count($this->template->nabidky)." --- ".count($this->template->kurzy_pozice)." --- ".count($this->template->kurzy_pole)."<br />";

                        /*array_unshift($zapasy_na_tiketu, $pom_nabidka);
                        array_unshift($this->template->nabidky, $pom_nabidka);
                        array_unshift($this->template->kurzy_pozice, array_shift($pom_kurzy_pozice));
                        array_unshift($this->template->kurzy_pole, array_shift($pom_kurzy_pole));*/

                        /*$pom_nabidka = $pom_nabidky;
                        array_unshift($zapasy_na_tiketu, $pom_nabidka);
                        array_unshift($this->template->nabidky, $pom_nabidka);
                        array_unshift($this->template->kurzy_pozice, $pom_kurzy_pozice);
                        array_unshift($this->template->kurzy_pole, $pom_kurzy_pole);   */                         

                        array_shift($pom_nabidky_kurzy);
                        array_shift($pom_nabidky_moznosti);
                        array_push($templ_nabidky_moznosti_id_pole,array_shift($pom_nabidky_moznosti_id_pole));
                        //array_splice($templ_nabidky_moznosti_id_pole, (count($templ_nabidky_moznosti_id_pole)-1), 0,array_shift($pom_nabidky_moznosti_id_pole));
                        //array_unshift($this->template->nabidky_moznosti_id_pole,array_shift($pom_nabidky_moznosti_id_pole));
                        //array_unshift($this->template->nabidky_moznosti_id_pole,$pom_nabidky_moznosti_id_pole);

                        $templ_i++;
                        array_push($templ_id_tiketu,$id_tiketu);
                        //array_splice($templ_id_tiketu, (count($templ_id_tiketu)-1), 0, $id_tiketu);
                        //array_unshift($this->template->id_tiketu,$id_tiketu);
                        //array_unshift($this->template->id_tiketu,$id_tiketu);
                        $kurz_tiketu = $kurz_tiketu * array_shift($pom_kurzy);
                            //$kurz_tiketu = $kurz_tiketu * array_shift($pom_kurzy);
                    }
                }
                $id_tiketu++;

                /*$pom_nabidky = array();
                $pom_kurzy = array(); 
                $pom_kurzy_pozice = array(); 
                $pom_nabidky_kurzy = array();
                $pom_nabidky_moznosti = array();
                $pom_kurzy_pole = array();*/
            }
        }
    }

    public function generatorTipsport($podminka, &$nabidky, &$prilezitost, &$kurz, &$templ_nabidky, &$templ_kurzy_pozice, &$templ_nabidky_kurzy, &$templ_nabidky_moznosti, &$templ_kurzy_pole, &$templ_nabidky_moznosti_id_pole, &$templ_i, &$templ_id_tiketu, &$templ_kurz_tiketu, &$templ_tiket, &$nabidky_moznosti_id, &$pom_nabidky_moznosti_id_pole, &$pom_nabidky_moznosti_id, &$pom_nabidky, &$pom_kurzy, &$pom_kurzy_pozice, &$pom_nabidky_kurzy, &$pom_nabidky_moznosti, &$pom_kurzy_pole, &$id_tiketu, &$kurz_tiketu, &$nalezeno, &$zapasy_na_tiketu, $doplneni_tiketu = NULL, $zacatek_tiketu = NULL, $zacatek_tiketu_prilezitost = NULL, $podminka_bez_zapasu = NULL, $kurz_zacatek_tiketu = NULL)
    {
        if(empty($kurz) || empty($podminka))
        {
            return 0;
        }
        if(is_null($podminka_bez_zapasu))
        {
            $podminka_bez_zapasu = "TRUE";
        }

        $sql = "SELECT tipsport_zakladni_nabidka_zapasy.matchId as matchId, tipsport_zakladni_nabidka_moznosti.shortName as shortName, rate, name FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti WHERE (".$podminka_bez_zapasu.") AND (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze AND rate <= ".$kurz." ORDER BY rate";
            //echo $sql;
        $data = $this->databaze->query($sql);
        foreach($data as $prilezitost) //projíždím zápasy odpovídající zvolenému nastavení  
        {
            $sql = "SELECT sport, competitionName, tipsport_zakladni_nabidka_souteze.id as id_souteze, tipsport_zakladni_nabidka_souteze.url as nabidka_url, tipsport_zakladni_nabidka_zapasy.matchId as matchId, name, tipsport_zakladni_nabidka_zapasy.url as zapasy_url, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.shortName) as shortName, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.rate) as rate, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.opportunityId) as opportunityId FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze AND tipsport_zakladni_nabidka_zapasy.matchId =".$prilezitost["matchId"]." GROUP BY matchId";
                //echo $sql."<br /><br />";
            $nabidky = $this->databaze->fetch($sql);

            $this->generator($nabidky, $prilezitost, $kurz, $templ_nabidky, $templ_kurzy_pozice, $templ_nabidky_kurzy, $templ_nabidky_moznosti, $templ_kurzy_pole, $templ_nabidky_moznosti_id_pole, $templ_i, $templ_id_tiketu, $templ_kurz_tiketu, $templ_tiket, $nabidky_moznosti_id, $pom_nabidky_moznosti_id_pole, $pom_nabidky_moznosti_id, $pom_nabidky, $pom_kurzy, $pom_kurzy_pozice, $pom_nabidky_kurzy, $pom_nabidky_moznosti, $pom_kurzy_pole, $id_tiketu, $kurz_tiketu, $nalezeno, $zapasy_na_tiketu, $kurz_zacatek_tiketu);
        }
    }

    public function generatorTipsportPodleZapasu($podminka, $podminka_bez_zapasu, $podminka_vybrane, &$nabidky, &$prilezitost, &$kurz, &$templ_nabidky, &$templ_kurzy_pozice, &$templ_nabidky_kurzy, &$templ_nabidky_moznosti, &$templ_kurzy_pole, &$templ_nabidky_moznosti_id_pole, &$templ_i, &$templ_id_tiketu, &$templ_kurz_tiketu, &$templ_tiket, &$nabidky_moznosti_id, &$pom_nabidky_moznosti_id_pole, &$pom_nabidky_moznosti_id, &$pom_nabidky, &$pom_kurzy, &$pom_kurzy_pozice, &$pom_nabidky_kurzy, &$pom_nabidky_moznosti, &$pom_kurzy_pole, &$id_tiketu, &$kurz_tiketu, &$nalezeno, &$zapasy_na_tiketu, &$doplneni_tiketu, &$zacatek_tiketu, &$zacatek_tiketu_prilezitost, &$templ_zacatek_tiketu_nabidky, &$templ_zacatek_tiketu_kurzy_pozice, &$templ_zacatek_tiketu_nabidky_kurzy, &$templ_zacatek_tiketu_nabidky_moznosti, &$templ_zacatek_tiketu_kurzy_pole, &$templ_zacatek_tiketu_nabidky_moznosti_id_pole, &$zacatek_tiketu_nabidky_moznosti_id, &$templ_zacatek_tiketu_id_tiketu)
    {
        if(empty($kurz) || empty($podminka) || empty($podminka_vybrane))
        {
            return 0;
        }

        $sql = "SELECT tipsport_zakladni_nabidka_zapasy.matchId, shortName, rate, name FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti WHERE (".$podminka_vybrane.") AND (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze AND rate < ".$kurz." AND rate = (SELECT MIN(rate) FROM tipsport_zakladni_nabidka_moznosti as tip WHERE tip.matchId = tipsport_zakladni_nabidka_moznosti.matchId) ORDER BY rate"; //AND rate <= ".$kurz." ORDER BY rate";

        $data = $this->databaze->query($sql);
        
        foreach($data as $prilezitost)
        {
            $sql = "SELECT sport, competitionName, tipsport_zakladni_nabidka_souteze.id as id_souteze, tipsport_zakladni_nabidka_souteze.url as nabidka_url, tipsport_zakladni_nabidka_zapasy.matchId as matchId, name, tipsport_zakladni_nabidka_zapasy.url as zapasy_url, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.shortName) as shortName, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.rate) as rate, GROUP_CONCAT(tipsport_zakladni_nabidka_moznosti.opportunityId) as opportunityId FROM tipsport_zakladni_nabidka_souteze, tipsport_zakladni_nabidka_zapasy, tipsport_zakladni_nabidka_moznosti WHERE (".$podminka.") AND tipsport_zakladni_nabidka_zapasy.matchId = tipsport_zakladni_nabidka_moznosti.matchId AND tipsport_zakladni_nabidka_souteze.id = tipsport_zakladni_nabidka_moznosti.id_souteze AND tipsport_zakladni_nabidka_zapasy.matchId =".$prilezitost["matchId"]." GROUP BY matchId";
            array_push($zacatek_tiketu, $this->databaze->fetch($sql)); 
            array_push($zacatek_tiketu_prilezitost, $prilezitost);       
        }

        for($i=0;$i<count($zacatek_tiketu);$i++)
        {
            //array_push($zacatek_tiketu_zapasy_na_tiketu,$zacatek_tiketu[$i]);
            //naplňování tiketu zápasama
            array_push($templ_zacatek_tiketu_nabidky, $zacatek_tiketu[$i]);
            array_push($templ_zacatek_tiketu_kurzy_pozice, $zacatek_tiketu_prilezitost[$i]["shortName"]);
            array_push($templ_zacatek_tiketu_nabidky_kurzy, explode(",", $zacatek_tiketu[$i]["rate"]));
            array_push($templ_zacatek_tiketu_nabidky_moznosti, explode(",", $zacatek_tiketu[$i]["shortName"]));
            array_push($templ_zacatek_tiketu_kurzy_pole, array_combine(end($templ_zacatek_tiketu_nabidky_moznosti), end($templ_zacatek_tiketu_nabidky_kurzy)));      

            array_push($zacatek_tiketu_nabidky_moznosti_id, explode(",", end($templ_zacatek_tiketu_nabidky)["opportunityId"]));
            array_push($templ_zacatek_tiketu_nabidky_moznosti_id_pole, array_combine(end($templ_zacatek_tiketu_nabidky_moznosti), end($zacatek_tiketu_nabidky_moznosti_id)));

            //$templ_i++;
            array_push($templ_zacatek_tiketu_id_tiketu,$id_tiketu);
            $kurz_tiketu = $kurz_tiketu * $zacatek_tiketu_prilezitost[$i]["rate"];
        }
        $kurz_zacatek_tiketu = $kurz_tiketu;

        $this->generatorTipsport($podminka, $nabidky, $prilezitost, $kurz, $templ_nabidky, $templ_kurzy_pozice, $templ_nabidky_kurzy, $templ_nabidky_moznosti, $templ_kurzy_pole, $templ_nabidky_moznosti_id_pole, $templ_i, $templ_id_tiketu, $templ_kurz_tiketu, $templ_tiket, $nabidky_moznosti_id, $pom_nabidky_moznosti_id_pole, $pom_nabidky_moznosti_id, $pom_nabidky, $pom_kurzy, $pom_kurzy_pozice, $pom_nabidky_kurzy, $pom_nabidky_moznosti, $pom_kurzy_pole, $id_tiketu, $kurz_tiketu, $nalezeno, $zapasy_na_tiketu, $doplneni_tiketu, $zacatek_tiketu, $zacatek_tiketu_prilezitost, $podminka_bez_zapasu, $kurz_zacatek_tiketu);
    }

    public function generatorFortunaPomocna($podminka_fortuna, &$sql_fortuna_podminka_souteze,&$sql_fortuna_podminka_sporty)
    {
        $data = $this->databaze->query("SELECT sport, competitionName FROM tipsport_zakladni_nabidka_souteze WHERE ".$podminka_fortuna);
        /*$sql_fortuna_podminka_souteze = "";
        $sql_fortuna_podminka_sporty = "";*/
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
    }

    public function generatorFortuna($podminka_fortuna, &$nabidky, &$prilezitost, &$kurz, &$templ_nabidky, &$templ_kurzy_pozice, &$templ_nabidky_kurzy, &$templ_nabidky_moznosti, &$templ_kurzy_pole, &$templ_nabidky_moznosti_id_pole, &$templ_i, &$templ_id_tiketu, &$templ_kurz_tiketu, &$templ_tiket, &$nabidky_moznosti_id, &$pom_nabidky_moznosti_id_pole, &$pom_nabidky_moznosti_id, &$pom_nabidky, &$pom_kurzy, &$pom_kurzy_pozice, &$pom_nabidky_kurzy, &$pom_nabidky_moznosti, &$pom_kurzy_pole, &$id_tiketu, &$kurz_tiketu, &$nalezeno, &$zapasy_na_tiketu, $kurz_zacatek_tiketu = NULL, &$doplneni_tiketu = NULL, &$zacatek_tiketu = NULL, &$zacatek_tiketu_prilezitost = NULL, &$sql_fortuna_podminka_souteze = NULL, &$sql_fortuna_podminka_sporty = NULL, &$podminka_vybrane = NULL, $fortunaPomocna = true)
    {
        if(empty($kurz) || empty($podminka))
        {
            return 0;
        }

        if($fortunaPomocna)
        {
            $this->generatorFortunaPomocna($podminka_fortuna, $sql_fortuna_podminka_souteze, $sql_fortuna_podminka_sporty);
        }
        $sql_fortuna_podminka_zapasy = "TRUE";
        //echo var_dump($podminka_vybrane);
        if(!empty($podminka_vybrane))
        {
            $data = $this->databaze->query("SELECT name FROM tipsport_zakladni_nabidka_zapasy WHERE ".$podminka_vybrane);
            $sql_fortuna_podminka_zapasy = "";
            foreach($data as $zapasy)
            {
                $sql_fortuna_podminka_zapasy = $sql_fortuna_podminka_zapasy."fortuna_nabidka_zapasy.nazev_zapasu NOT LIKE '%".$zapasy["name"]."%' AND ";
            }
            $sql_fortuna_podminka_zapasy = substr($sql_fortuna_podminka_zapasy, 0, strlen($sql_fortuna_podminka_zapasy)-4);
        }
        
        $sql = "SELECT fortuna_nabidka_zapasy.id_zapasu as matchId, fortuna_nabidka_sazky_moznosti.nazev_moznosti as shortName, fortuna_nabidka_sazky_moznosti.kurz_moznosti as rate, fortuna_nabidka_zapasy.nazev_zapasu as name FROM fortuna_nabidka_zapasy, fortuna_nabidka_sazky_moznosti, fortuna_nabidka_turnaje, fortuna_nabidka_kategorie WHERE (".$sql_fortuna_podminka_zapasy.") AND (".$sql_fortuna_podminka_souteze.") AND (".$sql_fortuna_podminka_sporty.")
            AND fortuna_nabidka_kategorie.id_kategorie = fortuna_nabidka_turnaje.id_kategorie
            AND fortuna_nabidka_turnaje.id_kategorie = fortuna_nabidka_zapasy.id_kategorie
            AND fortuna_nabidka_zapasy.id_kategorie = fortuna_nabidka_sazky_moznosti.id_kategorie

            AND fortuna_nabidka_turnaje.id_turnaje = fortuna_nabidka_zapasy.id_turnaje
            AND fortuna_nabidka_zapasy.id_turnaje = fortuna_nabidka_sazky_moznosti.id_turnaje

            AND fortuna_nabidka_zapasy.id_zapasu = fortuna_nabidka_sazky_moznosti.id_zapasu
            AND fortuna_nabidka_zapasy.id_zapasu = fortuna_nabidka_sazky_moznosti.id_sazky

            AND (nazev_moznosti ='1' OR nazev_moznosti ='10' OR nazev_moznosti ='0' OR nazev_moznosti ='02' OR nazev_moznosti ='2')
            AND kurz_moznosti <= ".$kurz." ORDER BY kurz_moznosti";

        $data = $this->databaze->query($sql);
        foreach($data as $prilezitost) //projíždím zápasy odpovídající zvolenému nastavení  
        {
            $sql = "SELECT fortuna_nabidka_kategorie.nazev_kategorie as sport, fortuna_nabidka_turnaje.nazev_turnaje as competitionName, fortuna_nabidka_turnaje.id_turnaje as id_souteze, fortuna_nabidka_zapasy.id_zapasu as matchId, fortuna_nabidka_zapasy.nazev_zapasu as name, GROUP_CONCAT(fortuna_nabidka_sazky_moznosti.nazev_moznosti) AS shortName, GROUP_CONCAT(fortuna_nabidka_sazky_moznosti.kurz_moznosti) AS rate, GROUP_CONCAT(fortuna_nabidka_sazky_moznosti.id_moznosti) as opportunityId FROM fortuna_nabidka_kategorie, fortuna_nabidka_turnaje, fortuna_nabidka_zapasy, fortuna_nabidka_sazky_moznosti WHERE (".$sql_fortuna_podminka_souteze.") AND (".$sql_fortuna_podminka_sporty.")
                AND fortuna_nabidka_kategorie.id_kategorie = fortuna_nabidka_turnaje.id_kategorie
                AND fortuna_nabidka_turnaje.id_turnaje = fortuna_nabidka_zapasy.id_turnaje
                AND fortuna_nabidka_zapasy.id_zapasu = fortuna_nabidka_sazky_moznosti.id_zapasu
                AND fortuna_nabidka_zapasy.id_zapasu = ".$prilezitost["matchId"]."
                AND fortuna_nabidka_zapasy.id_zapasu = fortuna_nabidka_sazky_moznosti.id_sazky
                AND (nazev_moznosti ='1' OR nazev_moznosti ='10' OR nazev_moznosti ='0' OR nazev_moznosti ='02' OR nazev_moznosti ='2')
                GROUP BY matchId";

            $nabidky = $this->databaze->fetch($sql);

            $this->generator($nabidky, $prilezitost, $kurz, $templ_nabidky, $templ_kurzy_pozice, $templ_nabidky_kurzy, $templ_nabidky_moznosti, $templ_kurzy_pole, $templ_nabidky_moznosti_id_pole, $templ_i, $templ_id_tiketu, $templ_kurz_tiketu, $templ_tiket, $nabidky_moznosti_id, $pom_nabidky_moznosti_id_pole, $pom_nabidky_moznosti_id, $pom_nabidky, $pom_kurzy, $pom_kurzy_pozice, $pom_nabidky_kurzy, $pom_nabidky_moznosti, $pom_kurzy_pole, $id_tiketu, $kurz_tiketu, $nalezeno, $zapasy_na_tiketu, $kurz_zacatek_tiketu);                
        }
    }

    public function generatorFortunaPodleZapasu($podminka_vybrane, $podminka_fortuna, &$nabidky, &$prilezitost, &$kurz, &$templ_nabidky, &$templ_kurzy_pozice, &$templ_nabidky_kurzy, &$templ_nabidky_moznosti, &$templ_kurzy_pole, &$templ_nabidky_moznosti_id_pole, &$templ_i, &$templ_id_tiketu, &$templ_kurz_tiketu, &$templ_tiket, &$nabidky_moznosti_id, &$pom_nabidky_moznosti_id_pole, &$pom_nabidky_moznosti_id, &$pom_nabidky, &$pom_kurzy, &$pom_kurzy_pozice, &$pom_nabidky_kurzy, &$pom_nabidky_moznosti, &$pom_kurzy_pole, &$id_tiketu, &$kurz_tiketu, &$nalezeno, &$zapasy_na_tiketu, &$doplneni_tiketu, &$zacatek_tiketu, &$zacatek_tiketu_prilezitost, &$templ_zacatek_tiketu_nabidky, &$templ_zacatek_tiketu_kurzy_pozice, &$templ_zacatek_tiketu_nabidky_kurzy, &$templ_zacatek_tiketu_nabidky_moznosti, &$templ_zacatek_tiketu_kurzy_pole, &$templ_zacatek_tiketu_nabidky_moznosti_id_pole, &$zacatek_tiketu_nabidky_moznosti_id, &$templ_zacatek_tiketu_id_tiketu)
    {
        if(empty($kurz) || empty($podminka) || empty($podminka_vybrane))
        {
            return 0;
        }
        
        $sql_fortuna_podminka_souteze = "";
        $sql_fortuna_podminka_sporty = "";
        $this->generatorFortunaPomocna($podminka_fortuna, $sql_fortuna_podminka_souteze, $sql_fortuna_podminka_sporty);

        $data = $this->databaze->query("SELECT name FROM tipsport_zakladni_nabidka_zapasy WHERE ".$podminka_vybrane);
        $sql_fortuna_podminka_zapasy = "";
        foreach($data as $zapasy)
        {
            //echo $zapasy["name"];
            $sql_fortuna_podminka_zapasy = $sql_fortuna_podminka_zapasy."fortuna_nabidka_zapasy.nazev_zapasu LIKE '%".$zapasy["name"]."%' OR ";
        }
        $sql_fortuna_podminka_zapasy = substr($sql_fortuna_podminka_zapasy, 0, strlen($sql_fortuna_podminka_zapasy)-4);

        $sql = "SELECT fortuna_nabidka_zapasy.id_zapasu as matchId, fortuna_nabidka_sazky_moznosti.nazev_moznosti as shortName, fortuna_nabidka_sazky_moznosti.kurz_moznosti as rate, fortuna_nabidka_zapasy.nazev_zapasu as name FROM fortuna_nabidka_zapasy, fortuna_nabidka_sazky_moznosti, fortuna_nabidka_turnaje, fortuna_nabidka_kategorie WHERE (".$sql_fortuna_podminka_souteze.") AND (".$sql_fortuna_podminka_sporty.") AND (".$sql_fortuna_podminka_zapasy.")
            AND fortuna_nabidka_kategorie.id_kategorie = fortuna_nabidka_turnaje.id_kategorie
            AND fortuna_nabidka_turnaje.id_kategorie = fortuna_nabidka_zapasy.id_kategorie
            AND fortuna_nabidka_zapasy.id_kategorie = fortuna_nabidka_sazky_moznosti.id_kategorie

            AND fortuna_nabidka_turnaje.id_turnaje = fortuna_nabidka_zapasy.id_turnaje
            AND fortuna_nabidka_zapasy.id_turnaje = fortuna_nabidka_sazky_moznosti.id_turnaje

            AND fortuna_nabidka_zapasy.id_zapasu = fortuna_nabidka_sazky_moznosti.id_zapasu
            AND fortuna_nabidka_zapasy.id_zapasu = fortuna_nabidka_sazky_moznosti.id_sazky

            AND (nazev_moznosti ='1' OR nazev_moznosti ='10' OR nazev_moznosti ='0' OR nazev_moznosti ='02' OR nazev_moznosti ='2')
            AND kurz_moznosti <= ".$kurz." 
            AND kurz_moznosti = (SELECT kurz_moznosti FROM fortuna_nabidka_sazky_moznosti as tip WHERE tip.id_zapasu = fortuna_nabidka_sazky_moznosti.id_zapasu
                                AND tip.id_sazky = fortuna_nabidka_sazky_moznosti.id_zapasu AND tip.nazev_moznosti != '12' ORDER BY kurz_moznosti LIMIT 1) 
            ORDER BY kurz_moznosti";
        //echo $sql."<hr />";
        $data = $this->databaze->query($sql);
        foreach($data as $prilezitost) 
        {
            $sql = "SELECT fortuna_nabidka_kategorie.nazev_kategorie as sport, fortuna_nabidka_turnaje.nazev_turnaje as competitionName, fortuna_nabidka_turnaje.id_turnaje as id_souteze, fortuna_nabidka_zapasy.id_zapasu as matchId, fortuna_nabidka_zapasy.nazev_zapasu as name, GROUP_CONCAT(fortuna_nabidka_sazky_moznosti.nazev_moznosti) AS shortName, GROUP_CONCAT(fortuna_nabidka_sazky_moznosti.kurz_moznosti) AS rate, GROUP_CONCAT(fortuna_nabidka_sazky_moznosti.id_moznosti) as opportunityId FROM fortuna_nabidka_kategorie, fortuna_nabidka_turnaje, fortuna_nabidka_zapasy, fortuna_nabidka_sazky_moznosti WHERE (".$sql_fortuna_podminka_souteze.") AND (".$sql_fortuna_podminka_sporty.") AND (".$sql_fortuna_podminka_zapasy.")
                AND fortuna_nabidka_kategorie.id_kategorie = fortuna_nabidka_turnaje.id_kategorie
                AND fortuna_nabidka_turnaje.id_turnaje = fortuna_nabidka_zapasy.id_turnaje
                AND fortuna_nabidka_zapasy.id_zapasu = fortuna_nabidka_sazky_moznosti.id_zapasu
                AND fortuna_nabidka_zapasy.id_zapasu = ".$prilezitost["matchId"]."
                AND (nazev_moznosti ='1' OR nazev_moznosti ='10' OR nazev_moznosti ='0' OR nazev_moznosti ='02' OR nazev_moznosti ='2')
                GROUP BY matchId";
            //echo "--- ".$sql."<hr />";
            array_push($zacatek_tiketu, $this->databaze->fetch($sql)); 
            array_push($zacatek_tiketu_prilezitost, $prilezitost);  
        }

        for($i=0;$i<count($zacatek_tiketu);$i++)
        {
            //array_push($zacatek_tiketu_zapasy_na_tiketu,$zacatek_tiketu[$i]);
            //naplňování tiketu zápasama
            array_push($templ_zacatek_tiketu_nabidky, $zacatek_tiketu[$i]);
            array_push($templ_zacatek_tiketu_kurzy_pozice, $zacatek_tiketu_prilezitost[$i]["shortName"]);
            array_push($templ_zacatek_tiketu_nabidky_kurzy, explode(",", $zacatek_tiketu[$i]["rate"]));
            array_push($templ_zacatek_tiketu_nabidky_moznosti, explode(",", $zacatek_tiketu[$i]["shortName"]));
            array_push($templ_zacatek_tiketu_kurzy_pole, array_combine(end($templ_zacatek_tiketu_nabidky_moznosti), end($templ_zacatek_tiketu_nabidky_kurzy)));      

            array_push($zacatek_tiketu_nabidky_moznosti_id, explode(",", end($templ_zacatek_tiketu_nabidky)["opportunityId"]));
            array_push($templ_zacatek_tiketu_nabidky_moznosti_id_pole, array_combine(end($templ_zacatek_tiketu_nabidky_moznosti), end($zacatek_tiketu_nabidky_moznosti_id)));

            //$templ_i++;
            array_push($templ_zacatek_tiketu_id_tiketu,$id_tiketu);
            $kurz_tiketu = $kurz_tiketu * $zacatek_tiketu_prilezitost[$i]["rate"];
        }
        $kurz_zacatek_tiketu = $kurz_tiketu;

        $this->generatorFortuna($podminka_fortuna, $nabidky, $prilezitost, $kurz, $templ_nabidky, $templ_kurzy_pozice, $templ_nabidky_kurzy, $templ_nabidky_moznosti, $templ_kurzy_pole, $templ_nabidky_moznosti_id_pole, $templ_i, $templ_id_tiketu, $templ_kurz_tiketu, $templ_tiket, $nabidky_moznosti_id, $pom_nabidky_moznosti_id_pole, $pom_nabidky_moznosti_id, $pom_nabidky, $pom_kurzy, $pom_kurzy_pozice, $pom_nabidky_kurzy, $pom_nabidky_moznosti, $pom_kurzy_pole, $id_tiketu, $kurz_tiketu, $nalezeno, $zapasy_na_tiketu, $kurz_zacatek_tiketu, $doplneni_tiketu, $zacatek_tiketu, $zacatek_tiketu_prilezitost, $sql_fortuna_podminka_souteze, $sql_fortuna_podminka_sporty, $podminka_vybrane, false);
    }
}