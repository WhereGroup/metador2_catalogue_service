<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use WhereGroup\CoreBundle\Component\Configuration;
use WhereGroup\CoreBundle\Component\Source;

/**
 * Class CswType
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Form
 */
class CswType extends AbstractType
{
    private $source;
    private $config;
    private $plugin;

    /**
     * CswController constructor.
     * @param Source $source
     */
    public function __construct(Source $source, Configuration $config, $plugin = null)
    {
        $this->source = $source;
        $this->config = $config;
        $this->plugin = $plugin;
    }

    public function __destruct()
    {
        unset($this->source, $this->config, $this->plugin);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $sources = $this->source->allValues();
//        $profiles = $this->plugin->getActiveProfiles();
        $profiles = array();
        foreach ($this->plugin->getActiveProfiles() as $key => $value){
            $profiles[$key] = $value['name'];
        }
        $builder
            ->add('slug', TextType::class, array(
                'label' => 'Slug',
                'required' => true,
            ))
            ->add('source', ChoiceType::class, array(
                'label' => 'Quelle',
                'required' => true,
                'choices' => $this->source->allValues()
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
                'empty_data' => 'none',
            ))
            ->add('accessConstraints', TextType::class, array( // csv value
                'label' => 'Zugangsbeschränkungen',
                'required' => false,
                'empty_data' => 'none',
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
            ->add('insert', CheckboxType::class, array(
                'label' => 'Einfügen',
                'required' => false,
            ))
            ->add('update', CheckboxType::class, array(
                'label' => 'Aktualisieren',
                'required' => false,
            ))
            ->add('delete', CheckboxType::class, array(
                'label' => 'Entfernen',
                'required' => false,
            ))
            ->add('service', ChoiceType::class, array(
                'label' => 'Service',
                'required' => false,
                'choices' => $profiles
            ))
            ->add('dataset', ChoiceType::class, array(
                'label' => 'Dataset',
                'required' => false,
                'choices' => $profiles
            ))
            ->add('series', ChoiceType::class, array(
                'label' => 'Series',
                'required' => false,
                'choices' => $profiles
            ))
            ->add('tile', ChoiceType::class, array(
                'label' => 'Tile',
                'required' => false,
                'choices' => $profiles
            ));

        $callBackTransformer = new CallbackTransformer(
            function ($textAsArray) { // transform the array to a string
                return isset($textAsArray) ? implode(', ', $textAsArray) : '';
            },
            function ($textAsString) { // transform the string back to an array
                return isset($textAsString) ? preg_split('/\s?,\s?/', trim($textAsString)) : array();
            }
        );
        $builder->get('keywords')->addModelTransformer($callBackTransformer);
        $builder->get('accessConstraints')->addModelTransformer($callBackTransformer);
    }
}
