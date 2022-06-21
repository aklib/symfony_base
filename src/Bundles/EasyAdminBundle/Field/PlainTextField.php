<?php /** @noinspection PhpUnused */

/**
 * Class PlainTextField
 * @package App\Bundles\EasyAdminBundle\Field
 *
 * since: 20.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\EasyAdminBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PlainTextField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_RENDER_AS_HTML = 'renderAsHtml';
    public const OPTION_STRIP_TAGS = 'stripTags';

    /**
     * @param string|false|null $label
     */
    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplatePath('bundles/EasyAdminBundle/crud/field/plain_text.html.twig')
//            ->setTemplateName('crud/field/plain_text')
            ->setFormType(TextType::class)
            ->addCssClass('field-text')
            ->setColumns('col-md-6 col-xxl-5')
            ->setCustomOption(self::OPTION_RENDER_AS_HTML, false)
            ->setCustomOption(self::OPTION_STRIP_TAGS, false);
    }

    public function renderAsHtml(bool $asHtml = true): self
    {
        $this->setCustomOption(self::OPTION_RENDER_AS_HTML, $asHtml);
        return $this;
    }

    public function stripTags(bool $stripTags = true): self
    {
        $this->setCustomOption(self::OPTION_STRIP_TAGS, $stripTags);
        return $this;
    }
}
