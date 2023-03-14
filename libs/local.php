<?php

declare(strict_types=1);

trait StatusIndicatorMonitorLocalLib
{
    private function GetFormStatus()
    {
        $formStatus = $this->GetCommonFormStatus();

        return $formStatus;
    }

    public static $STATUS_INVALID = 0;
    public static $STATUS_VALID = 1;
    public static $STATUS_RETRYABLE = 2;

    private function CheckStatus()
    {
        switch ($this->GetStatus()) {
            case IS_ACTIVE:
                $class = self::$STATUS_VALID;
                break;
            default:
                $class = self::$STATUS_INVALID;
                break;
        }

        return $class;
    }

    public static $STATE_UNKNOWN = 0;
    public static $STATE_ON = 1;
    public static $STATE_OFF = 2;
    public static $STATE_BLINK = 3;

    private function InstallVarProfiles(bool $reInstall = false)
    {
        if ($reInstall) {
            $this->SendDebug(__FUNCTION__, 'reInstall=' . $this->bool2str($reInstall), 0);
        }

        $associations = [
            ['Wert' => self::$STATE_UNKNOWN, 'Name' => $this->Translate('unknown'), 'Farbe' => -1],
            ['Wert' => self::$STATE_ON, 'Name' => $this->Translate('on'), 'Farbe' => -1],
            ['Wert' => self::$STATE_OFF, 'Name' => $this->Translate('off'), 'Farbe' => -1],
            ['Wert' => self::$STATE_BLINK, 'Name' => $this->Translate('blink'), 'Farbe' => -1],
        ];
        $this->CreateVarProfile('StatusIndicatorMonitor.State', VARIABLETYPE_INTEGER, '', 0, 0, 0, 0, '', $associations, $reInstall);
    }
}
