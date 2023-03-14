<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/common.php';
require_once __DIR__ . '/../libs/local.php';

class StatusIndicatorMonitor extends IPSModule
{
    use StatusIndicatorMonitor\StubsCommonLib;
    use StatusIndicatorMonitorLocalLib;

    private static $semaphoreTM = 1000;

    private $ModuleDir;
    private $SemaphoreID;

    public function __construct(string $InstanceID)
    {
        parent::__construct($InstanceID);

        $this->ModuleDir = __DIR__;
        $this->SemaphoreID = __CLASS__ . '_' . $InstanceID;
    }

    public function Create()
    {
        parent::Create();

        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyBoolean('module_disable', false);

        $this->RegisterPropertyString('topic', '');
        $this->RegisterPropertyString('state_name', 'Switch1');

        $this->RegisterPropertyInteger('max_age', 15 * 60);

        $this->RegisterPropertyInteger('observation_period', 3);
        $this->RegisterPropertyInteger('changes_count', 5);

        $this->RegisterPropertyInteger('inactivity_duration', 5);

        $this->RegisterAttributeString('UpdateInfo', '');

        $this->SetBuffer('States', json_encode([]));

        $this->InstallVarProfiles(false);

        $this->RegisterTimer('UpdateData', 0, 'IPS_RequestAction(' . $this->InstanceID . ', "UpdateData", "");');

        $this->RegisterMessage(0, IPS_KERNELMESSAGE);
    }

    public function MessageSink($timestamp, $senderID, $message, $data)
    {
        parent::MessageSink($timestamp, $senderID, $message, $data);

        if ($message == IPS_KERNELMESSAGE && $data[0] == KR_READY) {
            $this->SetUpdateInterval();
        }
    }

    private function CheckModuleConfiguration()
    {
        $r = [];

        $topic = $this->ReadPropertyString('topic');
        if ($topic == '') {
            $this->SendDebug(__FUNCTION__, '"topic" is empty', 0);
            $r[] = $this->Translate('Topic is required');
        }

        return $r;
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->MaintainReferences();

        if ($this->CheckPrerequisites() != false) {
            $this->MaintainTimer('UpdateData', 0);
            $this->MaintainStatus(self::$IS_INVALIDPREREQUISITES);
            return;
        }

        if ($this->CheckUpdate() != false) {
            $this->MaintainTimer('UpdateData', 0);
            $this->MaintainStatus(self::$IS_UPDATEUNCOMPLETED);
            return;
        }

        if ($this->CheckConfiguration() != false) {
            $this->MaintainTimer('UpdateData', 0);
            $this->MaintainStatus(self::$IS_INVALIDCONFIG);
            return;
        }

        $vpos = 1;

        $this->MaintainVariable('State', $this->Translate('State'), VARIABLETYPE_INTEGER, 'StatusIndicatorMonitor.State', $vpos++, true);
        $this->MaintainVariable('Alarm', $this->Translate('Alarm'), VARIABLETYPE_BOOLEAN, '~Alert', $vpos++, true);

        $module_disable = $this->ReadPropertyBoolean('module_disable');
        if ($module_disable) {
            $this->MaintainStatus(IS_INACTIVE);
            return;
        }

        $this->MaintainStatus(IS_ACTIVE);

        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $topic = $this->ReadPropertyString('topic');
        $this->SetReceiveDataFilter('.*' . $topic . '.*');

        if (IPS_GetKernelRunlevel() == KR_READY) {
            $this->SetUpdateInterval();
        }
    }

    private function GetFormElements()
    {
        $formElements = $this->GetCommonFormElements('Monitor status indicator');

        if ($this->GetStatus() == self::$IS_UPDATEUNCOMPLETED) {
            return $formElements;
        }

        $formElements[] = [
            'type'    => 'CheckBox',
            'name'    => 'module_disable',
            'caption' => 'Disable instance'
        ];

        $formElements[] = [
            'type'    => 'ValidationTextBox',
            'name'    => 'topic',
            'caption' => 'Topic',
        ];

        $formElements[] = [
            'type'      => 'ExpansionPanel',
            'caption'   => 'Detection of the state "flashing"',
            'expanded'  => true,
            'items'     => [
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'observation_period',
                    'minimum' => 1,
                    'caption' => 'Observation period',
                ],
                [
                    'type'    => 'NumberSpinner',
                    'name'    => 'changes_count',
                    'minimum' => 2,
                    'caption' => 'Minimum count of changes',
                ],
            ],
        ];

        $formElements[] = [
            'type'    => 'NumberSpinner',
            'name'    => 'inactivity_duration',
            'suffix'  => 'Minutes',
            'minimum' => 0,
            'caption' => 'Maximum duration of inactivity',
        ];

        return $formElements;
    }

    private function GetFormActions()
    {
        $formActions = [];

        if ($this->GetStatus() == self::$IS_UPDATEUNCOMPLETED) {
            $formActions[] = $this->GetCompleteUpdateFormAction();

            $formActions[] = $this->GetInformationFormAction();
            $formActions[] = $this->GetReferencesFormAction();

            return $formActions;
        }

        $formActions[] = [
            'type'      => 'ExpansionPanel',
            'caption'   => 'Expert area',
            'expanded'  => false,
            'items'     => [
                $this->GetInstallVarProfilesFormItem(),
            ],
        ];

        $formActions[] = $this->GetInformationFormAction();
        $formActions[] = $this->GetReferencesFormAction();

        return $formActions;
    }

    private function LocalRequestAction($ident, $value)
    {
        $r = true;
        switch ($ident) {
            case 'UpdateData':
                $this->UpdateData();
                break;
            default:
                $r = false;
                break;
        }
        return $r;
    }

    public function RequestAction($ident, $value)
    {
        if ($this->LocalRequestAction($ident, $value)) {
            return;
        }
        if ($this->CommonRequestAction($ident, $value)) {
            return;
        }

        if ($this->GetStatus() == IS_INACTIVE) {
            $this->SendDebug(__FUNCTION__, $this->GetStatusText() . ' => skip', 0);
            return;
        }

        $this->SendDebug(__FUNCTION__, 'ident=' . $ident . ', value=' . $value, 0);

        $r = false;
        switch ($ident) {
            default:
                $this->SendDebug(__FUNCTION__, 'invalid ident ' . $ident, 0);
                break;
        }
        if ($r) {
            $this->SetValue($ident, $value);
        }
    }

    private function SetUpdateInterval(int $sec = null)
    {
        if (is_null($sec)) {
            $sec = $this->ReadPropertyInteger('inactivity_duration') * 60;
        }
        $this->MaintainTimer('UpdateData', $sec * 1000);
    }

    private function DetermineState($entries)
    {
        $observation_period = $this->ReadPropertyInteger('observation_period');
        $changes_count = $this->ReadPropertyInteger('changes_count');

        $now = time();
        $state = self::$STATE_UNKNOWN;
        $state_changes = 0;
        $n_entries = count($entries);
        if ($n_entries) {
            $begin = $now - $observation_period;
            $last_state = $entries[$n_entries - 1]['state'];
            $cur_state = $state;
            for ($i = $n_entries - 2; $i >= 0; $i--) {
                if ($entries[$i]['timestamp'] < $begin) {
                    break;
                }
                if ($entries[$i]['state'] != $cur_state) {
                    $state_changes++;
                    $cur_state = $entries[$i]['state'];
                }
            }
            if ($state_changes >= $changes_count) {
                $state = self::$STATE_BLINK;
            } else {
                $state = $last_state ? self::$STATE_OFF : self::$STATE_ON;
            }
        }

        /*
        $this->SendDebug(__FUNCTION__, 'state_changes=' . $state_changes . ', determined state=' . $state, 0);
         */
        return $state;
    }

    public function ReceiveData($data)
    {
        $this->SendDebug(__FUNCTION__, 'data=' . $data, 0);
        $buffer = json_decode($data, true);

        $topic = $buffer['Topic'];
        $payload = json_decode(utf8_decode($buffer['Payload']), true);

        $this->SendDebug(__FUNCTION__, 'topic=' . $topic . ', payload=' . print_r($payload, true), 0);

        $topicV = explode('/', $topic);
        switch ($topic) {
            case 'tele/' . $topicV[1] . '/SENSOR':
                $state_name = $this->ReadPropertyString('state_name');
                if (isset($payload[$state_name]) == false) {
                    $this->SendDebug(__FUNCTION__, 'item "' . $state_name . '" missing', 0);
                    return;
                }
                $payload_state = $payload[$state_name];
                if (isset($payload['Time']) == false) {
                    $this->SendDebug(__FUNCTION__, 'item "Time" missing', 0);
                    return;
                }
                $payload_time = $payload['Time'];

                if (IPS_SemaphoreEnter($this->SemaphoreID, self::$semaphoreTM) == false) {
                    $this->SendDebug(__FUNCTION__, 'unable to lock sempahore ' . $this->SemaphoreID, 0);
                    return;
                }

                $max_age = $this->ReadPropertyInteger('max_age');
                $observation_period = $this->ReadPropertyInteger('observation_period');

                $now = time();
                $states = @json_decode($this->GetBuffer('States'), true);
                if ($states == false) {
                    $states = [
                        'timestamp' => $now,
                        'entries'   => [],
                    ];
                }
                $entries = $states['entries'];
                $new_entries = [];
                $begin = $now - $max_age;
                $n_entries = count($entries);
                for ($i = 0; $i < $n_entries; $i++) {
                    if ($entries[$i]['timestamp'] < $begin) {
                        continue;
                    }
                    $new_entries[] = $entries[$i];
                }
                $new_entries[] = [
                    'timestamp' => strtotime($payload_time),
                    'state'     => $payload_state == 'ON'
                ];
                $states['timestamp'] = $now;
                $states['entries'] = $new_entries;

                $this->SetBuffer('States', json_encode($states));

                $state = $this->DetermineState($new_entries);
                $this->SetValue('State', $state);

				$alarm = false;
                $this->SetValue('Alarm', $alarm);

                IPS_SemaphoreLeave($this->SemaphoreID);

				$this->SendDebug(__FUNCTION__, 'state=' . $this->CheckVarProfile4Value('StatusIndicatorMonitor.State', $state) . ', alarm=' . $this->bool2str($alarm), 0);

                if ($state == self::$STATE_BLINK) {
                    $this->SetUpdateInterval($observation_period + 1);
                } else {
                    $this->SetUpdateInterval();
                }
                break;
            case 'tele/' . $topicV[1] . '/STATE':
                break;
        }
    }

    private function UpdateData()
    {
        if (IPS_SemaphoreEnter($this->SemaphoreID, self::$semaphoreTM) == false) {
            $this->SendDebug(__FUNCTION__, 'unable to lock sempahore ' . $this->SemaphoreID, 0);
            return;
        }

        $states = @json_decode($this->GetBuffer('States'), true);
        $state = $this->DetermineState($states['entries']);
        $this->SetValue('State', $state);

        $inactivity_duration = $this->ReadPropertyInteger('inactivity_duration');
        $alarm = (time() - $states['timestamp']) > $inactivity_duration;
        $this->SetValue('Alarm', $alarm);

        IPS_SemaphoreLeave($this->SemaphoreID);

        $this->SendDebug(__FUNCTION__, 'state=' . $this->CheckVarProfile4Value('StatusIndicatorMonitor.State', $state) . ', alarm=' . $this->bool2str($alarm), 0);

        $this->SetUpdateInterval();
    }
}
