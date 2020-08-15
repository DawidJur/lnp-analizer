<?php


namespace App\Entity;


interface PageLinkInterface
{
    public function getId(): ?int;

    public function getLink(): ?string;

    public function setLink(string $link): self;
}