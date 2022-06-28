<?php /** @noinspection PhpPropertyOnlyWrittenInspection */

namespace App\Entity;

use App\Bundles\Attribute\Entity\AttributableEntity;
use App\Bundles\Attribute\Entity\AttributableEntityTrait;
use App\Entity\Extension\Traits\BlameableEntityTrait;
use App\Entity\Extension\Traits\TimestampableEntityTrait;
use App\Repository\PersonRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 */
class Person implements AttributableEntity
{
    use TimestampableEntityTrait, BlameableEntityTrait, AttributableEntityTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;


    public function getId(): int
    {
        return $this->id;
    }

}
