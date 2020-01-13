<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class RingDoorbell extends eqLogic {

    public static $_widgetPossibility = array('custom' => true);

    public static function dependancy_info() {
        $return = array();
        $return['log'] = __CLASS__ . '_update';
        $return['progress_file'] = jeedom::getTmpFolder('RingDoorbell') . '/dependance';
        $return['state'] = 'ok';
        if (exec('pip3 list | grep ring-doorbell | wc -l') == 0)
        {
            $return['state'] = 'nok';
        }

        return $return;
    }

    public static function dependancy_install() {
        log::remove(__CLASS__ . '_update');
        return array('script' => dirname(__FILE__) . '/../../resources/install.sh ' . jeedom::getTmpFolder('RingDoorbell') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_update'));
    }

    public static function syncWithRing() {
        log::add(__CLASS__, 'debug', "Sync with Ring.com started.");
        $result = shell_exec('sudo -H python3 '.dirname(__FILE__) . '/../../resources/RingDoorbellSync.py -u '. config::byKey('username', 'RingDoorbell') .' -p \''. config::byKey('password', 'RingDoorbell').'\'');
        log::add(__CLASS__, 'debug', "Values received from Ring: ".$result);
        $splittedDoorbells = explode(PHP_EOL, $result);
        foreach ($splittedDoorbells as $doorbell) {
            $values = explode('||', $doorbell);
            if(count($values) == 3){
                log::add(__CLASS__, 'debug', "Ring doorbell: ".$doorbell);
                $eqLogic = eqLogic::byLogicalId($values[0], 'RingDoorbell');
                if (!is_object($eqLogic)) {
                    $eqLogic = new RingDoorbell();
                    $eqLogic->setLogicalId($values[0]);
                    $eqLogic->setIsEnable(1);
                    $eqLogic->setCategory('security', 1);
                    $eqLogic->setIsVisible(1);
                    $eqLogic->setEqType_name('RingDoorbell');
                    $eqLogic->setName($values[2]);
                    $eqLogic->save();
                }

                $ringCmd = $eqLogic->getCmd(null, 'RingAction');
                if (!is_object($ringCmd)) {
                    $ringCmd = new RingDoorbellCmd();
                    $ringCmd->setName(__('RingAction', __FILE__));
                }
                
                $ringCmd->setEqLogic_id($eqLogic->getId());
                $ringCmd->setLogicalId('RingAction');
                $ringCmd->setType('action');
                $ringCmd->setSubType('other');
                $ringCmd->setIsVisible(0);
                $ringCmd->save();

                $ringCmd = $eqLogic->getCmd(null, 'Ring');
                if (!is_object($ringCmd)) {
                    $ringCmd = new RingDoorbellCmd();
                    $ringCmd->setName(__('Ring', __FILE__));
                }
                
                $ringCmd->setEqLogic_id($eqLogic->getId());
                $ringCmd->setLogicalId('Ring');
                $ringCmd->setType('info');
                $ringCmd->setSubType('boolean');
                $ringCmd->setIsHistorized(1);
                $ringCmd->save();

                $motionCmd = $eqLogic->getCmd(null, 'MotionAction');
                if (!is_object($motionCmd)) {
                    $motionCmd = new RingDoorbellCmd();
                    $motionCmd->setName(__('MotionAction', __FILE__));
                }
                
                $motionCmd->setEqLogic_id($eqLogic->getId());
                $motionCmd->setLogicalId('MotionAction');
                $motionCmd->setType('action');
                $motionCmd->setSubType('other');
                $motionCmd->setIsVisible(0);
                $motionCmd->save();
                
                $motionCmd = $eqLogic->getCmd(null, 'Motion');
                if (!is_object($motionCmd)) {
                    $motionCmd = new RingDoorbellCmd();
                    $motionCmd->setName(__('Motion', __FILE__));
                }
                
                $motionCmd->setEqLogic_id($eqLogic->getId());
                $motionCmd->setLogicalId('Motion');
                $motionCmd->setType('info');
                $motionCmd->setSubType('boolean');
                $motionCmd->setIsHistorized(1);
                $motionCmd->save();
            }
        }
    }

    public static function refreshData() 
    {
        $result = shell_exec('sudo -H python3 '.dirname(__FILE__) . '/../../resources/RingDoorbellUpdate.py -u '. config::byKey('username', 'RingDoorbell') .' -p \''. config::byKey('password', 'RingDoorbell').'\'');
        $splittedEvents = explode(PHP_EOL, $result);

        foreach (self::byType('RingDoorbell') as $eqLogic)
        {
            if ($eqLogic->getIsEnable() == 1)
            {
                log::add(__CLASS__, 'debug', "Ring.com old persisted data: ". $eqLogic->getConfiguration('RingDoorbellHistoricalData'));   
                $events = array();
                foreach ($splittedEvents as $event) {
                    $values = explode('||', $event);
                    if($eqLogic->getLogicalId() == $values[0]) {
                        RingDoorbell::updateInformation($eqLogic, $values[4], $values[2]);
                        $isAnwsered = $values[3] == "False" ? "0" : "1";
                        array_push($events, $values[4].'|'.$values[2].'|'.$isAnwsered);
                    }
                }

                $eqLogic->setConfiguration('RingDoorbellHistoricalData', implode(PHP_EOL, $events));
                $eqLogic->save();
                log::add(__CLASS__, 'debug', "Ring.com new persisted data: ". $eqLogic->getConfiguration('RingDoorbellHistoricalData'));   
                $eqLogic->refreshWidget();
            }
        }
    }

    public static function cron15() 
    {
        log::add(__CLASS__, 'debug', "Ring.com cron started.");
        if(config::byKey('useIFTT', 'RingDoorbell') != "1")
        {
            RingDoorbell::refreshData();
        }

        log::add(__CLASS__, 'debug', "Ring.com cron ended.");
    }

    public static function updateInformation($eqLogic, $type, $datetime)
    {
        log::add(__CLASS__, 'debug', "updateInformation ".$type." ".$datetime);
    }

    public function toHtml($_version = 'dashboard') {
        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            log::add(__CLASS__, 'debug', 'Not array');
            return $replace;
        }
        $version = jeedom::versionAlias($_version);
        $replace['#RingDoorbellHistoricalData#'] = $this->getConfiguration('RingDoorbellHistoricalData');
     	$html = template_replace($replace, getTemplate('core', $version, 'eqlogic', 'RingDoorbell'));
     	return $html;
	}
}

class RingDoorbellCmd extends cmd {
    public function execute($_options = array()) {
        if ($this->getLogicalId() == 'Ring' || $this->getLogicalId() == 'Motion') {
            RingDoorbell::refreshData();
        }
    }
}