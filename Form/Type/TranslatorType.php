<?php

namespace Bnh\TranslatableFieldBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Bnh\TranslatableFieldBundle\Helpers\TranslatableFieldManager as TranslatableFieldManager;

class TranslatorType extends AbstractType
{
    protected $translatablefieldmanager;
    private $locales;
    private $currentlocale;
    private $translator;
    
    public function __construct($localeCodes ,TranslatableFieldManager $translatableFieldManager, TranslatorInterface $translator)
    {
        $this->translatablefieldmanager = $translatableFieldManager;
        $this->translator = $translator;
        $this->locales = $localeCodes;
        $this->currentlocale = $this->translator->getLocale();
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->checkOptions($options);
        
        $fieldName = $builder->getName();
        $className = $options['translation_data_class'];
        $id = $options['object_id'];
        $locales = $options['locales'];
        
        // fetch data for each locale on this field of the object
        $translations = $this->translatablefieldmanager->getTranslatedFields($className, $fieldName, $id, $locales);

        // 'populate' fields by *hook on form generation
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($fieldName, $locales, $translations)
        {
            $form = $event->getForm();        
            foreach($locales as $locale)
            {
                $data = (array_key_exists($locale, $translations) && array_key_exists($fieldName, $translations[$locale])) ? $translations[$locale][$fieldName] : NULL;
                $form->add($locale, 'text', ['label' => false, 'data' => $data]);
            }
            
            // extra field for twig rendering
            $form->add('currentFieldName', 'hidden', array('data' => $fieldName));
        });

        // submit
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($fieldName, $className, $id, $locales)
        {
            $form = $event->getForm();
            $this->translatablefieldmanager->persistTranslations($form, $className, $fieldName, $id, $locales);
        });
    }
    
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // pass some variables for field rendering
        $view->vars['locales'] = $options['locales'];
        $view->vars['currentlocale'] = $this->currentlocale;
        $view->vars['tranlatedtablocales'] = $this->getTabTranslations();
    }

    public function getName()
    {
        return 'bnhtranslations';
    }
    
    private function getTabTranslations()
    {
        $translatedLocaleCodes = array();
        foreach($this->locales as $locale)
        {
            $translatedLocaleCodes[$locale] = $this->getTranslatedLocalCode($locale);
        }
        
        return $translatedLocaleCodes;
    }
    
    private function getTranslatedLocalCode($localeCode)
    {
        return \Locale::getDisplayLanguage($localeCode, $this->currentlocale);
    }
    
    private function checkOptions($options)
    {
        $condition_dataclass_empty = ($options['translation_data_class'] === "");
        $condition_id_null = ($options['object_id'] === null);
        $condition_locales_invalidarray = (!is_array($options['locales']) || empty($options['locales']));
            
        if($condition_dataclass_empty || $condition_id_null || $condition_locales_invalidarray)
        {
            throw new \Exception('An Error Ocurred');
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array
            (
                  'locales' => $this->locales
                , 'translation_data_class' => ""
                , 'object_id' => null
                , 'mapped' => false
                , 'required' => false
                , 'by_reference' => false
            )
        );
    }

}
