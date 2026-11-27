<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Symfony\Form;

use AlexandreBulete\DddMenuBundle\Infrastructure\Config\ConfiguredZoneCatalog;
use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Resource\MenuResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MenuType extends AbstractType
{
    public function __construct(
        private readonly ConfiguredZoneCatalog $zones,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('zone', ChoiceType::class, [
                'section' => 'placement',
                'label' => 'menu.menu.zone',
                'help' => 'menu.menu.zone_help',
                'choices' => $this->zones->choices(),
                'choice_translation_domain' => 'messages',
            ])
            ->add('title', TextType::class, [
                'section' => 'menu',
                'label' => 'menu.menu.title',
                'help' => 'menu.menu.title_help',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('position', IntegerType::class, [
                'section' => 'placement',
                'label' => 'menu.menu.position',
                'help' => 'menu.menu.position_help',
                'empty_data' => '0',
            ])
            ->add('items', MenuLinksType::class, [
                'section' => 'menu',
                'label' => 'menu.menu.items',
                'help' => 'menu.menu.items_help',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuResource::class,
            'sections' => [
                'menu' => ['label' => 'menu.section.menu'],
                'placement' => ['label' => 'menu.section.placement', 'column' => 'side'],
            ],
        ]);
    }
}
