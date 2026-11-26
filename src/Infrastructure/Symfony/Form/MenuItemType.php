<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Symfony\Form;

use AlexandreBulete\DddMenuBundle\Infrastructure\Link\LinkResolver;
use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Resource\MenuResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * One link of a menu: where it goes, and what it says.
 */
final class MenuItemType extends AbstractType
{
    public function __construct(
        private readonly LinkResolver $links,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('link', ChoiceType::class, [
                'label' => 'menu.item.link',
                'placeholder' => 'menu.item.link_placeholder',
                'choices' => $this->links->choices() + ['menu.item.url_choice' => MenuResource::URL_CHOICE],
                'choice_translation_domain' => 'messages',
                'required' => true,
            ])
            ->add('url', TextType::class, [
                'label' => 'menu.item.url',
                'required' => false,
                'attr' => ['placeholder' => 'menu.item.url_placeholder'],
            ])
            ->add('label', TextType::class, [
                'label' => 'menu.item.label',
                'required' => false,
                'attr' => ['placeholder' => 'menu.item.label_placeholder'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null, 'label' => false]);
    }

    public function getBlockPrefix(): string
    {
        return 'ddd_menu_item';
    }
}
