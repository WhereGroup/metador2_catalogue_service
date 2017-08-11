<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

/**
 * Class CswType
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Form
 */
class CswType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $a = 0;
        $builder
            ->add('slug', TextType::class, array(
                'label' => 'Slug',
                'required' => true
            ))
            ->add('source', TextType::class, array(
                'label' => 'Quelle',
                'required' => true,
            ))
            ->add('abstract', TextareaType::class, array(
                'label' => 'Beschreibung',
                'required' => false,
            ))
            ->add('keywords', TextareaType::class, array( // csv value
                'label' => 'Schlüsselwörter',
                'required' => false,
            ))
            ->add('fees', TextType::class, array(
                'label' => 'Gebühren',
                'required' => false,
                'empty_data' => 'none'
            ))
            ->add('accessConstraints', TextType::class, array( // csv value
                'label' => 'Zugangsbeschränkungen',
                'required' => false,
                'empty_data' => 'none'
            ))
            ->add('providerName', TextType::class, array(
                'label' => 'Betreiber',
                'required' => true,
            ))
            ->add('providerSite', UrlType::class, array(
                'label' => 'Betreiber Seite',
                'required' => false,
            ))
            ->add('individualName', TextType::class, array(
                'label' => 'Zuständige Person',
                'required' => false,
            ))
            ->add('positionName', TextType::class, array(
                'label' => 'Role',
                'required' => false,
            ))
            ->add('phoneVoice', TextType::class, array(
                'label' => 'Telefon',
                'required' => false,
            ))
            ->add('phoneFacsimile', TextType::class, array(
                'label' => 'FAX',
                'required' => false,
            ))
            ->add('deliveryPoint', TextType::class, array(
                'label' => 'Straße',
                'required' => false,
            ))
            ->add('city', TextType::class, array(
                'label' => 'Ort',
                'required' => false,
            ))
            ->add('administrativeArea', TextType::class, array(
                'label' => 'Bundesland',
                'required' => false,
            ))
            ->add('postalCode', TextType::class, array(
                'label' => 'PLZ',
                'required' => false,
            ))
            ->add('country', TextType::class, array(
                'label' => 'Land',
                'required' => false,
            ))
            ->add('electronicMailAddress', TextType::class, array(
                'label' => 'E-Mail',
                'required' => false,
            ))
            ->add('onlineResourse', TextType::class, array(
                'label' => 'Onlineresourse',
                'required' => false,
            ))
            ->add('doInsert', CheckboxType::class, array(
                'label' => 'Einfügen',
                'required' => false,
            ))
            ->add('doUpdate', CheckboxType::class, array(
                'label' => 'Aktualisieren',
                'required' => false,
            ))
            ->add('doDelete', CheckboxType::class, array(
                'label' => 'Entfernen',
                'required' => false,
            ))
        ;
        
        $callBackTransformer = new CallbackTransformer(
            // transform the array to a string
            function ($textAsArray) {
                if (isset($textAsArray)) {
                    return implode(', ', $textAsArray);
                }
            },
            function ($textAsArray) {
                // transform the string back to an array
                if (isset($textAsArray)) {
                    return preg_split('/\s?,\s?/', trim($textAsArray)) ;
                }
            }
        );
        $builder->get('keywords')->addModelTransformer($callBackTransformer);
        $builder->get('accessConstraints')->addModelTransformer($callBackTransformer);
    }
}
