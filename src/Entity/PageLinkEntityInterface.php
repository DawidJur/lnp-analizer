<?php


namespace App\Entity;


interface PageLinkEntityInterface
{
    public function getId(): ?int;

    public function getLink(): ?string;

    public function setLink(string $link): self;
}