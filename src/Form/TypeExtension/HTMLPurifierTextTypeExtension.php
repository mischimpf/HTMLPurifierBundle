<?php

namespace Exercise\HTMLPurifierBundle\Form\TypeExtension;

use Exercise\HTMLPurifierBundle\Form\Listener\HTMLPurifierListener;
use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HTMLPurifierTextTypeExtension extends AbstractTypeExtension
{
    private $purifiersRegistry;

    public function __construct(HTMLPurifiersRegistryInterface $registry)
    {
        $this->purifiersRegistry = $registry;
    }

    public static function getExtendedTypes(): iterable
    {
        return [TextType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'purify_html' => false,
                'purify_html_profile' => 'default',
            ])
            ->setAllowedTypes('purify_html', 'bool')
            ->setAllowedTypes('purify_html_profile', ['string', 'null'])
            ->setNormalizer('purify_html_profile', function (Options $options, $profile) {
                if (!$options['purify_html']) {
                    return null;
                }

                if ($this->purifiersRegistry->has($profile)) {
                    return $profile;
                }

                throw new InvalidOptionsException(sprintf('The profile "%s" is not registered.', $profile));
            })
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['purify_html']) {
            $builder->addEventSubscriber(
                new HTMLPurifierListener($this->purifiersRegistry, $options['purify_html_profile'])
            );
        }
    }
}
