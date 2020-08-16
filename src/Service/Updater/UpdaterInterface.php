<?php


namespace App\Service\Updater;


interface UpdaterInterface
{
    public function save(array $data): void;
}