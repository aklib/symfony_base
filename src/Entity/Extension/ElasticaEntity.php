<?php
/**
 * Class ElasticaEntity
 * @package App\Entity\Extension
 *
 * since: 07.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Entity\Extension;

interface ElasticaEntity
{
    public function toArray(): array;
}