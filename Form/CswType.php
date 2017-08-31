<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Form;

use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use WhereGroup\CoreBundle\Component\Configuration;
use WhereGroup\CoreBundle\Component\Source;
use WhereGroup\UserBundle\Component\User;

/**
 * Class CswType
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Form
 */
class CswType extends AbstractType
{
    private $source;
    private $config;
    private $plugin;
    private $user;

    /**
     * CswType constructor.
     * @param Source $source
     * @param Configuration $config
     * @param User $user
     * @param null $plugin
     */
    public function __construct(
        Source $source,
        Configuration $config,
        User $user,
        $plugin = null
    ) {
        $this->source = $source;
        $this->config = $config;
        $this->user = $user;
        $this->plugin = $plugin;
    }

    /**
     *
     */
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
        $users = array();
        foreach ($this->user->findAll() as $user) {
            $users[$user->getUsername()] = $user->getUsername();
        }

        $profiles = array_combine(
            array_keys($this->plugin->getActiveProfiles()),
            array_keys($this->plugin->getActiveProfiles())
        );
        $builder
            ->add('slug', TextType::class, array(
                'label' => 'URL Titel',
                'required' => true,
            ))
            ->add('source', ChoiceType::class, array(
                'label' => 'Quelle',
                'required' => true,
                'choices' => $this->source->allValues(),
            ))
            ->add('username', ChoiceType::class, array(
                'label' => 'Benutzer',
                'required' => true,
                'choices' => $users,
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
            ->add('profileMapping', HiddenType::class);

        $stringArrayTransformer = new CallbackTransformer(
            function ($textAsArray) {
                // transform the array to a string
                return isset($textAsArray) ? implode(', ', $textAsArray) : '';
            },
            function ($textAsString) {
                // transform the string back to an array
                return isset($textAsString) ? preg_split('/\s?,\s?/', trim($textAsString)) : array();
            }
        );
        $stringAssocArrayTransformer = new CallbackTransformer(
            function ($textAsArray) {
                // ignore value after FormEvents::PRE_SET_DATA and FormEvents::PRE_SUBMIT
                return '';
            },
            function ($textAsString) {
                // use value before data store
                return isset($textAsString) ? $textAsString : array();
            }
        );
        $builder->get('profileMapping')->addModelTransformer($stringAssocArrayTransformer);
        $builder->get('keywords')->addModelTransformer($stringArrayTransformer);
        $builder->get('accessConstraints')->addModelTransformer($stringArrayTransformer);
        $fields = $this->config->get('hierarchy_levels', 'plugin', 'metador_core');
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($fields, $profiles) {
                /* @var Csw $data */
                $data = $event->getData();
                if (null === $data) {
                    return;
                }
                $pm = $data->getProfileMapping();
                $form = $event->getForm();
                foreach ($fields as $field) {
                    $_data = '';
                    if ($pm && isset($pm[$field])) {
                        $_data = $pm[$field];
                    }
                    $form
                        ->add($field, ChoiceType::class, array(
                            'required' => false,
                            'choices' => $profiles,
                            'mapped' => false,
                            'data' => $_data,
                        ));
                }
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($fields, $profiles) {
                $data = $event->getData();
                if (null === $data) {
                    return;
                }
                $pm = array();
                foreach ($fields as $field) {
                    if (isset($data[$field]) && $data[$field]) {
                        $pm[$field] = $data[$field];
                    }
                }
                $data['profileMapping'] = $pm;
                $event->setData($data);
                $form = $event->getForm();
                $form->get('profileMapping')->setData($pm);
            }
        );
    }
}
