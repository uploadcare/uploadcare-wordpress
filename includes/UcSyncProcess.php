<?php

class UcSyncProcess extends WP_Background_Process
{
    protected $action = 'uploadcare_process';

    /**
     * @inheritDoc
     */
    protected function task($item)
    {
        \ULog($item);

        return false;
    }

    protected function complete()
    {
        parent::complete();
        \ULog(\sprintf('Task %s complete', $this->action));
    }
}
