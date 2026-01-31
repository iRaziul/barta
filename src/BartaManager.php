<?php

declare(strict_types=1);

namespace Larament\Barta;

use Illuminate\Support\Manager;
use Larament\Barta\Drivers\AdnsmsDriver;
use Larament\Barta\Drivers\AlphasmsDriver;
use Larament\Barta\Drivers\BanglalinkDriver;
use Larament\Barta\Drivers\BulksmsDriver;
use Larament\Barta\Drivers\ElitbuzzDriver;
use Larament\Barta\Drivers\EsmsDriver;
use Larament\Barta\Drivers\GrameenphoneDriver;
use Larament\Barta\Drivers\GreenwebDriver;
use Larament\Barta\Drivers\InfobipDriver;
use Larament\Barta\Drivers\LogDriver;
use Larament\Barta\Drivers\MimsmsDriver;
use Larament\Barta\Drivers\RobiDriver;
use Larament\Barta\Drivers\SmsnocDriver;
use Larament\Barta\Drivers\SslDriver;

class BartaManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('barta.default');
    }

    protected function createLogDriver(): LogDriver
    {
        return new LogDriver;
    }

    protected function createEsmsDriver(): EsmsDriver
    {
        return new EsmsDriver($this->config->get('barta.drivers.esms'));
    }

    protected function createMimsmsDriver(): MimsmsDriver
    {
        return new MimsmsDriver($this->config->get('barta.drivers.mimsms'));
    }

    protected function createSslDriver(): SslDriver
    {
        return new SslDriver($this->config->get('barta.drivers.ssl'));
    }

    protected function createGrameenphoneDriver(): GrameenphoneDriver
    {
        return new GrameenphoneDriver($this->config->get('barta.drivers.grameenphone'));
    }

    protected function createBanglalinkDriver(): BanglalinkDriver
    {
        return new BanglalinkDriver($this->config->get('barta.drivers.banglalink'));
    }

    protected function createRobiDriver(): RobiDriver
    {
        return new RobiDriver($this->config->get('barta.drivers.robi'));
    }

    protected function createInfobipDriver(): InfobipDriver
    {
        return new InfobipDriver($this->config->get('barta.drivers.infobip'));
    }

    protected function createAdnsmsDriver(): AdnsmsDriver
    {
        return new AdnsmsDriver($this->config->get('barta.drivers.adnsms'));
    }

    protected function createAlphasmsDriver(): AlphasmsDriver
    {
        return new AlphasmsDriver($this->config->get('barta.drivers.alphasms'));
    }

    protected function createGreenwebDriver(): GreenwebDriver
    {
        return new GreenwebDriver($this->config->get('barta.drivers.greenweb'));
    }

    protected function createBulksmsDriver(): BulksmsDriver
    {
        return new BulksmsDriver($this->config->get('barta.drivers.bulksms'));
    }

    protected function createElitbuzzDriver(): ElitbuzzDriver
    {
        return new ElitbuzzDriver($this->config->get('barta.drivers.elitbuzz'));
    }

    protected function createSmsnocDriver(): SmsnocDriver
    {
        return new SmsnocDriver($this->config->get('barta.drivers.smsnoc'));
    }
}
