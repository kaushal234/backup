<?php

declare(strict_types=1);

namespace App\Form\Type\User;

use App\DataTransferObject\Country as DtoCountry;
use App\DataTransferObject\User\UpdateUser;
use App\Form\Type\CountryAutocompleteType;
use App\Locale;
use App\Sdk\Client;
use App\Sdk\Resource\Country;
use App\Sdk\Utils\IriToId;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UserType extends AbstractType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Client $client,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $languages = array_reduce(Locale::cases(), static function ($memo, Locale $locale) {
            $memo[$locale->value] = $locale->toApiLanguage();

            return $memo;
        }, []);

        $phoneAttr = ['class' => 'form-control'];
        if (null !== $options['phone_prefix']) {
            $phoneAttr['placeholder'] = $options['phone_prefix'];
        }

        $builder
            ->add('iri', HiddenType::class)
            ->add('profileIri', HiddenType::class)
            ->add('lastname', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('firstname', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('title', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('division', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('department', TextType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('language', ChoiceType::class, [
                'required' => true,
                'choices' => $languages,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('reception', TextType::class, [
                'required' => false,
                'attr' => $phoneAttr,
            ])
            ->add('phone', TextType::class, [
                'required' => false,
                'attr' => $phoneAttr,
            ])
            ->add('mobile', TextType::class, [
                'required' => false,
                'attr' => $phoneAttr,
            ])
            ->add('street', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('street2', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('postalCode', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('state', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('country', CountryAutocompleteType::class)
        ;

        $client = $this->client;
        $builder->get('country')->addModelTransformer(new CallbackTransformer(
            static function ($value) use ($client) {
                if ($value instanceof DtoCountry) {
                    return $client->find(Country::class, ['resource_id' => IriToId::iriToId($value->iri)]);
                }

                return $value;
            },
            static function ($airport): ?DtoCountry {
                if (!$airport instanceof Country) {
                    return $airport;
                }

                $dtoAirport = new DtoCountry();
                $dtoAirport->iri = $airport->iri;

                return $dtoAirport;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateUser::class,
            'csrf_protection' => true,
            'action' => $this->urlGenerator->generate('account:update'),
            'phone_prefix' => null,
        ]);
        $resolver->setAllowedTypes('phone_prefix', ['null', 'string']);
    }
}
