<?php

namespace App\Entity;

use App\Repository\EmailTemplateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailTemplateRepository::class)]
class EmailTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $content;

    #[ORM\Column(type: 'array')]
    private $availableFields = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAvailableFields(): ?array
    {
        return $this->availableFields;
    }

    public function setAvailableFields(array $availableFields): self
    {
        $this->availableFields = $availableFields;

        return $this;
    }
}
